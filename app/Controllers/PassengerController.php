<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Http,Auth,Files,Audit};
use App\Services\{PassengerService,PassengerExportService};

final class PassengerController {
    public static function index(): void {
        Auth::requirePermission('passengers.view');
        Http::view('passengers/index', ['title' => 'Reporte de pasajeros']);
    }

    public static function generate(): never {
        Auth::requirePermission('passengers.generate');
        Http::verifyCsrf();
        $upload = null;
        $stage = 'recepcion';
        $outputs = [];
        $warnings = [];

        try {
            $filter = [
                'type' => (string) ($_POST['type'] ?? 'todo'),
                'year' => (int) ($_POST['year'] ?? 0),
                'month' => (int) ($_POST['month'] ?? 0),
                'day' => (int) ($_POST['day'] ?? 0),
            ];
            $full = ($_POST['full_fleet'] ?? '') === '1';
            $upload = Files::upload('source');

            $stage = 'lectura_excel';
            $loaded = PassengerService::readExcel($upload);
            $stage = 'calculo';
            $result = PassengerService::processRows($loaded['rows'], $filter, $full);
            $sheetName = $loaded['sheet'];
            unset($loaded);
            gc_collect_cycles();

            $dateTag = date('Ymd_His');
            $stage = 'exportar_excel';
            try {
                [$file, $relative] = Files::exportPath('xlsx');
                $outputs[] = ['type'=>'xlsx', 'name'=>'PASAJEROS_'.$dateTag.'.xlsx', 'relative'=>$relative, 'path'=>$file];
                PassengerExportService::excel($result, $file, basename((string)($_FILES['source']['name'] ?? 'reporte.xlsx')));
                self::assertExport($file, 'Excel');
            } catch (\Throwable $error) {
                self::cleanLastExport($outputs, 'xlsx');
                $ref = PassengerDiagnostics::record($error, $stage);
                $warnings[] = 'No se pudo generar el Excel (referencia '.$ref.'). Revisa el registro de diagnóstico.';
            }

            $stage = 'exportar_pdf';
            try {
                [$file, $relative] = Files::exportPath('pdf');
                $outputs[] = ['type'=>'pdf', 'name'=>'PASAJEROS_'.$dateTag.'.pdf', 'relative'=>$relative, 'path'=>$file];
                PassengerExportService::pdf($result, $file);
                self::assertExport($file, 'PDF');
            } catch (\Throwable $error) {
                self::cleanLastExport($outputs, 'pdf');
                $ref = PassengerDiagnostics::record($error, $stage);
                $warnings[] = 'No se pudo generar el PDF (referencia '.$ref.'). Revisa el registro de diagnóstico.';
            }

            // Ambos formatos, más una descarga conjunta en ZIP cuando se generaron correctamente.
            if (count($outputs) === 2) {
                $stage = 'empaquetar_reportes';
                try {
                    [$zipFile, $zipRelative] = Files::exportPath('zip');
                    $archive = new \ZipArchive();
                    if ($archive->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                        throw new \RuntimeException('No se pudo abrir el ZIP de reportes.');
                    }
                    try {
                        foreach ($outputs as $export) {
                            if (!$archive->addFile($export['path'], $export['name'])) {
                                throw new \RuntimeException('No se pudo añadir '.$export['name'].' al ZIP.');
                            }
                        }
                    } finally {
                        $archive->close();
                    }
                    self::assertExport($zipFile, 'ZIP');
                    array_unshift($outputs, [
                        'type'=>'zip', 'name'=>'PASAJEROS_'.$dateTag.'_EXCEL_Y_PDF.zip',
                        'relative'=>$zipRelative, 'path'=>$zipFile,
                    ]);
                } catch (\Throwable $error) {
                    if (isset($zipFile) && is_file($zipFile)) @unlink($zipFile);
                    $ref = PassengerDiagnostics::record($error, $stage);
                    $warnings[] = 'Se generaron Excel y PDF por separado, pero no se pudo comprimir el ZIP (referencia '.$ref.').';
                }
            }

            if (!$outputs) {
                self::cleanTemporary($upload);
                Http::json(['ok'=>false, 'error'=>'No se pudo exportar Excel ni PDF. '.implode(' ', $warnings).' Consulta storage/logs/passengers.log.', 'warnings'=>$warnings], 500);
            }

            $stage = 'guardar_resultados';
            $items = Files::persistJob('passengers', [...$filter, 'flota_completa'=>$full, 'hoja'=>$sheetName],
                array_map(static fn($f) => ['type'=>$f['type'], 'name'=>$f['name'], 'relative'=>$f['relative']], $outputs));
            Audit::log('passengers.generated', [
                'records'=>$result['filtered_count'],
                'income_cents'=>$result['totals']['TOTAL RECAUDADO'],
                'formats'=>array_column($outputs, 'type'),
            ]);
            if ($result['unknown_family']) {
                $warnings[] = 'Hay '.$result['unknown_family'].' registros con tarifas desconocidas; conservaron el valor declarado en el Excel original.';
            }
            self::cleanTemporary($upload);
            Http::json([
                'ok'=>true, 'period'=>$result['period'], 'sheet'=>$sheetName, 'files'=>$items,
                'metrics'=>[
                    'vehicles'=>count($result['summary']),
                    'passengers'=>$result['totals']['TOTAL PASAJEROS'],
                    'amount'=>number_format($result['totals']['TOTAL RECAUDADO']/100, 2, '.', ','),
                    'records'=>$result['filtered_count'],
                ],
                'warnings'=>$warnings,
            ]);
        } catch (\InvalidArgumentException|\RuntimeException $error) {
            self::cleanExports($outputs);
            PassengerDiagnostics::record($error, $stage);
            self::cleanTemporary($upload);
            Http::json(['ok'=>false, 'error'=>$error->getMessage()], 422);
        } catch (\Throwable $error) {
            self::cleanExports($outputs);
            $ref = PassengerDiagnostics::record($error, $stage);
            $names = ['lectura_excel'=>'lectura del archivo Excel', 'calculo'=>'cálculo de pasajeros',
                'guardar_resultados'=>'registro de archivos en MySQL', 'recepcion'=>'recepción del archivo'];
            $place = $names[$stage] ?? 'generación del reporte';
            self::cleanTemporary($upload);
            Http::json(['ok'=>false, 'error'=>'Error durante la '.$place.' (referencia '.$ref.'). El detalle se guardó en storage/logs/passengers.log.'], 500);
        } finally {
            self::cleanTemporary($upload);
        }
    }

    private static function cleanTemporary(?string $upload): void {
        if ($upload && is_file($upload)) @unlink($upload);
    }

    private static function assertExport(string $file, string $format): void {
        if (!is_file($file) || filesize($file) < 100) {
            throw new \RuntimeException('El archivo '.$format.' no pudo escribirse correctamente.');
        }
    }
    private static function cleanLastExport(array &$outputs, string $type): void {
        foreach ($outputs as $index=>$output) {
            if ($output['type'] !== $type) continue;
            if (is_file($output['path'])) @unlink($output['path']);
            unset($outputs[$index]);
        }
        $outputs = array_values($outputs);
    }
    private static function cleanExports(array $outputs): void {
        foreach ($outputs as $output) if (is_file($output['path'])) @unlink($output['path']);
    }
}
