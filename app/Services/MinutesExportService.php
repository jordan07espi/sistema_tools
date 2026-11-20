<?php
declare(strict_types=1);
namespace App\Services;
use App\Core\Http;
use Dompdf\{Dompdf,Options};
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\{Fill,Border,Alignment};
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class MinutesExportService {
    private static function money(float $value): string {return '$ '.number_format($value,2,',','.');}
    public static function excel(array $report,string $file): void {
        $book=new Spreadsheet();
        try{
            $ws=$book->getActiveSheet()->setTitle('RESUMEN');$ws->fromArray([['DISCO','FECHA','VALOR','RUTA']],null,'A1');$r=2;
            foreach($report['vehicles'] as $vehicle){
                $ws->setCellValue([1,$r],$vehicle['disc'])->setCellValue([2,$r],Date::PHPToExcel(new \DateTimeImmutable($report['date'])))->setCellValue([3,$r],$vehicle['calculation']['valor_cobrar']);
                $ws->setCellValueExplicit([4,$r],$report['line']['name'],DataType::TYPE_STRING);$r++;
            }
            $ws->getStyle('A1:D1')->applyFromArray(['font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'0C3272']]]);
            $ws->getStyle('A1:D'.($r-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D1D5DB');
            $ws->getStyle('A1:D'.($r-1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $ws->getStyle('B2:B'.($r-1))->getNumberFormat()->setFormatCode('yyyy-mm-dd');$ws->getStyle('C2:C'.($r-1))->getNumberFormat()->setFormatCode('"$ "#,##0.00');
            foreach(['A'=>12,'B'=>16,'C'=>16,'D'=>24] as $c=>$width)$ws->getColumnDimension($c)->setWidth($width);
            $ws->getRowDimension(1)->setRowHeight(24);$ws->freezePane('A2');$ws->setAutoFilter('A1:D'.($r-1));$ws->setShowGridlines(false);
            (new Xlsx($book))->save($file);
        }finally{$book->disconnectWorksheets();}
    }
    private static function colored(string $text,string $color): string {
        $safe='black';
        if(MinutesService::red($color))$safe='#ff0000';
        elseif(preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{3})?$/i',$color))$safe=$color;
        elseif(in_array(strtolower($color),['blue','green','orange','gray','black'],true))$safe=strtolower($color);
        return '<span style="color:'.$safe.'">'.Http::e($text).'</span>';
    }
    public static function html(array $report): string {
        $logo=dirname(__DIR__,2).'/Assets/images/logo-ejecuttrans.png';$image=is_file($logo)?'<img class="logo" src="data:image/png;base64,'.base64_encode(file_get_contents($logo)).'">':'';
        $html='<!doctype html><html lang="es"><head><meta charset="utf-8"><style>@page{margin:10mm 10mm 20mm}body{font-family:Helvetica,Arial,sans-serif;font-size:10pt;color:#000}h1{font-size:14pt;line-height:16pt;color:#0C3272;margin:2pt 0 0;text-align:center}h2{font-size:11pt;line-height:13.5pt;text-align:center;margin:0 0 6pt}.logo-wrap{text-align:center;height:36pt;margin-bottom:4pt}.logo{height:34pt;width:auto;max-width:77pt;display:inline-block}.meta{font-size:14pt;line-height:15pt;font-weight:bold;margin-bottom:18pt}table{border-collapse:collapse;width:100%;table-layout:fixed}th,td{border:0.5pt solid #777;padding:4pt 2pt;vertical-align:middle;text-align:center;word-wrap:break-word}th{background:#123574;color:white;font-weight:bold;line-height:1.1}thead{display:table-header-group}tr{page-break-inside:avoid}.summary-block{page-break-inside:avoid}h3{font-size:11.5pt;margin:14pt 0 0}.summary{width:72%;font-size:10.5pt;table-layout:fixed}.summary td{text-align:left;padding:4pt 5pt}.summary .value{font-weight:bold}.summary .direct{background:#FFF4CC}.summary .total td{background:#EAF2F8;font-weight:bold}.note{font-size:10pt;line-height:1.25;margin-top:5pt}.vehicle{page-break-before:always}.vehicle.first{page-break-before:auto}.footer{position:fixed;bottom:-14mm;left:0;right:0;text-align:center;font-size:7.5pt;color:#000}.signature{width:65mm;border-top:0.5pt solid #777;margin:0 auto 5pt}.footer b{font-size:8.5pt}</style></head><body><div class="footer"><div class="signature"></div><b>Departamento de Tecnologías - Ejecuttrans</b><br>Trabajo elaborado por el Departamento de Tecnologías de Ejecuttrans</div>';
        // El recurso cuadrado incluye márgenes blancos: encuadrar la marca sin deformarla.
        $html=str_replace('</style>','.logo-crop{display:inline-block;width:77pt;height:36pt;overflow:hidden}.logo-crop .logo{width:77pt;height:77pt;margin-top:-20pt}.summary td.label{width:33.33%}.summary td.value{width:16.67%}</style>',$html);
        if($image)$image='<span class="logo-crop">'.$image.'</span>';
        foreach($report['vehicles'] as $i=>$vehicle){
            $v=$vehicle['calculation'];$line=$report['line'];$font=max(5,min(10,10*min(1,19/max(1,count($vehicle['rows'])))));
            $html.='<section class="vehicle'.($i===0?' first':'').'">'.($image?'<div class="logo-wrap">'.$image.'</div>':'').'<h1>SISTEMA DE CONTROL DE FLOTA MONITOREO SATELITAL, GESTIÓN Y RECAUDO - CAJA COMÚN</h1><h2>Generador de Reporte Diario de Minutos Caídos</h2><div class="meta">Agencia: Ejecuttrans (STO DOMINGO)<br>Ruta: '.Http::e($line['name']).'<br>Fecha: '.Http::e($report['date']).'<br>Vehículo: '.Http::e($vehicle['vehicle']).'</div>';
            $html.='<table style="font-size:'.$font.'pt"><thead><tr><th style="width:7%">Vehículo</th>';
            foreach($report['controls'] as $c)$html.='<th>'.Http::e($report['headers'][$c]).'</th>';
            $html.='<th style="width:6%">MULTA</th></tr></thead><tbody>';
            foreach($vehicle['rows'] as $r=>$row){
                $html.='<tr><td><b>'.Http::e($vehicle['vehicle']).'</b></td>';
                foreach($report['controls'] as $c)$html.='<td>'.self::colored($row[$c],$vehicle['colors'][$r][$c]).'</td>';
                $html.='<td><b>'.self::colored($row[$report['fine_column']],$vehicle['colors'][$r][$report['fine_column']]).'</b></td></tr>';
            }
            $html.='</tbody></table><div class="summary-block"><h3>RESUMEN DE MINUTOS CAÍDOS</h3><table class="summary"><colgroup><col style="width:33.33%"><col style="width:16.67%"><col style="width:33.33%"><col style="width:16.67%"></colgroup>';
            $tolerance=$line['code']==='23A'||$v['minutos_tolerancia_linea_23a']>0;
            $rates=[];foreach($line['points'] as $p)if((int)$p['enabled']&&$p['rate_cents']!==null)$rates[]=(int)$p['rate_cents'];
            if($line['direct_rate_cents']!==null)$rates=[(int)$line['direct_rate_cents']];
            $rates=array_unique($rates);sort($rates);
            $rateLabel=implode('/',array_map(fn($c)=>'$'.str_replace('.',',',rtrim(rtrim(number_format($c/100,2,'.',''),'0'),'.')),$rates));
            $items=[['Minutos rojos detectados:',$v['total_rojo'].' min',$tolerance?'NS ignorados / tolerancia:':'Minutos NS ignorados:',$v['minutos_ns_ignorados'].($tolerance?' / '.$v['minutos_tolerancia_linea_23a']:'').' min'],
                ['Sancionables sujetos a BASE:',$v['minutos_sujetos_base'].' min','BASE permitida:',$v['base'].' min'],
                ['Minutos perdonados por BASE:',$v['minutos_perdonados'].' min','Minutos cobrables después de BASE:',$v['minutos_cobrables_base'].' min'],
                ['Valor minutos normales ('.str_replace('$ ','$',self::money($v['tarifa_minuto_base'])).'/min):',self::money($v['valor_minutos_base']),'Cobro directo'.($rateLabel!==''?' ('.$rateLabel.' por min)':'').':',$v['minutos_fuera_base'].' min = '.self::money($v['valor_fuera_base'])],
                ['TOTAL MINUTOS SANCIONADOS:',$v['minutos_a_cobrar'].' min','VALOR TOTAL A COBRAR:',self::money($v['valor_cobrar'])]];
            foreach($items as $j=>$cells){$html.='<tr'.($j===4?' class="total"':'').'>';foreach($cells as $k=>$cell)$html.='<td class="'.($k%2?'value ':'label ').($j===3&&$k>=2?'direct':'').'">'.Http::e($cell).'</td>';$html.='</tr>';}
            $html.='</table><div class="note">';
            if($line['direct_rate_cents']!==null)$html.='Los puntos marcados como <b>(S)</b> se cobran directamente a <b>'.self::money((int)$line['direct_rate_cents']/100).' por minuto</b> y no entran en la BASE. ';
            else $html.='Los puntos marcados como <b>(S)</b> entran en la BASE de <b>'.(int)$line['base_minutes'].' minutos</b> y, una vez superada, se cobran a <b>'.self::money((int)$line['base_rate_cents']/100).' por minuto</b>. ';
            $special=[];$tolerances=[];
            foreach($line['points'] as $point){if(!(int)$point['enabled'])continue;if($point['rate_cents']!==null)$special[]=$point['name'].' ('.self::money((int)$point['rate_cents']/100).'/min)';if((int)$point['tolerance_minutes'])$tolerances[]=$point['name'].' ('.$point['tolerance_minutes'].' min por caída)';}
            if($special&&$line['direct_rate_cents']===null)$html.='Punto(s) con cobro directo fuera de la BASE para '.Http::e($line['name']).': <b>'.Http::e(implode(', ',$special)).'</b>. ';
            if($tolerances)$html.='Tolerancias: <b>'.Http::e(implode(', ',$tolerances)).'</b>. ';
            $html.='Los puntos marcados como <b>(NS)</b> se ignoran aunque el dato esté en rojo. La columna <b>MULTA</b> conserva el dato de origen; el valor calculado se muestra en el resumen.</div></div></section>';
        }
        return $html.'</body></html>';
    }
    public static function pdf(array $report,string $file): void {
        $options=new Options();$options->set('isRemoteEnabled',false);$options->set('isPhpEnabled',false);$options->set('defaultFont','DejaVu Sans');
        $pdf=new Dompdf($options);$pdf->loadHtml(self::html($report),'UTF-8');$pdf->setPaper('A3','landscape');$pdf->render();file_put_contents($file,$pdf->output());
    }
}
