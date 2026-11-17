<?php
declare(strict_types=1);
namespace App\Services;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
final class PassengerService {
    public const COMPLETE_CENTS = 39;
    public const HALF_CENTS = 19;
    public const PRODUCTS = ['PAGANTE - URBANO','SOMOS CIUDAD','SOMOS ESPECIALES','SOMOS FUTURO','SOMOS SENIOR'];
    public const COLUMNS = ['FECHA','CONCESIONARIA','LINEA','VEHICULO','CONDUCTOR','TIPO TARIFA','PRODUCTO','CANTIDAD','VALOR UNITARIO','TOTAL'];
    private const ALIASES = [
        'FECHA'=>['DATADOTURNO','DATA','FECHA'],
        'CONCESIONARIA'=>['CONCESSIONARIA','CONCESIONARIA'],
        'LINEA'=>['LINHA','LINEA','RUTA'],
        'VEHICULO'=>['PREFIXO','PREFIJO','VEHICULO','DISCO','CARRO'],
        'CONDUCTOR'=>['MOTORISTA','CONDUCTOR','CHOFER'],
        'FAMILIA'=>['FAMILIA','TIPODETARIFA','TARIFA'],
        'PRODUCTO'=>['PRODUTO','PRODUCTO'],
        'CANTIDAD'=>['QTD','QUANTIDADE','CANTIDAD'],
        'VALOR_UNITARIO'=>['VALORUNITARIO','TARIFAUNITARIA'],
        'TOTAL'=>['TOTAL','VALORTOTAL']
    ];
    public static function normalized(mixed $s): string {
        $s=mb_strtoupper(trim((string)($s??'')),'UTF-8');
        $s=transliterator_transliterate('Any-Latin; Latin-ASCII',$s) ?: $s;
        return trim(preg_replace('/\s+/u',' ',$s));
    }
    public static function colkey(mixed $s): string { return preg_replace('/[^A-Z0-9]+/','',self::normalized($s)); }
    private static function getColumns(array $header): ?array {
        $c=[]; foreach ($header as $i=>$cell) { $k=self::colkey($cell); if ($k!=='') $c[$k]=$i; }
        $out=[];
        foreach (self::ALIASES as $dest=>$opts) {
            $found=null;
            foreach ($opts as $alias) if (isset($c[$alias])) {$found=$c[$alias];break;}
            if ($found===null) foreach ($c as $col=>$index) {foreach ($opts as $alias) {if (str_contains($col,$alias)) {$found=$index;break 2;}}}
            if ($found===null) return null;
            $out[$dest]=$found;
        }
        return $out;
    }
    public static function dateValue(mixed $v): ?string {
        if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d');
        if (is_numeric($v) && (float)$v>20000 && (float)$v<90000) {
            return ExcelDate::excelToDateTimeObject((float)$v)->format('Y-m-d');
        }
        $value=trim((string)$v); if ($value==='') return null;
        foreach (['Y-m-d H:i:s','Y-m-d','d/m/Y H:i:s','d/m/Y','d-m-Y','m/d/Y H:i:s'] as $fmt) {
            $date=\DateTimeImmutable::createFromFormat('!'.$fmt,$value);
            if ($date && $date->format($fmt)===$value) return $date->format('Y-m-d');
        }
        return null;
    }
    public static function amountCents(mixed $v): int {
        if (is_numeric($v)) return (int)round((float)$v*100);
        $t=preg_replace('/(?:US\$|USD|\$|\s)/i','',(string)$v);
        if (str_contains($t,',') && str_contains($t,'.')) {
            $t=strrpos($t,',')>strrpos($t,'.') ? str_replace(',','.',str_replace('.','',$t)) : str_replace(',','',$t);
        } elseif (str_contains($t,',')) $t=str_replace(',','.',$t);
        preg_match('/-?\d+(?:\.\d+)?/',$t,$m);
        return (int)round(((float)($m[0]??0))*100);
    }
    public static function vehicle(mixed $v): string {
        $s=preg_replace('/\s+/','',self::normalized($v));
        if (preg_match('/\d+/',$s,$m)) return 'EJ'.str_pad((string)(int)$m[0],3,'0',STR_PAD_LEFT);
        return $s;
    }
    public static function family(mixed $v): string {
        $t=self::normalized($v);
        if (str_contains($t,'COMPLETA'))return 'TARIFA COMPLETA';
        if (str_contains($t,'MEDIA'))return 'TARIFA MEDIA';
        return $t?:'SIN CLASIFICAR';
    }
    public static function product(mixed $v): string {
        $t=self::normalized($v);
        foreach (self::PRODUCTS as $p) {
            if ($t===str_replace(' - ',' ', $p) || str_contains($t,self::normalized($p)) || ($p==='PAGANTE - URBANO' && $t==='PAGANTE URBANO')) return $p;
        }
        return $t?:'SIN PRODUCTO';
    }
    public static function readExcel(string $file): array {
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        // La exportación original contiene hojas dinámicas. Solo cargar la hoja
        // de movimientos evita consumir memoria con tablas dinámicas innecesarias.
        $names = $reader->listWorksheetNames($file);
        foreach ($names as $name) {
            $reader->setLoadSheetsOnly([$name]);
            $book = $reader->load($file);
            try {
                $sheet = $book->getSheet(0);
                $header = $sheet->rangeToArray('A1:AZ1', null, false, false, false)[0] ?? [];
                $cols = self::getColumns($header);
                if (!$cols) continue;
                $rows = [];
                $max = $sheet->getHighestDataRow();
                for ($start=2; $start<=$max; $start+=500) {
                    $end = min($max, $start+499);
                    foreach ($sheet->rangeToArray('A'.$start.':AZ'.$end, null, false, false, false) as $raw) {
                        if (!isset($raw[$cols['FECHA']]) || $raw[$cols['FECHA']] === null || $raw[$cols['FECHA']] === '') continue;
                        $row = [];
                        foreach ($cols as $key=>$idx) $row[$key] = $raw[$idx] ?? null;
                        $rows[] = $row;
                    }
                }
                if (!$rows) throw new \RuntimeException('La hoja de movimientos está vacía.');
                return ['sheet'=>$name, 'rows'=>$rows];
            } finally {
                $book->disconnectWorksheets();
                unset($book);
            }
        }
        throw new \RuntimeException('No se encontró una hoja de movimientos válida. Debe contener: Data do turno, Concessionária, Linha, Prefixo, Motorista, familia, Produto, Qtd, Valor unitário y Total.');
    }
    public static function processRows(array $rawRows,array $filter,bool $fullFleet=true): array {
        $type=$filter['type']??'todo';
        if (!in_array($type,['todo','anio','mes','dia'],true)) throw new \InvalidArgumentException('Filtro de período no válido.');
        $year=(int)($filter['year']??0); $month=(int)($filter['month']??0);$day=(int)($filter['day']??0);
        if ($type!=='todo' && ($year<2000||$year>2100)) throw new \InvalidArgumentException('Selecciona un año válido.');
        if (in_array($type,['mes','dia'],true)&&($month<1||$month>12)) throw new \InvalidArgumentException('Selecciona un mes válido.');
        if ($type==='dia'&&!checkdate($month,$day,$year)) throw new \InvalidArgumentException('Selecciona una fecha válida.');
        $detail=[];$min=null;$max=null;$unknown=0;$dropped=0;
        foreach ($rawRows as $raw) {
            $date=self::dateValue($raw['FECHA']??null); $vehicle=self::vehicle($raw['VEHICULO']??null);
            if (!$date || !$vehicle) {$dropped++;continue;}
            if ($min===null||$date<$min) $min=$date;
            if ($max===null||$date>$max) $max=$date;
            if ($type==='anio' && (int)substr($date,0,4)!==$year)continue;
            if ($type==='mes' && (substr($date,0,7)!==sprintf('%04d-%02d',$year,$month)))continue;
            if ($type==='dia' && $date!==sprintf('%04d-%02d-%02d',$year,$month,$day))continue;
            $family=self::family($raw['FAMILIA']??null); $quantity=(int)round((float)($raw['CANTIDAD']??0));
            $unit=match($family){'TARIFA COMPLETA'=>self::COMPLETE_CENTS,'TARIFA MEDIA'=>self::HALF_CENTS,default=>self::amountCents($raw['VALOR_UNITARIO']??0)};
            $total=in_array($family,['TARIFA COMPLETA','TARIFA MEDIA'],true)?$unit*$quantity:self::amountCents($raw['TOTAL']??0);
            if (!in_array($family,['TARIFA COMPLETA','TARIFA MEDIA'],true))$unknown++;
            $detail[]=['FECHA'=>$date,'CONCESIONARIA'=>trim((string)($raw['CONCESIONARIA']??'')),
                'LINEA'=>trim((string)($raw['LINEA']??'')),'VEHICULO'=>$vehicle,
                'CONDUCTOR'=>trim((string)($raw['CONDUCTOR']??'')),'TIPO TARIFA'=>$family,
                'PRODUCTO'=>self::product($raw['PRODUCTO']??null),'CANTIDAD'=>$quantity,
                'VALOR UNITARIO'=>$unit,'TOTAL'=>$total];
        }
        if (!$detail) throw new \RuntimeException('No se encontraron registros para el período seleccionado.');
        usort($detail,static fn($a,$b)=>[$a['FECHA'],$a['VEHICULO'],$a['LINEA'],$a['PRODUCTO']] <=> [$b['FECHA'],$b['VEHICULO'],$b['LINEA'],$b['PRODUCTO']]);
        $summary=[];
        if ($fullFleet) for ($n=1;$n<=94;$n++) $summary[sprintf('EJ%03d',$n)]=self::emptyVehicle(sprintf('EJ%03d',$n));
        foreach ($detail as $r) {
            $v=$r['VEHICULO']; if (!isset($summary[$v]))$summary[$v]=self::emptyVehicle($v);
            $s=&$summary[$v];
            if (in_array($r['PRODUCTO'],self::PRODUCTS,true)) $s[$r['PRODUCTO']]+=$r['CANTIDAD'];
            if ($r['TIPO TARIFA']==='TARIFA COMPLETA') { $s['TOTAL COMPLETA']+=$r['CANTIDAD'];$s['VALOR COMPLETA']+=$r['TOTAL']; }
            if ($r['TIPO TARIFA']==='TARIFA MEDIA') { $s['TOTAL MEDIA']+=$r['CANTIDAD'];$s['VALOR MEDIA']+=$r['TOTAL']; }
            $s['TOTAL PASAJEROS']+=$r['CANTIDAD'];$s['TOTAL RECAUDADO']+=$r['TOTAL']; unset($s);
        }
        uksort($summary,static function($a,$b){preg_match('/\d+/',$a,$m);preg_match('/\d+/',$b,$n);return [(int)($m[0]??999999),$a]<=>[(int)($n[0]??999999),$b];});
        $all=['VEHICULO'=>'TOTAL GENERAL']; foreach (self::emptyVehicle('TOTAL GENERAL') as $k=>$v) if ($k!=='VEHICULO')$all[$k]=$v;
        foreach ($summary as $s) foreach ($s as $k=>$v) if ($k!=='VEHICULO') $all[$k]+=$v;
        $period=match($type){'dia'=>sprintf('%02d/%02d/%04d',$day,$month,$year),'mes'=>sprintf('%02d/%04d',$month,$year),'anio'=>'AÑO '.$year,default=> 'DEL '.date('d/m/Y',strtotime($min)).' AL '.date('d/m/Y',strtotime($max))};
        return ['detail'=>$detail,'summary'=>array_values($summary),'totals'=>$all,'period'=>$period,
            'filtered_count'=>count($detail),'unknown_family'=>$unknown,'invalid_rows'=>$dropped];
    }
    private static function emptyVehicle(string $id): array {
        return ['VEHICULO'=>$id,'PAGANTE - URBANO'=>0,'SOMOS CIUDAD'=>0,'TOTAL COMPLETA'=>0,'VALOR COMPLETA'=>0,
            'SOMOS ESPECIALES'=>0,'SOMOS FUTURO'=>0,'SOMOS SENIOR'=>0,'TOTAL MEDIA'=>0,'VALOR MEDIA'=>0,
            'TOTAL PASAJEROS'=>0,'TOTAL RECAUDADO'=>0];
    }
}
