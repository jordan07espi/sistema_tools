<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\Http;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Dompdf\Dompdf;
use Dompdf\Options;
final class PassengerExportService {
    private const FIELDS=['VEHICULO','PAGANTE - URBANO','SOMOS CIUDAD','TOTAL COMPLETA','VALOR COMPLETA','SOMOS ESPECIALES','SOMOS FUTURO','SOMOS SENIOR','TOTAL MEDIA','VALOR MEDIA','TOTAL PASAJEROS','TOTAL RECAUDADO'];
    private const HEADERS=['VEHÍCULO','PAGANTE - URBANO','SOMOS CIUDAD','TOTAL PASAJEROS','VALOR USD','SOMOS ESPECIALES','SOMOS FUTURO','SOMOS SENIOR','TOTAL PASAJEROS','VALOR USD','TOTAL PASAJEROS','TOTAL RECAUDADO'];
    /** Logotipo institucional único: Assets/images (nunca recibido desde la subida). */
    private static function logoPath(): ?string {
        $file = dirname(__DIR__, 2).'/Assets/images/logo-ejecuttrans.png';
        return is_file($file) && is_readable($file) ? $file : null;
    }
    public static function excel(array $report,string $file,string $source): void {
        $logo = self::logoPath();
        $book=new Spreadsheet();$sh=$book->getActiveSheet();$sh->setTitle('RESUMEN POR VEHICULO');
        // Logotipo centrado y encima del título, igual que en el PDF.
        $sh->mergeCells('A1:L3');
        if ($logo) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath($logo);
            $drawing->setHeight(74);
            $drawing->setCoordinates('G1');
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(2);
            $drawing->setWorksheet($sh);
        }
        $sh->mergeCells('A4:L4');
        $sh->setCellValue('A4', 'REPORTE CONSOLIDADO DE PASAJEROS POR VEHÍCULO');
        $sh->mergeCells('A5:L5');
        $sh->setCellValue('A5', 'CIA EJECUTTRANS S.A. | PERÍODO: '.$report['period']);
        $sh->mergeCells('A6:L6');
        $sh->setCellValue('A6', 'FUENTE: '.$source.' | GENERADO: '.date('d/m/Y H:i'));
        $sh->mergeCells('A8:A9');
        $sh->setCellValue('A8', 'VEHÍCULO');
        $sh->mergeCells('B8:E8');
        $sh->setCellValue('B8', 'TARIFA COMPLETA');
        $sh->mergeCells('F8:J8');
        $sh->setCellValue('F8', 'TARIFA MEDIA');
        $sh->mergeCells('K8:L8');
        $sh->setCellValue('K8', 'TOTAL GENERAL');
        foreach (array_slice(self::HEADERS,1) as $i=>$h) $sh->setCellValue(Coordinate::stringFromColumnIndex($i+2).'9',$h);
        $row=10;
        foreach ($report['summary'] as $r) {self::excelRow($sh,$row++,$r);}
        self::excelRow($sh,$row,$report['totals']);
        $sh->getStyle("A{$row}:L{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sh->getStyle("A{$row}:L{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('17365D');
        // Bloques de firmas separados de la tabla, con espacio para la rúbrica.
        // Se centran tanto en pantalla como al imprimir el libro de Excel.
        for ($blank = $row + 1; $blank <= $row + 7; $blank++) $sh->getRowDimension($blank)->setRowHeight(19);
        foreach ([['A','D','Departamento Monitoreo'], ['G','J','Gerencia']] as [$first, $last, $department]) {
            $lineRow = $row + 8;
            $approvalRow = $row + 9;
            $departmentRow = $row + 10;
            foreach ([$lineRow, $approvalRow, $departmentRow] as $r) $sh->mergeCells($first.$r.':'.$last.$r);
            $sh->setCellValue($first.$lineRow, '___________________________________');
            $sh->setCellValue($first.$approvalRow, 'Aprobado por');
            $sh->setCellValue($first.$departmentRow, $department);
            $sh->getStyle($first.$lineRow.':'.$last.$departmentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sh->getStyle($first.$approvalRow.':'.$last.$departmentRow)->getFont()->setBold(true);
        }
        $sh->getRowDimension($row+8)->setRowHeight(24);
        $sh->getRowDimension($row+9)->setRowHeight(21);
        $sh->getRowDimension($row+10)->setRowHeight(21);
        $sh->mergeCells('A'.($row+13).':L'.($row+13));$sh->setCellValue('A'.($row+13),'Departamento de Tecnologías - Ejecuttrans');
        $sh->mergeCells('A'.($row+14).':L'.($row+14));$sh->setCellValue('A'.($row+14),'Trabajo elaborado por el Departamento de Tecnologías de Ejecuttrans');
        $sh->getStyle('A'.($row+13).':L'.($row+14))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sh->getStyle('A4:L4')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('17365D');
        $sh->getStyle('A5:L5')->getFont()->setSize(12)->setBold(true)->getColor()->setRGB('2F75B5');
        $sh->getStyle('A6:L6')->getFont()->setSize(9)->getColor()->setRGB('4B5563');
        $sh->getStyle('A8:L9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('17365D');
        $sh->getStyle('A8:L9')->getFont()->setBold(true)->setSize(10)->getColor()->setARGB('FFFFFFFF');
        $sh->getStyle('A4:L9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sh->getStyle('A10:L'.$row)->getFont()->setSize(10);
        $sh->getStyle('A10:L'.$row)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        foreach ([1, 2, 3] as $r) $sh->getRowDimension($r)->setRowHeight(25);
        $sh->getRowDimension(4)->setRowHeight(33);
        $sh->getRowDimension(5)->setRowHeight(24);
        $sh->getRowDimension(6)->setRowHeight(20);
        $sh->getRowDimension(9)->setRowHeight(43);
        $sh->getStyle('E10:E'.$row)->getNumberFormat()->setFormatCode('"$" #,##0.00');
        $sh->getStyle('J10:J'.$row)->getNumberFormat()->setFormatCode('"$" #,##0.00');
        $sh->getStyle('L10:L'.$row)->getNumberFormat()->setFormatCode('"$" #,##0.00');
        $sh->getStyle('A10:L'.$row)->applyFromArray(['borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN]]]);
        foreach (range('A','L') as $col) $sh->getColumnDimension($col)->setWidth($col==='A'?16:19);
        $sh->freezePane('B10');$sh->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
        $sh->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);$sh->setShowGridlines(false);
        $detail=$book->createSheet();$detail->setTitle('DETALLE FILTRADO');
        $detail->fromArray(PassengerService::COLUMNS,null,'A1');
        $dr = 2;
        $batch = [];
        foreach ($report['detail'] as $r) {
            $arr = [];
            foreach (PassengerService::COLUMNS as $key) {
                $value = $r[$key];
                if ($key === 'FECHA') $value = date('d/m/Y', strtotime($value));
                if (in_array($key, ['VALOR UNITARIO', 'TOTAL'], true)) $value /= 100;
                $arr[] = $value;
            }
            $batch[] = $arr;
            if (count($batch) === 300) {
                $detail->fromArray($batch, null, 'A'.$dr);
                $dr += count($batch);
                $batch = [];
            }
        }
        if ($batch) {
            $detail->fromArray($batch, null, 'A'.$dr);
            $dr += count($batch);
        }
        $detail->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('17365D');
        $detail->getStyle('A1:J1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $detail->getStyle('I2:J'.($dr-1))->getNumberFormat()->setFormatCode('"$" #,##0.00');
        foreach (range('A','J') as $c) $detail->getColumnDimension($c)->setWidth(in_array($c,['C','E'],true)?37:20);
        $detail->freezePane('A2');$detail->setAutoFilter('A1:J'.($dr-1));
        $writer=new Xlsx($book);
        $writer->setPreCalculateFormulas(false);
        $writer->save($file);
        $book->disconnectWorksheets();
    }
    private static function excelRow($sheet,int $row,array $values): void {
        foreach (self::FIELDS as $idx=>$key) $sheet->setCellValue(Coordinate::stringFromColumnIndex($idx+1).$row,
            in_array($key,['VALOR COMPLETA','VALOR MEDIA','TOTAL RECAUDADO'],true)?$values[$key]/100:$values[$key]);
    }
    public static function pdf(array $report,string $file): void {
        $logo = self::logoPath();
        $rows='';$all=[...$report['summary'],$report['totals']];
        foreach ($all as $index=>$r) {
            $last=$index===count($all)-1;
            $rows.='<tr'.($last?' class="total"':'').'><td>'.Http::e($r['VEHICULO']).'</td>';
            foreach (array_slice(self::FIELDS,1) as $key) {
                $v=$r[$key];$isMoney=in_array($key,['VALOR COMPLETA','VALOR MEDIA','TOTAL RECAUDADO'],true);
                $rows.='<td>'.($isMoney?'$'.number_format($v/100,2):number_format($v,0)).'</td>';
            }
            $rows.='</tr>';
        }
        $headers='';foreach (self::HEADERS as $h)$headers.='<th>'.Http::e($h).'</th>';
        $logoHtml='';
        if ($logo && is_file($logo)) {
            $type=mime_content_type($logo);$image='data:'.$type.';base64,'.base64_encode(file_get_contents($logo));
            $logoHtml='<div class="report-brand"><img src="'.$image.'" alt="Logotipo institucional"></div>';
        }
        $html='<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            @page{size:A4 landscape;margin:10mm 7mm 17mm}
            body{font-family:DejaVu Sans,sans-serif;color:#263449;font-size:9.4px}
            .report-brand{text-align:center;margin:0 auto 6px;width:100%}
            .report-brand img{display:block;margin:0 auto;width:82px;height:82px}
            h1{text-align:center;color:#17365D;font-size:16px;margin:3px 0 4px}
            h2{text-align:center;color:#2F75B5;font-size:10.5px;margin:2px 0 12px}
            table{border-collapse:collapse;width:100%;table-layout:fixed}
            thead{display:table-header-group}
            th{background:#17365D;color:white;padding:7px 2px;border:1px solid #adc0d1;font-size:8.1px;line-height:1.3;word-wrap:break-word}
            td{border:1px solid #c6d2dc;padding:4px 2px;text-align:right;font-size:9.2px;line-height:1.25}
            td:first-child{text-align:center;font-weight:bold}
            tr:nth-child(even){background:#f2f7fc}
            tr.total{background:#17365D;color:#fff;font-weight:bold}
            tr{page-break-inside:avoid}
            .signatures{margin-top:94px;width:100%;table-layout:fixed;page-break-inside:avoid;border-collapse:collapse}
            .signatures td{border:none;text-align:center;font-size:10px;padding:0 12px;vertical-align:top;background:white}
            .signatures .sign-line{font-size:11px;color:#253b5c;white-space:nowrap}
            .signatures .approval{margin:9px 0 4px;font-size:10px;font-weight:bold;color:#17365D}
            .signatures .department{margin:0;font-size:10px;color:#263449}
            .footer{position:fixed;bottom:-12mm;text-align:center;width:100%;font-size:8px;color:#345475}
        </style></head><body>
            '.$logoHtml.'<h1>REPORTE CONSOLIDADO DE PASAJEROS POR VEHÍCULO</h1>
            <h2>CIA EJECUTTRANS S.A. — PERÍODO: '.Http::e($report['period']).'</h2>
            <table><thead><tr>'.$headers.'</tr></thead><tbody>'.$rows.'</tbody></table>
            <table class="signatures"><tr>
                <td><div class="sign-line">___________________________________</div><p class="approval">Aprobado por</p><p class="department">Departamento Monitoreo</p></td>
                <td><div class="sign-line">___________________________________</div><p class="approval">Aprobado por</p><p class="department">Gerencia</p></td>
            </tr></table>
            <div class="footer">Departamento de Tecnologías - Ejecuttrans<br>Trabajo elaborado por el Departamento de Tecnologías de Ejecuttrans</div>
        </body></html>';
        $options=new Options();$options->set('isRemoteEnabled',false);$options->set('defaultFont','DejaVu Sans');
        $pdf=new Dompdf($options);$pdf->loadHtml($html,'UTF-8');$pdf->setPaper('A4','landscape');$pdf->render();
        if (file_put_contents($file, $pdf->output()) === false) {
            throw new \RuntimeException('No se pudo escribir el archivo PDF.');
        }
    }
}
