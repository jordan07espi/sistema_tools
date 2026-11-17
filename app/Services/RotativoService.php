<?php
declare(strict_types=1);
namespace App\Services;
use PhpOffice\PhpSpreadsheet\IOFactory;
final class RotativoService {
    private const DAYS=['LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO','DOMINGO'];
    private const MONTHS=['ENERO'=>1,'FEBRERO'=>2,'MARZO'=>3,'ABRIL'=>4,'MAYO'=>5,'JUNIO'=>6,'JULIO'=>7,'AGOSTO'=>8,'SEPTIEMBRE'=>9,'OCTUBRE'=>10,'NOVIEMBRE'=>11,'DICIEMBRE'=>12];
    private const UNIFIED_18=[
        'LINEA 18.- VIA QUEVEDO KM 7/8 - TERMINAL TERRESTRE',
        'LINEA 18.- TERMINAL TERRESTRE - VIA QUEVEDO KM 7/8',
    ];
    public static function norm(mixed $text): string { return PassengerService::normalized($text); }
    public static function header(mixed $text): string {return trim(preg_replace('/[^A-Z0-9]+/',' ',self::norm($text)));}
    public static function code(mixed $value): string { return preg_replace('/\s+/u','',trim((string)$value)); }
    public static function bus(mixed $value): ?string {
        $s=preg_replace('/\s+/','',self::norm($value));
        if ($s===''||$s==='P'||$s==='RH')return null;
        if (preg_match('/^\d+\.0$/',$s))$s=substr($s,0,-2);
        if (preg_match('/^(\d+)(?:\.0)?R$/',$s,$m))$s=$m[1];
        if (!ctype_digit($s))return null;
        return 'EJE'.str_pad((string)(int)$s,3,'0',STR_PAD_LEFT);
    }
    private static function turn(mixed $value): ?int {
        $s=self::norm($value);return preg_match('/^(\d+)/',$s,$m)?(int)$m[1]:null;
    }
    private static function loadSheet(string $file,?string $name=null): array {
        $r=IOFactory::createReaderForFile($file);$r->setReadDataOnly(true);$wb=$r->load($file);
        try {
            $ws=$name?$wb->getSheetByName($name):$wb->getSheet(0);
            if (!$ws)throw new \RuntimeException("No existe la hoja {$name}.");
            return $ws->toArray(null,true,true,false);
        } finally {$wb->disconnectWorksheets();}
    }
    public static function runWithDatabase(string $rotativo,array $settings): array {
        $dbConfig=RouteRepository::settings();
        $rows=self::loadSheet($rotativo,'Hoja1');
        if (self::norm($settings['day']??'')==='TODOS') {
            $allFiles=[];$warnings=[];$dates=[];$count=0;
            foreach(self::DAYS as $day){
                try { $perDay=self::generate($rows,$dbConfig['codes'],array_merge($settings,['day'=>$day]),$dbConfig); }
                catch(\RuntimeException $e){$warnings[]=$day.': '.$e->getMessage();continue;}
                $folder=$perDay['date'].'_'.$day;
                foreach($perDay['files'] as $name=>$data) $allFiles[$folder.'/'.$name]=$data;
                foreach($perDay['warnings'] as $w)$warnings[]=$day.': '.$w;
                $dates[]=$perDay['date'];$count+=count($perDay['files']);
            }
            if(!$allFiles)throw new \RuntimeException('No fue posible generar los archivos semanales.');
            return ['files'=>$allFiles,'warnings'=>$warnings,'date'=>($dates[0]??'').'_AL_'.($dates[6]??''),
              'day'=>'TODOS','corridor'=>self::norm($settings['corridor']??''),'days'=>count($dates),'routes'=>$count];
        }
        return self::generate($rows,$dbConfig['codes'],$settings,$dbConfig);
    }
    public static function mapCodes(array $rows): array {
        $required=['RUTA','CODIGO','CODIGO SDF','CODIGO SF','CODIGO DF','CODRUTA'];
        $keys=[];foreach ($rows[0]??[] as $i=>$cell){$s=self::header($cell);if ($s!=='')$keys[$s]=$i;}
        foreach ($required as $key)if (!isset($keys[$key]))throw new \RuntimeException('El catálogo interno de rutas no contiene el campo '.$key.'.');
        $mapped=[];
        foreach (array_slice($rows,1) as $row) {
            $route=trim((string)($row[$keys['RUTA']]??''));if (!$route)continue;
            $id=self::norm($route);if (isset($mapped[$id]))throw new \RuntimeException('Ruta duplicada en códigos: '.$route);
            $mapped[$id]=['ruta'=>$route,'codigo'=>self::code($row[$keys['CODIGO']]??null),
                'sdf'=>self::code($row[$keys['CODIGO SDF']]??null),'sf'=>self::code($row[$keys['CODIGO SF']]??null),
                'df'=>self::code($row[$keys['CODIGO DF']]??null),'codRuta'=>self::code($row[$keys['CODRUTA']]??null)];
        }
        if (!$mapped)throw new \RuntimeException('No se encontraron códigos de rutas.');
        return $mapped;
    }
    public static function codeForDay(array $route,string $day): array {
        if (in_array($day,array_slice(self::DAYS,0,5),true))return [$route['codigo'],'Lunes - Viernes'];
        if ($day==='SABADO')return $route['sdf']!==''?[$route['sdf'],'Sabado y Domingo']:[$route['sf'],'Sábado'];
        return $route['sdf']!==''?[$route['sdf'],'Sabado y Domingo']:[$route['df'],'Domingo'];
    }
    private static function expectedWeek(array $rows): ?string {
        foreach (array_slice($rows,0,12) as $row) {
            $title=self::norm($row[0]??'');
            if (!str_contains($title,'SEMANA')||!str_contains($title,'DEL'))continue;
            if (!preg_match('/DEL\s+(\d{1,2})\s+DE\s+([A-Z]+)\s+AL\s+(\d{1,2})\s+DE\s+([A-Z]+)\s+DEL\s+(\d{4})/',$title,$m))return null;
            if (!isset(self::MONTHS[$m[2]],self::MONTHS[$m[4]]))return null;
            $y=(int)$m[5];$yStart=self::MONTHS[$m[2]]>self::MONTHS[$m[4]]?$y-1:$y;
            if (!checkdate(self::MONTHS[$m[2]],(int)$m[1],$yStart))return null;
            $first=sprintf('%04d-%02d-%02d',$yStart,self::MONTHS[$m[2]],(int)$m[1]);
            $last=sprintf('%04d-%02d-%02d',$y,self::MONTHS[$m[4]],(int)$m[3]);
            if ((strtotime($last)-strtotime($first))/86400!==6)return null;
            return $first;
        }
        return null;
    }
    private static function records(array $rows,int $start,int $end,int $headerRow,int $turnCol,int $busCol,string $codRoute,string $route,string $corridor,bool $unified,?array $rules=null): array {
        $special=null;
        if($rules!==null){
            foreach($rules as $rule){
                if($rule['corridor']===$corridor && str_starts_with(self::norm($route),$rule['prefix'])){
                    $special=[$rule['from'],$rule['to']];break;
                }
            }
        }else{
            if ($corridor==='QUEVEDO' && str_starts_with(self::norm($route),'LINEA 23A'))$special=[4,6];
            if ($corridor==='SANTA MARTHA' && str_starts_with(self::norm($route),'LINEA 23B'))$special=[6,10];
        }
        $tab=$special?$special[0]:1;$backup=1;$maxTurn=0;$records=[];
        for ($r=$headerRow+1;$r<=$end;$r++) {
            $turn=$rows[$r][$turnCol]??null;
            $turnStr=self::norm($turn);if ($turnStr==='')continue;
            $bus=self::bus($rows[$r][$busCol]??null);
            if ($unified) {
                $number=self::turn($turn);
                if ($number!==null){$local=$number;$maxTurn=max($maxTurn,$number);$backup=max($backup,$number+1);}
                else {$local=$backup++;$maxTurn=max($maxTurn,$local);}
                if ($turnStr==='P'||$bus===null)continue;
                $records[]=['tab'=>$local,'local'=>$local,'codRuta'=>$codRoute,'bus'=>$bus];continue;
            }
            if ($special) {if ($tab>$special[1])break;$local=$tab++;}
            else $local=$tab;
            if ($turnStr==='P'||$bus===null)continue;
            $records[]=['tab'=>$local,'codRuta'=>$codRoute,'bus'=>$bus];
            if (!$special)$tab++;
        }
        return ['records'=>$records,'max_turn'=>$maxTurn];
    }
    private static function csv(string $dayDate,string $schedule,string $code,array $records): string {
        $csv="\xEF\xBB\xBF";
        $csv.='codigo;Tab;codRuta;'.$dayDate."\n";
        $csv.='horario;;;'.$schedule."\n";
        $csv.='flota;;;'.$code."\n";
        foreach ($records as $r) $csv.='turno;'.$r['tab'].';'.$r['codRuta'].';'.$r['bus']."\n";
        return $csv;
    }
    private static function filename(string $route): string {
        return trim(preg_replace('/\s+/u',' ',preg_replace('/[\\\\\/:*?"<>|]/u','_',$route))).'.csv';
    }
    public static function generate(array $rows,array $codeRows,array $settings,array $dbConfig=[]): array {
        $day=self::norm($settings['day']??'');$corridor=self::norm($settings['corridor']??'');
        if (!in_array($day,self::DAYS,true))throw new \InvalidArgumentException('Día no válido.');
        if (!in_array($corridor,['QUEVEDO','SANTA MARTHA'],true))throw new \InvalidArgumentException('Corredor no válido.');
        $start=(string)($settings['start_date']??'');
        $startDate=\DateTimeImmutable::createFromFormat('!Y-m-d',$start);
        if (!$startDate||$startDate->format('Y-m-d')!==$start)throw new \InvalidArgumentException('La fecha inicial debe tener formato AAAA-MM-DD.');
        if ($startDate->format('N')!=='1')throw new \InvalidArgumentException('La fecha inicial debe corresponder a un lunes.');
        $date=$startDate->modify('+'.array_search($day,self::DAYS,true).' days')->format('Y-m-d');
        $warnings=[];$titleStart=self::expectedWeek($rows);
        if ($titleStart && $titleStart!==$start)$warnings[]="La semana detectada en el archivo inicia el {$titleStart}; se usó {$start}.";
        $map=self::mapCodes($codeRows);$routes=[];
        $groups=$dbConfig['groups']??['LINEA 18'=>['filename'=>'LINEA 18.csv', 'routes'=>array_map([self::class,'norm'],self::UNIFIED_18)]];
        $memberToGroup=[];
        foreach($groups as $groupName=>$info)foreach($info['routes'] as $routeName)$memberToGroup[self::norm($routeName)]=$groupName;
        foreach ($rows as $index=>$row) {
            $route=trim((string)($row[0]??''));
            if (str_starts_with(self::norm($route),'LINEA'))$routes[]=['index'=>$index,'route'=>$route];
        }
        if (!$routes)throw new \RuntimeException('No se encontraron rutas que inicien con LINEA en Hoja1.');
        $col=array_search($day,self::DAYS,true)*2;$busCol=$col+1;$files=[];$unified=[];
        foreach ($routes as $i=>$info) {
            $name=$info['route'];$id=self::norm($name);$rule=$map[$id]??null;
            if (!$rule){$warnings[]="{$name}: no está configurada en el catálogo de rutas.";continue;}
            if ($rule['codRuta']===''){$warnings[]="{$name}: sin codRuta.";continue;}
            [$code,$horario]=self::codeForDay($rule,$day);
            if ($code===''){$warnings[]="{$name}: no tiene código para {$day}.";continue;}
            $beg=$info['index'];$end=isset($routes[$i+1])?$routes[$i+1]['index']-1:count($rows)-1;
            $headerRow=null;
            for ($r=$beg;$r<=min($beg+5,$end);$r++) {
                if (str_contains(self::norm($rows[$r][$col]??''),$day)){$headerRow=$r;break;}
            }
            if ($headerRow===null){$warnings[]="{$name}: falta encabezado de {$day}.";continue;}
            $heading=self::norm($rows[$headerRow][$col]??'');
            if (preg_match('/\b(\d{1,2})\b/',$heading,$m) && (int)$m[1] !== (int)substr($date,-2)) {
                $warnings[]="{$name}: día de cabecera {$m[1]} difiere de {$date}.";
            }
            $groupName=$memberToGroup[$id]??null;
            $entry=self::records($rows,$beg,$end,$headerRow,$col,$busCol,$rule['codRuta'],$name,$corridor,$groupName!==null,$dbConfig['rules']??null);
            if ($groupName!==null) {$unified[$groupName][$id]=['name'=>$name,'code'=>$code,'horario'=>$horario,...$entry];continue;}
            if (!$entry['records']){$warnings[]="{$name}: sin buses válidos.";continue;}
            $files[self::filename($name)]=self::csv($date,$horario,$code,$entry['records']);
        }
        foreach($groups as $groupName=>$config){
            $blocks=[];$missing=[];
            foreach($config['routes'] as $name){
                $key=self::norm($name);
                if(isset($unified[$groupName][$key]))$blocks[]=$unified[$groupName][$key];
                else $missing[]=$name;
            }
            if($missing){$warnings[]=$groupName.': faltan rutas para unificar: '.implode(' | ',$missing);continue;}
            if(count(array_unique(array_column($blocks,'code')))!==1||count(array_unique(array_column($blocks,'horario')))!==1){
                $warnings[]=$groupName.': códigos u horarios incompatibles.';continue;
            }
            $combined=[];$offset=0;
            foreach($blocks as $block){
                foreach($block['records'] as $record){$record['tab']=$offset+$record['local'];$combined[]=$record;}
                $offset+=$block['max_turn'];
            }
            $filename=basename((string)($config['filename']??($groupName.'.csv')));
            if($combined)$files[$filename]=self::csv($date,$blocks[0]['horario'],$blocks[0]['code'],$combined);
            else $warnings[]=$groupName.': sin buses válidos.';
        }
        if (!$files)throw new \RuntimeException('No se generó ningún CSV. '.implode(' / ',array_slice($warnings,0,3)));
        return ['files'=>$files,'warnings'=>$warnings,'day'=>$day,'corridor'=>$corridor,'date'=>$date,'route_count'=>count($routes)];
    }
}
