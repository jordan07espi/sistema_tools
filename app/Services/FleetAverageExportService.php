<?php
declare(strict_types=1);
namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\{Alignment,Border,Fill};
use PhpOffice\PhpSpreadsheet\Worksheet\{Worksheet,PageSetup};
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class FleetAverageExportService {
    private const BLUE='17365D';
    private const COLORS=['REAL'=>'E2F0D9','ESTIMADO'=>'FFF2CC','SIN DATOS'=>'F4CCCC'];
    private static function text(Worksheet $ws,string|array $cell,string $text): void {$ws->setCellValueExplicit($cell,$text,DataType::TYPE_STRING);}
    private static function band(Worksheet $ws,string $range): void {
        $ws->getStyle($range)->applyFromArray(['fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>self::BLUE]],'font'=>['bold'=>true,'color'=>['rgb'=>'FFFFFF']],
            'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER,'wrapText'=>true]]);
    }
    private static function layout(Worksheet $ws,int $paper): void {
        $ws->setShowGridlines(false);
        $ws->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize($paper)->setFitToWidth(1)->setFitToHeight(0);
    }
    private static function replaceSheet(Spreadsheet $book,string $name): Worksheet {
        $old=$book->getSheetByName($name);if($old)$book->removeSheetByIndex($book->getIndex($old));
        return $book->createSheet()->setTitle($name);
    }
    private static function planning(Worksheet $ws,array $plan,array $cells): void {
        $cut=$plan['cut'];$merges=[];$widths=[];
        foreach($ws->getMergeCells() as $range){
            [$min,$max]=Coordinate::rangeBoundaries($range);
            if($min[1]<=$cut)$merges[]=[$min,$max];
            $ws->unmergeCells($range);
        }
        // Congelar fórmulas con sus valores almacenados antes de insertar columnas.
        foreach($ws->getCellCollection()->getCoordinates() as $coordinate){
            $cell=$ws->getCell($coordinate);
            if($cell->getRow()<=$cut && $cell->isFormula())$cell->setValue($cell->getOldCalculatedValue());
        }
        if($cut<$ws->getHighestRow())$ws->removeRow($cut+1,$ws->getHighestRow()-$cut);
        for($c=1;$c<=14;$c++)$widths[$c]=$ws->getColumnDimension(Coordinate::stringFromColumnIndex($c))->getWidth();
        for($c=14;$c>=2;$c-=2)$ws->insertNewColumnBefore(Coordinate::stringFromColumnIndex($c+1),1);
        for($d=0;$d<7;$d++){
            $turn=$d*3+1;$bus=$turn+1;$pass=$turn+2;
            for($r=1;$r<=$cut;$r++)$ws->duplicateStyle($ws->getStyle([$bus,$r]),Coordinate::stringFromColumnIndex($pass).$r);
            $ws->getColumnDimension(Coordinate::stringFromColumnIndex($turn))->setWidth($widths[$d*2+1]>0?$widths[$d*2+1]:8);
            $ws->getColumnDimension(Coordinate::stringFromColumnIndex($bus))->setWidth($widths[$d*2+2]>0?$widths[$d*2+2]:10);
            $ws->getColumnDimension(Coordinate::stringFromColumnIndex($pass))->setWidth(14);
        }
        foreach($merges as [$min,$max]){
            $a=$min[0]+intdiv($min[0]-1,2);$b=$max[0]+intdiv($max[0]-1,2)+($max[0]%2===0?1:0);
            $ws->mergeCells(Coordinate::stringFromColumnIndex($a).$min[1].':'.Coordinate::stringFromColumnIndex($b).min($max[1],$cut));
        }
        foreach($plan['blocks'] as $block){
            $r=$block['header']+1;
            for($d=0;$d<7;$d++){
                $cell=[$d*3+1,$r];$text=(string)$ws->getCell($cell)->getValue();
                self::text($ws,$cell,$text."\nTURNO | BUS | PASAJEROS");
                $ws->getStyle($cell)->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            }
            $ws->getRowDimension($r)->setRowHeight(max(30,$ws->getRowDimension($r)->getRowHeight()));
        }
        foreach($cells as $entry){
            $coordinate=Coordinate::stringFromColumnIndex($entry['col']).$entry['row'];
            $ws->setCellValue($coordinate,$entry['value']);
            $ws->getStyle($coordinate)->getNumberFormat()->setFormatCode('#,##0');
            $ws->getStyle($coordinate)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $ws->getStyle($coordinate)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLORS[$entry['state']]);
            if($entry['comment']!=='')$ws->getComment($coordinate)->setAuthor('Ejecuttrans')->getText()->createTextRun($entry['comment']);
        }
        $ws->freezePane('A1');self::layout($ws,PageSetup::PAPERSIZE_A4);$ws->getPageSetup()->setPrintArea('A1:U'.$cut);
    }
    private static function summary(Spreadsheet $book,array $summary,array $dates,array $counts): void {
        $ws=self::replaceSheet($book,'RESUMEN');self::layout($ws,PageSetup::PAPERSIZE_A3);
        $ws->mergeCells('A1:Y1')->setCellValue('A1','RESUMEN SEMANAL DE PASAJEROS Y FLOTA');self::band($ws,'A1:Y3');
        $ws->getRowDimension(1)->setRowHeight(26);$ws->getRowDimension(3)->setRowHeight(36);
        $ws->mergeCells('A2:A3')->setCellValue('A2','RUTA');
        foreach([...FleetAverageService::DAYS,'SEMANA'] as $d=>$day){
            $c=2+$d*3;$a=Coordinate::stringFromColumnIndex($c);$b=Coordinate::stringFromColumnIndex($c+2);
            $ws->mergeCells($a.'2:'.$b.'2')->setCellValue($a.'2',$day.($d<7?' '.date('d/m/Y',strtotime($dates[$d])):''));
            foreach(['TOTAL PASAJEROS','TURNOS ACTIVOS','PROMEDIO / TURNO'] as $i=>$label)$ws->setCellValue([$c+$i,3],$label);
        }
        $r=4;
        foreach($summary as $route){
            self::text($ws,[1,$r],$route['RUTA']);
            foreach(FleetAverageService::DAYS as $d=>$day){
                $v=$route[$day];$ws->fromArray([[$v['total_pasajeros'],$v['turnos_activos'],$v['promedio_turno']]],null,Coordinate::stringFromColumnIndex(2+$d*3).$r,true);
            }
            $ws->fromArray([[$route['TOTAL_SEMANA'],$route['TURNOS_SEMANA'],$route['PROMEDIO_SEMANA']]],null,'W'.$r,true);
            if($r%2===0)$ws->getStyle('A'.$r.':Y'.$r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F6FA');
            $ws->getRowDimension($r)->setRowHeight(32);$r++;
        }
        $ws->setCellValue('A'.$r,'TOTAL GENERAL');
        foreach(FleetAverageService::DAYS as $d=>$day){
            $total=array_sum(array_map(fn($v)=>$v[$day]['total_pasajeros'],$summary));$turns=array_sum(array_map(fn($v)=>$v[$day]['turnos_activos'],$summary));
            $ws->fromArray([[$total,$turns,$turns?$total/$turns:0]],null,Coordinate::stringFromColumnIndex(2+$d*3).$r,true);
        }
        $total=array_sum(array_column($summary,'TOTAL_SEMANA'));$turns=array_sum(array_column($summary,'TURNOS_SEMANA'));
        $ws->fromArray([[$total,$turns,$turns?$total/$turns:0]],null,'W'.$r,true);self::band($ws,'A'.$r.':Y'.$r);
        $ws->getStyle('A2:Y'.$r)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('808080');
        $ws->getStyle('A4:A'.$r)->getAlignment()->setWrapText(true);
        $ws->getStyle('B4:Y'.$r)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->getStyle('B4:Y'.$r)->getNumberFormat()->setFormatCode('#,##0');
        for($c=4;$c<=25;$c+=3)$ws->getStyle(Coordinate::stringFromColumnIndex($c).'4:'.Coordinate::stringFromColumnIndex($c).$r)->getNumberFormat()->setFormatCode('#,##0.0');
        $k=$r+3;
        $kpis=[['CARROS/TURNOS ACTIVOS EN TODA LA SEMANA',$turns],['PROMEDIO DE CARROS/TURNOS ACTIVOS POR DIA',$turns/7],['TOTAL DE PASAJEROS DE LA SEMANA',$total],['PROMEDIO SEMANAL DE PASAJEROS POR TURNO',$turns?$total/$turns:0]];
        foreach($kpis as $i=>[$label,$value]){
            $row=$k+$i;$ws->mergeCells('A'.$row.':E'.$row)->setCellValue('A'.$row,$label)->setCellValue('F'.$row,$value);
            $ws->getStyle('A'.$row.':F'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAF7');
            $ws->getStyle('A'.$row.':F'.$row)->getFont()->setBold(true);$ws->getStyle('F'.$row)->getNumberFormat()->setFormatCode(in_array($i,[1,3],true)?'#,##0.0':'#,##0');
        }
        $ws->mergeCells('H'.($k-1).':J'.($k-1))->setCellValue('H'.($k-1),'COBERTURA DE LIQUIDACIONES');self::band($ws,'H'.($k-1).':J'.($k-1));
        foreach($dates as $i=>$date){
            $row=$k+$i;$count=$counts[$date]??0;
            $ws->mergeCells('H'.$row.':I'.$row)->setCellValue('H'.$row,FleetAverageService::DAYS[$i].' '.date('d/m/Y',strtotime($date)));
            $ws->setCellValue('J'.$row,$count)->setCellValue('K'.$row,$count?'CON DATOS':'SIN DATOS');
            $ws->getStyle('H'.$row.':K'.$row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($count?self::COLORS['REAL']:self::COLORS['SIN DATOS']);
        }
        $ws->setCellValue('A'.($k+8),'Verde: real. Amarillo: estimado. Rojo: sin datos. Los totales incluyen estimaciones.');$ws->mergeCells('A'.($k+8).':J'.($k+8));
        $ws->getColumnDimension('A')->setWidth(55);
        for($c=2;$c<=25;$c++)$ws->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(14);
        $ws->freezePane('B4');$ws->setAutoFilter('A3:Y'.($r-1));$ws->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1,3)->setPrintArea('A1:Y'.($k+8));
    }
    private static function control(Spreadsheet $book,array $incidents,array $names): void {
        $ws=self::replaceSheet($book,'CONTROL');self::layout($ws,PageSetup::PAPERSIZE_A4);
        $ws->mergeCells('A1:G1')->setCellValue('A1','CONTROL DE DATOS ESTIMADOS Y FALTANTES');self::band($ws,'A1:G1');$ws->getRowDimension(1)->setRowHeight(26);
        $ws->setCellValue('A3','PLANIFICACION');$ws->mergeCells('B3:G3');self::text($ws,'B3',$names['planning']);
        $ws->setCellValue('A4','LIQUIDACIONES');$ws->mergeCells('B4:G4');self::text($ws,'B4',implode(', ',$names['details']));
        $ws->getStyle('B3:G4')->getAlignment()->setWrapText(true);$ws->getRowDimension(4)->setRowHeight(42);
        $headers=['FECHA','RUTA','TURNO','BUS','ESTADO','DETALLE','PASAJEROS'];$ws->fromArray([$headers],null,'A6');self::band($ws,'A6:G6');
        foreach($incidents as $i=>$incident){
            $r=$i+7;
            foreach($headers as $c=>$key){
                $value=$incident[$key];
                if($key==='FECHA')$value=Date::PHPToExcel(new \DateTimeImmutable($value));
                if(is_string($value))self::text($ws,[$c+1,$r],$value);else $ws->setCellValue([$c+1,$r],$value);
            }
            $ws->getStyle('A'.$r)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $ws->getStyle('A'.$r.':G'.$r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::COLORS[$incident['ESTADO']]);
            $ws->getStyle('A'.$r.':G'.$r)->getAlignment()->setWrapText(true);$ws->getRowDimension($r)->setRowHeight(32);
        }
        foreach([14,55,10,10,16,24,16] as $i=>$width)$ws->getColumnDimension(Coordinate::stringFromColumnIndex($i+1))->setWidth($width);
        $ws->freezePane('A7');if($incidents)$ws->setAutoFilter('A6:G'.(count($incidents)+6));
        $ws->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6,6)->setPrintArea('A1:G'.max(7,count($incidents)+6));
    }
    public static function save(Spreadsheet $book,Worksheet $sheet,array $plan,array $result,array $counts,string $output,array $names): void {
        self::planning($sheet,$plan,$result['cells']);
        self::summary($book,$result['summary'],$plan['dates'],$counts);self::control($book,$result['incidents'],$names);
        $book->setActiveSheetIndex($book->getIndex($sheet));
        // Salida XLSX sin macros, conservando las demás hojas del cuadro original.
        $book->setHasMacros(false);$book->setMacrosCode(null);
        $writer=new Xlsx($book);$writer->setPreCalculateFormulas(false);$writer->save($output);
    }
}
