<?php
declare(strict_types=1);
namespace App\Services;
use PhpOffice\PhpSpreadsheet\{Spreadsheet,Worksheet\Worksheet,Worksheet\Table,Worksheet\Table\TableStyle,Cell\DataType,Cell\Coordinate,Shared\Date,Style\Fill,Style\Border,Writer\Xlsx};

final class BankPaymentsExportService {
    private const AUTHOR='Departamento de Tecnologías - Ejecuttrans';
    private const MONEY='$#,##0.00;[Red]-$#,##0.00';
    public static function color(array $i): string {
        $s=$i['resultado'];if($i['posibles_tipeo'])return 'FCE4D6';
        if(!$i['encontrado']&&str_contains(BankPaymentsService::norm($i['grupo']['banco']),'GUAYAQUIL'))return 'E4DFEC';
        if($s==='CONCILIADO'||str_contains($s,'CON SALDO ANTERIOR'))return 'E2F0D9';
        if(str_contains($s,'SALDO A FAVOR'))return 'D9EAF7';
        if(str_contains($s,'INCOMPLETO')||str_contains($s,'FALTANTE'))return 'F4CCCC';
        return 'FFF2CC';
    }
    public static function comment(array $i): string {
        $s=$i['resultado'];if($s==='CONCILIADO')return '';
        $text=match(true){str_contains($s,'CON SALDO ANTERIOR')=>'Conciliado utilizando saldo a favor de un pago anterior.',str_contains($s,'SALDO A FAVOR')=>'Pago encontrado con saldo a favor.',str_contains($s,'DOCUMENTO REPETIDO')=>'El comprobante aparece más de una vez en el estado de cuenta y requiere revisión.',str_contains($s,'ESTADO')=>'El comprobante fue encontrado, pero en el sistema figura con estado '.$i['grupo']['estado'].'.',str_contains($s,'FALTANTE')=>'El valor encontrado en el banco es menor al valor total registrado en el sistema.',default=>'El comprobante fue encontrado, pero requiere revisión.'};
        return $text."\n\nResultado de conciliación: ".$s.'.';
    }
    private static function value(Worksheet $s,int $c,int $r,mixed $v): void {
        if($v===null)return;
        if(is_string($v))$s->setCellValueExplicit([$c,$r],$v,DataType::TYPE_STRING);else $s->setCellValue([$c,$r],$v);
    }
    private static function row(Worksheet $s,int $r,array $values): void {foreach($values as $c=>$v)self::value($s,$c+1,$r,$v);}
    private static function fill(Worksheet $s,string $range,string $color): void {$s->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);}
    private static function title(Worksheet $s,int $cols,string $subtitle): void {
        $last=Coordinate::stringFromColumnIndex($cols);$s->mergeCells('A1:'.$last.'1')->mergeCells('A2:'.$last.'2');
        $s->setCellValue('A1','CONCILIACIÓN DE PAGOS BANCARIOS')->setCellValue('A2',$subtitle);
        self::fill($s,'A1:'.$last.'1','17365D');self::fill($s,'A2:'.$last.'2','D9EAF7');
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');$s->getStyle('A2')->getFont()->setBold(true)->getColor()->setRGB('17365D');
        $s->getStyle('A1:'.$last.'2')->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);$s->getRowDimension(1)->setRowHeight(28);$s->getRowDimension(2)->setRowHeight(28);
        $s->setShowGridlines(false);$s->getTabColor()->setRGB('2F75B5');
        $s->getPageSetup()->setOrientation('landscape')->setPaperSize(9)->setFitToWidth(1)->setFitToHeight(0);
        $s->getHeaderFooter()->setOddFooter('&C&8'.self::AUTHOR.' | Trabajo elaborado por el Departamento de Tecnologías de Ejecuttrans&R Página &P de &N');
    }
    private static function header(Worksheet $s,int $r,array $headers): void {
        self::row($s,$r,$headers);$range='A'.$r.':'.Coordinate::stringFromColumnIndex(count($headers)).$r;
        self::fill($s,$range,'2F75B5');$s->getStyle($range)->getFont()->setBold(true)->setSize(9)->getColor()->setRGB('FFFFFF');$s->getStyle($range)->getAlignment()->setHorizontal('center')->setWrapText(true);$s->getRowDimension($r)->setRowHeight(32);
    }
    private static function table(Worksheet $s,int $cols,int $last,string $name): void {
        $range='A3:'.Coordinate::stringFromColumnIndex($cols).max(3,$last);$s->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('A6A6A6');
        $s->getStyle($range)->getAlignment()->setVertical('center')->setWrapText(true);$s->freezePane('A4');$s->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(3,3);
        if($last>3){$table=new Table($range,$name);$style=new TableStyle();$style->setTheme(TableStyle::TABLE_STYLE_MEDIUM2)->setShowRowStripes(false);$table->setStyle($style);$s->addTable($table);}
    }
    public static function write(array $items,array $movements,string $output): void {
        $book=new Spreadsheet();
        try{
            $book->getProperties()->setCreator(self::AUTHOR)->setTitle('Conciliación de pagos bancarios');
            $summary=$book->getActiveSheet()->setTitle('RESUMEN');$consolidated=$book->createSheet()->setTitle('CONSOLIDADO');$missing=$book->createSheet()->setTitle('NO ENCONTRADOS');
            self::title($summary,4,'CIA EJECUTTRANS S.A.');self::header($summary,4,['INDICADOR','CANTIDAD']);
            $found=array_filter($items,fn($i)=>$i['encontrado']);$absent=array_values(array_filter($items,fn($i)=>!$i['encontrado']));
            $metrics=[['Comprobantes analizados',count($items),'D9EAF7'],['Encontrados en banco',count($found),'E2F0D9'],['No encontrados',count($absent),'FFF2CC'],['Posibles errores de digitación',count(array_filter($absent,fn($i)=>(bool)$i['posibles_tipeo'])),'FCE4D6'],['Encontrados que requieren revisión',count(array_filter($found,fn($i)=>$i['resultado']!=='CONCILIADO')),'F4CCCC'],['No encontrados de Banco Guayaquil',count(array_filter($absent,fn($i)=>str_contains(BankPaymentsService::norm($i['grupo']['banco']),'GUAYAQUIL'))),'E4DFEC']];
            foreach($metrics as $j=>[$label,$value,$color]){$r=$j+5;self::row($summary,$r,[$label,$value]);self::fill($summary,'A'.$r.':B'.$r,$color);$summary->getStyle('B'.$r)->getFont()->setBold(true);$summary->getRowDimension($r)->setRowHeight(22);}
            $summary->mergeCells('A12:D12');self::header($summary,12,['LEYENDA']);
            $legend=[['E2F0D9','Conciliado correctamente o con saldo anterior'],['D9EAF7','Pago encontrado con saldo a favor'],['F4CCCC','Estado incompleto o pago con valor faltante'],['FCE4D6','Posible error de digitación en el número de comprobante'],['FFF2CC','Comprobante no encontrado o que requiere revisión'],['E4DFEC','Banco Guayaquil: revisar con su estado de cuenta correspondiente']];
            foreach($legend as $j=>[$color,$text]){$r=$j+13;$summary->mergeCells('A'.$r.':D'.$r);self::value($summary,1,$r,$text);self::fill($summary,'A'.$r.':D'.$r,$color);$summary->getRowDimension($r)->setRowHeight(25);}
            foreach(['A'=>48,'B'=>18,'C'=>4,'D'=>24] as $c=>$width)$summary->getColumnDimension($c)->setWidth($width);
            $summary->getStyle('A4:D18')->getAlignment()->setWrapText(true)->setVertical('center');$summary->getPageSetup()->setFitToHeight(1);$summary->getTabColor()->setRGB('17365D');

            // Todos los movimientos permanecen en su orden original, incluidos débitos.
            self::title($consolidated,9,'ESTADO DE CUENTA ORIGINAL CON DATOS DEL SISTEMA DE MINUTOS ADJUNTOS');
            self::header($consolidated,3,['Fecha','Documento','Monto','Codigo','Concepto','Disco','Fecha de obligación','Valor total','Estado']);
            $byDocument=[];foreach($found as $i)$byDocument[$i['grupo']['comprobante_normalizado']]=$i;
            foreach($movements as $j=>$m){$r=$j+4;
                $values=[$m['fecha']?Date::PHPToExcel(new \DateTimeImmutable($m['fecha'])):null,$m['documento_original'],$m['monto']===null?null:$m['monto']/100,$m['codigo'],$m['concepto']];
                // Mantener los valores originales, incluso texto no interpretable o saltos de línea.
                $aliases=[['FECHA'],['DOCUMENTO','COMPROBANTE','REFERENCIA','NUMERODEDOCUMENTO'],['MONTO','VALOR','IMPORTE'],['CODIGO','COD'],['CONCEPTO','DESCRIPCION','DETALLE']];
                foreach($aliases as $c=>$options)foreach($m['encabezados_originales'] as $k=>$h)if(in_array(BankPaymentsService::header($h),$options,true)){$values[$c]=$m['valores_originales'][$k]??null;break;}
                if($m['fecha'])$values[0]=Date::PHPToExcel(new \DateTimeImmutable($m['fecha']));
                self::row($consolidated,$r,$values);$i=$m['es_credito']?($byDocument[$m['documento_normalizado']]??null):null;
                if($i){$g=$i['grupo'];foreach([$g['discos'],$g['fechas_obligacion'],$g['valor_total']/100,$g['estado']] as $k=>$v)self::value($consolidated,$k+6,$r,$v);
                    self::fill($consolidated,'F'.$r.':I'.$r,self::color($i));$comment=self::comment($i);if($comment!=='')$consolidated->getComment('I'.$r)->setAuthor(self::AUTHOR)->setWidth('380px')->setHeight('120px')->getText()->createTextRun($comment);}
                $consolidated->getRowDimension($r)->setRowHeight($i?max(30,min(90,15*(int)ceil(strlen($i['grupo']['fechas_obligacion'])/35))):30);
            }
            $last=count($movements)+3;self::table($consolidated,9,$last,'TablaConsolidado');$consolidated->getStyle('A4:A'.$last)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            foreach(['C','H'] as $c)$consolidated->getStyle($c.'4:'.$c.$last)->getNumberFormat()->setFormatCode(self::MONEY);
            foreach([12,20,14,14,42,12,42,16,20] as $j=>$width)$consolidated->getColumnDimension(Coordinate::stringFromColumnIndex($j+1))->setWidth($width);

            self::title($missing,9,'COMPROBANTES DEL SISTEMA NO ENCONTRADOS EN LOS ESTADOS DE CUENTA');self::header($missing,3,['Disco','Fecha de obligación','Fecha de registro','Valor total','Estado','Banco','N.º de comprobante','Posible documento bancario','Revisión sugerida']);
            foreach($absent as $j=>$i){$g=$i['grupo'];$r=$j+4;$documents=[];$details=[];
                foreach($i['posibles_tipeo'] as [$m,$distance]){$documents[]=$m['documento_original'];$details[]='Posible error de tipeo: difiere en '.$distance.' '.($distance===1?'dígito':'dígitos').'. Movimiento bancario del '.($m['fecha']?date('d/m/Y',strtotime($m['fecha'])):'sin fecha').' por $'.number_format($m['monto']/100,2,'.','').'.';}
                self::row($missing,$r,[$g['discos'],$g['fechas_obligacion'],$g['fechas_registro'],$g['valor_total']/100,$g['estado'],$g['banco'],$g['comprobante_original'],implode(' / ',$documents),implode("\n",$details)]);
                self::fill($missing,'A'.$r.':I'.$r,self::color($i));$missing->getComment('E'.$r)->setAuthor(self::AUTHOR)->getText()->createTextRun($i['resultado']);$missing->getRowDimension($r)->setRowHeight($details?max(45,count($details)*30):30);
            }
            self::table($missing,9,count($absent)+3,'TablaNoEncontrados');if($absent)$missing->getStyle('D4:D'.(count($absent)+3))->getNumberFormat()->setFormatCode(self::MONEY);
            foreach([12,38,24,16,20,23,24,26,65] as $j=>$width)$missing->getColumnDimension(Coordinate::stringFromColumnIndex($j+1))->setWidth($width);$missing->getTabColor()->setRGB('C65911');
            $book->setActiveSheetIndex(0);(new Xlsx($book))->save($output);
        }finally{$book->disconnectWorksheets();}
    }
}
