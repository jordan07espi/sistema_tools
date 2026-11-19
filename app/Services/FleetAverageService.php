<?php
declare(strict_types=1);
namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/** Reglas de App_Promedio_Flotas.py; cada asignación cuenta como un turno. */
final class FleetAverageService {
    public const DAYS=['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO','DOMINGO'];
    private const MONTHS=['ENERO'=>1,'FEBRERO'=>2,'MARZO'=>3,'ABRIL'=>4,'MAYO'=>5,'JUNIO'=>6,'JULIO'=>7,'AGOSTO'=>8,'SEPTIEMBRE'=>9,'SETIEMBRE'=>9,'OCTUBRE'=>10,'NOVIEMBRE'=>11,'DICIEMBRE'=>12];
    public static function norm(mixed $v): string {return RotativoService::norm($v);}
    public static function active(mixed $v): bool {
        if(is_bool($v)||$v===null)return false;
        if(is_int($v)||is_float($v))return is_finite((float)$v);
        return (bool)preg_match('/^\d+(?:\.0+)?$/D',self::norm($v));
    }
    public static function bus(mixed $v): ?int {
        if(is_bool($v)||$v===null)return null;
        if(is_int($v)||is_float($v))return $v>0 && floor($v)===$v*1.0?(int)$v:null;
        $s=str_replace(' ','',self::norm($v));
        foreach(['/^EJE[-_]?0*(\d+)$/D','/^0*(\d+)R$/D','/^0*(\d+)$/D','/^0*(\d+)[A-Z]{2,}[-_]?\d+$/D'] as $pattern){
            if(preg_match($pattern,$s,$m))return (int)$m[1]>0?(int)$m[1]:null;
        }
        if(preg_match('/^0*(\d+)\s+[A-Z]/',self::norm($v),$m))return (int)$m[1]>0?(int)$m[1]:null;
        return null;
    }
    public static function number(mixed $v): int {
        if(is_int($v)||is_float($v))return (int)round((float)$v,0,PHP_ROUND_HALF_EVEN);
        $s=str_replace(['$',' '],'',trim((string)$v));
        if(str_contains($s,',')&&str_contains($s,'.'))$s=strrpos($s,',')>strrpos($s,'.')?str_replace(',','.',str_replace('.','',$s)):str_replace(',','',$s);
        elseif(str_contains($s,','))$s=strlen(substr($s,strrpos($s,',')+1))<=2?str_replace(',','.',$s):str_replace(',','',$s);
        if(!is_numeric($s)){
            if(!preg_match('/-?\d+(?:[.,]\d+)?/',$s,$m))return 0;
            $s=str_replace(',','.',$m[0]);
        }
        return (int)round((float)$s,0,PHP_ROUND_HALF_EVEN);
    }
    public static function date(mixed $v): ?string {
        if($v instanceof \DateTimeInterface)return $v->format('Y-m-d');
        $s=trim((string)$v);if($s==='')return null;
        foreach(['Y-m-d','Y/m/d','d/m/Y','d-m-Y','d.m.Y','d/m/y','d-m-y'] as $format){
            $date=\DateTimeImmutable::createFromFormat('!'.$format,substr($s,0,10));
            $errors=\DateTimeImmutable::getLastErrors();
            if($date && (!$errors || (!$errors['warning_count']&&!$errors['error_count'])))return $date->format('Y-m-d');
        }
        return null;
    }
    public static function snapshot(Worksheet $sheet): array {
        $rows=[];
        for($r=1;$r<=$sheet->getHighestDataRow();$r++){
            $row=[];
            for($c=1;$c<=14;$c++){
                $cell=$sheet->getCell([$c,$r]);
                $row[]=$cell->isFormula()?$cell->getOldCalculatedValue():$cell->getValue();
            }
            $rows[]=$row;
        }
        return $rows;
    }
    public static function planning(array $rows): array {
        $title='';
        foreach(array_slice($rows,0,15) as $row){
            foreach(array_slice($row,0,14) as $v)if(str_contains(self::norm($v),'SEMANA')){$title=self::norm($v);break 2;}
        }
        if(!preg_match('/SEMANA\s+DEL\s+(\d{1,2})\s+DE\s+([A-Z]+)\s+AL\s+(\d{1,2})\s+DE\s+([A-Z]+)\s+D(?:E|EL)\s+(\d{4})/',$title,$m))throw new \RuntimeException('No se pudo interpretar la semana de la planificación.');
        $mi=self::MONTHS[$m[2]]??0;$mf=self::MONTHS[$m[4]]??0;$year=(int)$m[5];$endYear=$year+($mf<$mi?1:0);
        // Este módulo conserva la convención del Python: año del inicio.
        if(!checkdate($mi,(int)$m[1],$year)||!checkdate($mf,(int)$m[3],$endYear))throw new \RuntimeException('La planificación contiene fechas inválidas.');
        $start=new \DateTimeImmutable(sprintf('%04d-%02d-%02d',$year,$mi,$m[1]));
        $end=new \DateTimeImmutable(sprintf('%04d-%02d-%02d',$endYear,$mf,$m[3]));
        if((int)$start->diff($end)->format('%r%a')!==6)throw new \RuntimeException('La planificación debe abarcar exactamente siete días.');
        $dates=[];for($d=0;$d<7;$d++)$dates[]=$start->modify("+{$d} days")->format('Y-m-d');
        $cut=count($rows);$titles=[];
        foreach($rows as $r=>$row){
            foreach(array_slice($row,0,14) as $v)if(str_contains(self::norm($v),'PARADA OBLIGATORIA')){$cut=$r;break 2;}
        }
        for($r=0;$r<$cut;$r++)if(preg_match('/^LINEA\b/',self::norm($rows[$r][0]??'')))$titles[]=$r;
        if(!$titles)throw new \RuntimeException('No se encontraron rutas antes de PARADA OBLIGATORIA.');
        $blocks=[];
        foreach($titles as $i=>$r){
            $limit=$titles[$i+1]??$cut;$header=null;
            for($h=$r+1;$h<min($limit,$r+5);$h++){
                $n=0;foreach(array_slice($rows[$h],0,14) as $v)if(preg_match('/^('.implode('|',self::DAYS).')\b/',self::norm($v)))$n++;
                if($n>=3){$header=$h;break;}
            }
            if($header===null)throw new \RuntimeException('No se encontró la cabecera de días de '.($rows[$r][0]??'la ruta').'.');
            $blocks[]=['name'=>trim((string)$rows[$r][0]),'title'=>$r,'header'=>$header,'start'=>$header+1,'end'=>$limit-1];
        }
        return ['dates'=>$dates,'cut'=>$cut,'blocks'=>$blocks];
    }
    private static function columns(array $row): ?array {
        $map=[];foreach($row as $c=>$v)$map[preg_replace('/[^A-Z0-9]+/','',self::norm($v))]=$c;
        $result=[];
        foreach([['VEHICULO','VEHICULOS','DISCO','CARRO'],['FECHA'],['TOTALPASAJEROS','PASAJEROS','TOTALDEPASAJEROS']] as $options){
            $found=null;
            foreach($options as $key)if(isset($map[$key])){$found=$map[$key];break;}
            if($found===null)foreach($map as $key=>$c){foreach($options as $option)if(str_contains((string)$key,$option)){$found=$c;break 2;}}
            if($found===null)return null;
            $result[]=$found;
        }
        return $result;
    }
    public static function liquidations(array $files): array {
        $data=[];$counts=[];$seen=[];
        foreach(array_unique($files) as $file){
            $reader=IOFactory::createReaderForFile($file);
            // Se conservan formatos para reconocer fechas seriales de Excel.
            $book=$reader->load($file);$found=false;
            try {
                foreach($book->getWorksheetIterator() as $sheet){
                    $columns=null;
                    foreach($sheet->toArray(null,true,false,false) as $r=>$row){
                        $header=self::columns($row);
                        if($header!==null){$columns=$header;$found=true;continue;}
                        if($columns===null)continue;
                        [$cb,$cf,$cp]=$columns;$bus=self::bus($row[$cb]??null);$raw=$row[$cf]??null;
                        $date=is_numeric($raw)&&ExcelDate::isDateTime($sheet->getCell([$cf+1,$r+1]))?ExcelDate::excelToDateTimeObject((float)$raw)->format('Y-m-d'):self::date($raw);
                        if($bus===null||$date===null)continue;
                        $passengers=self::number($row[$cp]??null);$identity=$date.'|'.$bus.'|'.$passengers;
                        if(isset($seen[$identity]))continue;
                        $seen[$identity]=true;$key=$date.'|'.$bus;
                        $data[$key]=($data[$key]??0)+$passengers;$counts[$date]=($counts[$date]??0)+1;
                    }
                }
            } finally {$book->disconnectWorksheets();}
            if(!$found)throw new \RuntimeException('Una liquidación no contiene juntas las columnas Vehículo, Fecha y Total Pasajeros.');
        }
        if(!$data)throw new \RuntimeException('No se encontraron registros válidos en las liquidaciones.');
        return ['data'=>$data,'counts'=>$counts];
    }
    public static function calculate(array $rows,array $plan,array $liquidation): array {
        $summary=[];$incidents=[];$cells=[];
        foreach($plan['blocks'] as $block){
            $route=['RUTA'=>$block['name']];
            foreach(self::DAYS as $d=>$day){
                $active=[];$real=[];$pending=[];$total=0;$estimated=0;$missing=0;$date=$plan['dates'][$d];
                for($r=$block['start'];$r<=$block['end'];$r++){
                    $turn=$rows[$r][$d*2]??null;$bus=self::bus($rows[$r][$d*2+1]??null);
                    if(!self::active($turn)||$bus===null)continue;
                    $active[]=[$r,$bus];$key=$date.'|'.$bus;
                    if(!array_key_exists($key,$liquidation)){$pending[]=[$r,$bus,$turn,'NO ENCONTRADO'];continue;}
                    $value=$liquidation[$key];
                    if($value===0){$pending[]=[$r,$bus,$turn,'REGISTRO EN CERO'];continue;}
                    $real[]=$value;$total+=$value;$cells[]=['row'=>$r+1,'col'=>$d*3+3,'value'=>$value,'state'=>'REAL','comment'=>''];
                }
                $mean=$real?(int)round(array_sum($real)/count($real),0,PHP_ROUND_HALF_EVEN):null;
                foreach($pending as [$r,$bus,$turn,$reason]){
                    $state=$mean===null?'SIN DATOS':'ESTIMADO';
                    if($mean===null)$missing++;else{$estimated++;$total+=$mean;}
                    $comment=$reason.'. '.($mean===null?'No existen otros buses con datos reales en esta ruta y fecha.':'Se utilizó el promedio de los otros buses con datos reales de '.$block['name'].' el '.$date.': '.$mean.' pasajeros.');
                    $cells[]=['row'=>$r+1,'col'=>$d*3+3,'value'=>$mean,'state'=>$state,'comment'=>$comment];
                    $incidents[]=['FECHA'=>$date,'RUTA'=>$block['name'],'TURNO'=>$turn,'BUS'=>$bus,'ESTADO'=>$state,'DETALLE'=>$reason,'PASAJEROS'=>$mean];
                }
                $n=count($active);
                $route[$day]=['total_pasajeros'=>$total,'turnos_activos'=>$n,'promedio_turno'=>$n?$total/$n:0,'reales'=>count($real),'estimados'=>$estimated,'sin_dato'=>$missing];
            }
            $route['TOTAL_SEMANA']=array_sum(array_map(fn($day)=>$route[$day]['total_pasajeros'],self::DAYS));
            $route['TURNOS_SEMANA']=array_sum(array_map(fn($day)=>$route[$day]['turnos_activos'],self::DAYS));
            $route['PROMEDIO_SEMANA']=$route['TURNOS_SEMANA']?$route['TOTAL_SEMANA']/$route['TURNOS_SEMANA']:0;
            $summary[]=$route;
        }
        return ['summary'=>$summary,'incidents'=>$incidents,'cells'=>$cells];
    }
    public static function run(string $planning,array $details,string $output,array $sourceNames=[]): array {
        $liquidations=self::liquidations($details);
        $book=IOFactory::load($planning);
        try {
            $sheet=$book->getActiveSheet();$rows=self::snapshot($sheet);$plan=self::planning($rows);
            $result=self::calculate($rows,$plan,$liquidations['data']);
            FleetAverageExportService::save($book,$sheet,$plan,$result,$liquidations['counts'],$output,$sourceNames+['planning'=>basename($planning),'details'=>array_map('basename',$details)]);
            $states=array_count_values(array_column($result['incidents'],'ESTADO'));
            return ['rutas'=>count($plan['blocks']),'fechas_con_datos'=>count(array_filter($plan['dates'],fn($date)=>!empty($liquidations['counts'][$date]))),
                'estimados'=>$states['ESTIMADO']??0,'sin_datos'=>$states['SIN DATOS']??0,'turnos_semana'=>array_sum(array_column($result['summary'],'TURNOS_SEMANA')),
                'total_pasajeros'=>array_sum(array_column($result['summary'],'TOTAL_SEMANA')),'inicio'=>$plan['dates'][0],'fin'=>$plan['dates'][6]];
        } finally {$book->disconnectWorksheets();}
    }
}
