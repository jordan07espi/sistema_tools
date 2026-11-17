<?php
/**
 * Prueba de integración del Reporte de Pasajeros dentro de Docker.
 * Ejecutar: docker compose exec web php tests/passengers_integration.php
 * Opcional: ... tests/passengers_integration.php storage/uploads/archivo.xlsx
 * No escribe registros en MySQL ni deja archivos permanentes.
 */
declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
use App\Services\{PassengerService,PassengerExportService};
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$generated = false;
$input = $argv[1] ?? '';
$tmp = sys_get_temp_dir().'/ejecuttrans_passenger_test_'.bin2hex(random_bytes(6));
if (!mkdir($tmp, 0700, true) && !is_dir($tmp)) throw new RuntimeException('No se pudo crear el directorio temporal.');
try {
    if ($input === '') {
        $input = $tmp.'/movimientos.xlsx';
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Movimientos');
        $sheet->fromArray([
            ['Data do turno','Concessionária','Linha','Prefixo','Motorista','familia','Produto','Qtd','Valor unitário','Total'],
            [46046,'EJECUTTRANS S.A.','L18','EJ001','CONDUCTOR 1','Tarifa Completa','SOMOS CIUDAD',2,'US$ 0.39','US$ 0.00'],
            [46046,'EJECUTTRANS S.A.','L18','EJ001','CONDUCTOR 1','Tarifa Media','SOMOS FUTURO',3,'US$ 0.19','US$ 0.00'],
        ], null, 'A1');
        (new Xlsx($book))->save($input);
        $book->disconnectWorksheets();
        $generated = true;
    }
    if (!is_file($input)) throw new RuntimeException('No existe el archivo: '.$input);
    echo "1/4 Lectura Excel...\n";
    $loaded = PassengerService::readExcel($input);
    echo "Hoja: {$loaded['sheet']}; filas: ".count($loaded['rows'])."\n";
    echo "2/4 Cálculo...\n";
    $filter = ['type'=>'anio','year'=>2026,'month'=>0,'day'=>0];
    $result = PassengerService::processRows($loaded['rows'], $filter, true);
    echo "Registros 2026: {$result['filtered_count']}; pasajeros: {$result['totals']['TOTAL PASAJEROS']}; centavos: {$result['totals']['TOTAL RECAUDADO']}\n";
    if ($generated && ($result['totals']['TOTAL PASAJEROS'] !== 5 || $result['totals']['TOTAL RECAUDADO'] !== 135)) {
        throw new RuntimeException('Cálculo incorrecto en la prueba mínima.');
    }
    echo "3/4 Exportación Excel...\n";
    $xlsx = $tmp.'/reporte.xlsx';
    PassengerExportService::excel($result, $xlsx, basename($input));
    if (!is_file($xlsx) || filesize($xlsx)<100) throw new RuntimeException('Excel no generado.');
    echo 'Excel: '.filesize($xlsx)." bytes\n";
    $summaryBook = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsx);
    $summarySheet = $summaryBook->getSheetByName('RESUMEN POR VEHICULO');
    $approvalRow = 10 + count($result['summary']) + 9;
    if ($summarySheet === null || $summarySheet->getCell('A'.$approvalRow)->getValue() !== 'Aprobado por'
        || $summarySheet->getCell('G'.$approvalRow)->getValue() !== 'Aprobado por') {
        throw new RuntimeException('El Excel no contiene los dos bloques de aprobaciones.');
    }
    if ($summarySheet->getCell('A4')->getValue() !== 'REPORTE CONSOLIDADO DE PASAJEROS POR VEHÍCULO' || $summarySheet->getShowGridlines()) {
        throw new RuntimeException('La hoja no tiene título en A4 o sigue mostrando cuadrícula.');
    }
    if (count($summarySheet->getDrawingCollection()) < 1) {
        throw new RuntimeException('No se incrustó el logotipo en el Excel.');
    }
    if ($summarySheet->getDrawingCollection()[0]->getCoordinates() !== 'G1') {
        throw new RuntimeException('El logo de Excel debe estar centrado encima del título.');
    }
    if ($summarySheet->getStyle('A10')->getFont()->getSize() < 10) {
        throw new RuntimeException('El cuerpo de la tabla Excel no tiene el tamaño de letra esperado.');
    }
    $summaryBook->disconnectWorksheets();
    echo "4/4 Exportación PDF...\n";
    $pdf = $tmp.'/reporte.pdf';
    PassengerExportService::pdf($result, $pdf);
    if (!is_file($pdf) || filesize($pdf)<100 || file_get_contents($pdf, false, null, 0, 4)!=='%PDF') {
        throw new RuntimeException('PDF inválido.');
    }
    echo 'PDF: '.filesize($pdf)." bytes\n";
    $combined = $tmp.'/reporte_excel_pdf.zip';
    $zip = new ZipArchive();
    if ($zip->open($combined, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('ZIP no creado.');
    $zip->addFile($xlsx,'reporte.xlsx');
    $zip->addFile($pdf,'reporte.pdf');
    $zip->close();
    if ($zip->open($combined) !== true) throw new RuntimeException('ZIP no se puede abrir.');
    $valid = $zip->locateName('reporte.xlsx') !== false && $zip->locateName('reporte.pdf') !== false;
    $zip->close();
    if (!$valid) throw new RuntimeException('El ZIP no contiene ambos formatos.');
    echo "ZIP: incluye Excel y PDF.\nOK: integración del reporte de pasajeros.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'FALLO: '.get_class($e).' - '.$e->getMessage().' en '.$e->getFile().':'.$e->getLine()."\n");
    exit(1);
} finally {
    foreach (glob($tmp.'/*') ?: [] as $file) if (is_file($file)) @unlink($file);
    @rmdir($tmp);
}
