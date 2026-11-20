<?php
declare(strict_types=1);
namespace App\Services;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class MinutesService {
    public static function norm(mixed $value): string {return RotativoService::norm($value);}
    public static function compare(mixed $value): string {return trim(preg_replace('/[^A-Z0-9]+/',' ',self::norm($value)));}
    public static function code(string $value): string {
        $s=preg_replace('/[^A-Z0-9]/','',preg_replace('/^LINEA\s*/','',self::norm($value)));
        return preg_match('/^0*(\d+)([A-Z]*)$/D',$s,$m)?(int)$m[1].$m[2]:$s;
    }
    public static function pointName(string $value): string {
        $s=preg_replace('/^\s*\d+\.\s*/','',$value);
        return self::compare(preg_replace('/\(\s*(?:NS|S)\s*\)\s*$/i','',$s));
    }
    public static function pointNumber(string $value): int {return preg_match('/^\s*(\d+)\./',$value,$m)?(int)$m[1]:0;}
    public static function type(string $value): string {return preg_match('/\(\s*(NS|S)\s*\)\s*$/i',$value,$m)?strtoupper($m[1]):'';}
    public static function red(string $color): bool {return in_array(strtolower(str_replace(' ','',trim($color))),['red','#ff0000','#f00','rgb(255,0,0)'],true);}
    public static function minutes(string $value): int {return preg_match('/\[\s*([+-]?\d+)\s*\]/',$value,$m)?max(0,(int)$m[1]):0;}
    private static function nodeText(\DOMNode $node): string {
        if($node instanceof \DOMText)return $node->nodeValue;
        if(strtolower($node->nodeName)==='br')return ' ';
        $s='';foreach($node->childNodes as $child)$s.=self::nodeText($child);return $s;
    }
    private static function nodeColor(\DOMElement $cell): string {
        $color='black';$nodes=[$cell];foreach($cell->getElementsByTagName('*') as $child)$nodes[]=$child;
        foreach($nodes as $node){
            if(preg_match('/(?:^|;)\s*color\s*:\s*([^;]+)/i',$node->getAttribute('style'),$m))$color=trim($m[1]);
            elseif(strtolower($node->tagName)==='font'&&$node->hasAttribute('color'))$color=trim($node->getAttribute('color'));
        }
        return $color;
    }
    public static function read(string $file): array {
        $sample=file_get_contents($file,false,null,0,1024);$rows=[];$colors=[];
        if(preg_match('/<(?:!doctype|html|table|tr|head|body)\b/i',$sample)){
            $html=file_get_contents($file);if(!mb_check_encoding($html,'UTF-8'))$html=mb_convert_encoding($html,'UTF-8','Windows-1252');
            $dom=new \DOMDocument();$old=libxml_use_internal_errors(true);
            try{$loaded=$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET|LIBXML_COMPACT);}finally{libxml_clear_errors();libxml_use_internal_errors($old);}
            if(!$loaded)throw new \RuntimeException('No se pudo leer el reporte HTML.');
            $xpath=new \DOMXPath($dom);
            foreach($xpath->query('//tr') as $tr){
                $row=[];$palette=[];
                foreach($xpath->query('./th|./td',$tr) as $cell){$row[]=trim(preg_replace('/\s+/u',' ',self::nodeText($cell)));$palette[]=self::nodeColor($cell);}
                if($row){$rows[]=$row;$colors[]=$palette;}
            }
        } else {
            try{$book=IOFactory::load($file);}catch(\Throwable $e){throw new \RuntimeException('El archivo no es un reporte XLS/HTML o Excel válido.',0,$e);}
            try{
                $sheet=$book->getActiveSheet();$rows=$sheet->toArray('',true,true,false);
                foreach($rows as $r=>$row){$palette=[];foreach($row as $c=>$value)$palette[]='#'.$sheet->getStyle([$c+1,$r+1])->getFont()->getColor()->getRGB();$colors[]=$palette;}
            }finally{$book->disconnectWorksheets();}
        }
        $header=null;
        foreach($rows as $i=>$row)if(in_array('VEHICULO',array_map([self::class,'norm'],$row),true)){$header=$i;break;}
        if($header===null)throw new \RuntimeException('No se encontró la columna Vehículo en el reporte.');
        $headers=array_map('strval',$rows[$header]);$n=count($headers);$records=[];$palettes=[];
        foreach(array_slice($rows,$header+1) as $i=>$row){
            $row=array_slice(array_pad(array_map('strval',$row),$n,''),0,$n);
            if(!array_filter($row,fn($v)=>trim($v)!==''))continue;
            $records[]=$row;$palettes[]=array_slice(array_pad($colors[$header+1+$i],$n,'black'),0,$n);
        }
        return ['headers'=>$headers,'rows'=>$records,'colors'=>$palettes];
    }
    public static function column(array $headers,array $names): ?int {
        foreach($headers as $i=>$header)if(in_array(self::norm($header),$names,true))return $i;return null;
    }
    public static function controls(array $headers): array {
        $out=[];foreach($headers as $i=>$header)if(preg_match('/^\d+\./',trim($header)))$out[]=$i;
        if(!$out)throw new \RuntimeException('No se encontraron puntos de control numerados.');return $out;
    }
    public static function detect(array $table,array $config,string $filename): array {
        $ri=self::column($table['headers'],['RUTA']);$fi=self::column($table['headers'],['FECHA']);
        if($ri===null)throw new \RuntimeException('El reporte no contiene la columna Ruta.');
        $lines=[];$dates=[];$unknown=[];
        foreach($table['rows'] as $row){
            $route=trim($row[$ri]);
            if($route!==''){
                $id=$config['aliases'][self::compare($route)]??null;
                if($id===null)$unknown[$route]=true;else $lines[$id]=true;
            }
            if($fi!==null&&trim($row[$fi])!==''){
                $date=FleetAverageService::date(str_replace('T',' ',$row[$fi]));
                if($date===null)throw new \RuntimeException('Fecha no válida en el reporte: '.$row[$fi]);
                $dates[$date]=true;
            }
        }
        if($unknown)throw new \RuntimeException('Configura estas rutas en Puntos de control antes de generar: '.implode(', ',array_keys($unknown)).'.');
        if(count($lines)!==1)throw new \RuntimeException('El reporte debe pertenecer a una sola línea principal.');
        if($fi===null&&preg_match_all('/(?<!\d)(\d{1,2})[_.\/-](\d{1,2})[_.\/-](\d{4})(?!\d)/',$filename,$matches,PREG_SET_ORDER)){
            foreach($matches as $m)if(checkdate((int)$m[2],(int)$m[1],(int)$m[3])){$dates[sprintf('%04d-%02d-%02d',$m[3],$m[2],$m[1])]=true;break;}
        }
        if(count($dates)!==1)throw new \RuntimeException('El reporte debe contener una sola fecha. Si no tiene columna Fecha, usa un nombre como Reporte_19_11_2026.xls.');
        $line=$config['lines'][array_key_first($lines)];
        if(!(int)$line['enabled'])throw new \RuntimeException('La línea está deshabilitada en Puntos de control.');
        return ['line'=>$line,'date'=>array_key_first($dates)];
    }
    public static function displayHeaders(array $headers,string $code): array {
        if($code==='18')foreach($headers as &$header)if(self::pointNumber($header)===12&&self::pointName($header)==='MAINPRO')$header=preg_replace('/^(\s*12\.)\s*MAINPRO/i','$1 POLICIA',$header);
        return $headers;
    }
    public static function point(string $header,array $line): ?array {
        $name=self::pointName($header);$number=self::pointNumber($header);
        if($name==='')return null;
        $candidates=array_filter($line['points'],fn($p)=>(int)$p['enabled']===1&&(!(int)$p['point_number']||(int)$p['point_number']===$number));
        usort($candidates,fn($a,$b)=>(int)$b['point_number']<=>(int)$a['point_number']);
        // Preferir una configuración exacta antes de las abreviaturas.
        foreach($candidates as $p)if($p['normalized_name']===$name)return $p;
        if($line['code']==='18'&&$number===12&&$name==='POLICIA')$name='MAINPRO';
        foreach($candidates as $p)if($p['normalized_name']===$name)return $p;
        foreach($candidates as $p)if($p['normalized_name']!==''&&(str_contains($p['normalized_name'],$name)||str_contains($name,$p['normalized_name'])))return $p;
        return null;
    }
    public static function calculate(array $headers,array $rows,array $colors,array $line): array {
        $controls=self::controls($headers);$base=(int)$line['base_minutes'];$rate=(int)$line['base_rate_cents'];
        $total=0;$ignored=0;$subject=0;$direct=0;$directValue=0;$tolerated=0;$detail=[];
        foreach($rows as $r=>$row)foreach($controls as $c){
            if(!self::red($colors[$r][$c]??'black'))continue;
            $value=trim($row[$c]);$minutes=self::minutes($value);if(!$minutes)continue;
            $total+=$minutes;$header=$headers[$c];$name=self::pointName($header);$point=self::point($header,$line);$type=self::type($header);
            if($point&&$point['sanction']!=='inherit')$type=$point['sanction'];
            $entry=['punto'=>$name,'valor_reporte'=>$value,'minutos'=>$minutes,'valor'=>0.0];
            if($type==='NS'){$ignored+=$minutes;$entry['clasificacion']='NS - IGNORADO';$detail[]=$entry;continue;}
            if($type!=='S'){$entry['clasificacion']='SIN ETIQUETA - IGNORADO';$detail[]=$entry;continue;}
            $tolerance=(int)($point['tolerance_minutes']??0);
            // En Python la tolerancia requiere coincidencia exacta del nombre.
            if($point&&$point['normalized_name']!==$name)$tolerance=0;
            $applied=$tolerance>0;$original=$minutes;$minutes=max(0,$minutes-$tolerance);$tolerated+=$original-$minutes;$entry['minutos']=$minutes;
            if(!$minutes){$entry['minutos_detectados']=$original;$entry['clasificacion']=$line['code']==='23A'?'TOLERANCIA LINEA 23A - PRIMER MINUTO NO COBRABLE':'TOLERANCIA - MINUTOS NO COBRABLES';$detail[]=$entry;continue;}
            $unit=$line['direct_rate_cents']??($point['rate_cents']??null);
            $prefix=$applied?($line['code']==='23A'?'TOLERANCIA LINEA 23A APLICADA | ':'TOLERANCIA APLICADA | '):'';
            if($unit!==null){
                $unit=(int)$unit;$direct+=$minutes;$directValue+=$minutes*$unit;$entry['valor']=$minutes*$unit/100;
                $entry['clasificacion']=$prefix.'FUERA DE BASE - $'.($unit/100).' POR MINUTO';
            }else{
                $subject+=$minutes;$entry['valor']=null;$entry['clasificacion']=$prefix.'SUJETO A BASE - $'.($rate/100).' POR MINUTO DESPUES DE BASE';
            }
            $detail[]=$entry;
        }
        $billable=max(0,$subject-$base);$baseValue=$billable*$rate;
        return ['total_rojo'=>$total,'minutos_ns_ignorados'=>$ignored,'minutos_sujetos_base'=>$subject,'base'=>$base,'minutos_perdonados'=>min($base,$subject),'minutos_cobrables_base'=>$billable,
            'tarifa_minuto_base'=>$rate/100,'valor_minutos_base'=>$baseValue/100,'minutos_fuera_base'=>$direct,'valor_fuera_base'=>$directValue/100,'minutos_tolerancia_linea_23a'=>$tolerated,
            'minutos_a_cobrar'=>$billable+$direct,'valor_cobrar'=>($baseValue+$directValue)/100,'detalle'=>$detail];
    }
    public static function process(string $source,string $originalName,array $config): array {
        $table=self::read($source);$detected=self::detect($table,$config,$originalName);$line=$detected['line'];
        $headers=self::displayHeaders($table['headers'],$line['code']);$vi=self::column($headers,['VEHICULO']);$mi=self::column($headers,['MULTA','MULTAS','VALOR MULTA','VALOR DE MULTA']);
        if($mi===null)throw new \RuntimeException('No se encontró la columna Multa.');
        foreach(self::controls($headers) as $c){
            $point=self::point($headers[$c],$line);
            if($point&&$point['sanction']!=='inherit')$headers[$c]=preg_replace('/\s*\(\s*(?:NS|S)\s*\)\s*$/i','',$headers[$c]).' ('.$point['sanction'].')';
        }
        $groups=[];
        foreach($table['rows'] as $r=>$row){$bus=trim($row[$vi]);if($bus==='')continue;$groups[$bus]['rows'][]=$row;$groups[$bus]['colors'][]=$table['colors'][$r];}
        if(!$groups)throw new \RuntimeException('No se encontraron vehículos en el reporte.');
        $vehicles=[];$omitted=[];
        foreach($groups as $bus=>$group){
            $calculation=self::calculate($headers,$group['rows'],$group['colors'],$line);
            if($calculation['valor_cobrar']<=0){$omitted[]=$bus;continue;}
            if(!preg_match('/\d+/',$bus,$m))throw new \RuntimeException('No se pudo obtener el disco del vehículo '.$bus.'.');
            $vehicles[]=['vehicle'=>$bus,'disc'=>(int)$m[0],...$group,'calculation'=>$calculation];
        }
        if(!$vehicles)throw new \RuntimeException('No existen vehículos con valor a cobrar mayor que $0; no se generó PDF ni Excel.');
        usort($vehicles,fn($a,$b)=>$a['disc']<=>$b['disc']);
        return ['line'=>$line,'date'=>$detected['date'],'headers'=>$headers,'controls'=>self::controls($headers),'vehicle_column'=>$vi,'fine_column'=>$mi,'vehicles'=>$vehicles,'omitted'=>$omitted,
            'total'=>array_sum(array_map(fn($v)=>$v['calculation']['valor_cobrar'],$vehicles))];
    }
}
