<?php
declare(strict_types=1);
namespace App\Services;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/** Conciliación por comprobante. Importes internos en centavos. */
final class BankPaymentsService {
    private const SYSTEM=[
        'conductor'=>['CONDUCTOR'],'disco'=>['DISCO','VEHICULO','CARRO'],'fecha_obligacion'=>['FECHADEOBLIGACION','FECHAOBLIGACION'],
        'ruta'=>['RUTA','LINEA'],'valor_dia'=>['VALORDELDIA','VALORDIA'],'valor_total_exportado'=>['VALORTOTAL','TOTAL'],
        'fecha_registro'=>['FECHADEREGISTRO','FECHAREGISTRO'],'estado'=>['ESTADO'],'banco'=>['BANCO'],
        'comprobante_original'=>['NDECOMPROBANTE','NUMERODECOMPROBANTE','NRODECOMPROBANTE','COMPROBANTE','DOCUMENTO'],
        'motivo'=>['MOTIVO','OBSERVACION','OBSERVACIONES']];
    private const BANK=['fecha'=>['FECHA'],'documento_original'=>['DOCUMENTO','COMPROBANTE','REFERENCIA','NUMERODEDOCUMENTO'],
        'monto'=>['MONTO','VALOR','IMPORTE'],'concepto'=>['CONCEPTO','DESCRIPCION','DETALLE'],'tipo'=>['TIPO','DEBITOCREDITO','DC'],
        'oficina'=>['OFICINA','AGENCIA'],'codigo'=>['CODIGO','COD'],'saldo'=>['SALDO']];
    public static function clean(mixed $v): string {
        $s=trim((string)$v);return in_array(strtolower($s),['nan','none'],true)?'':preg_replace('/\s+/u',' ',$s);
    }
    public static function norm(mixed $v): string {return RotativoService::norm(self::clean($v));}
    public static function header(mixed $v): string {return preg_replace('/[^A-Z0-9]/','',self::norm($v));}
    public static function identifier(mixed $v): string {return preg_replace('/^(\d+)\.0$/','$1',self::clean($v));}
    public static function document(mixed $v): string {$s=preg_replace('/\D/','',self::identifier($v));return $s===''?'':(ltrim($s,'0')?:'0');}
    public static function cents(mixed $v): ?int {
        if($v===null||$v==='')return null;
        if(is_int($v)||is_float($v))return (int)round($v*100,0,PHP_ROUND_HALF_UP);
        $s=str_replace(['US$','USD','$',' '],'',self::clean($v));
        if(str_contains($s,',')&&str_contains($s,'.'))$s=strrpos($s,',')>strrpos($s,'.')?str_replace(',','.',str_replace('.','',$s)):str_replace(',','',$s);
        elseif(str_contains($s,','))$s=strlen(substr($s,strrpos($s,',')+1))<=2?str_replace(',','.',$s):str_replace(',','',$s);
        $s=preg_replace('/[^0-9.\-]/','',$s);
        if(!preg_match('/^-?(?:\d+(?:\.\d*)?|\.\d+)$/D',$s))return null;
        $negative=str_starts_with($s,'-');$parts=explode('.',ltrim($s,'-'));$dec=str_pad($parts[1]??'',3,'0');
        $value=(int)$parts[0]*100+(int)substr($dec,0,2)+((int)$dec[2]>=5?1:0);return $negative?-$value:$value;
    }
    public static function date(mixed $v): ?string {
        if($v instanceof \DateTimeInterface)return $v->format('Y-m-d');
        $s=substr(self::clean($v),0,10);
        foreach(['d/m/Y','Y-m-d','d-m-Y','d/m/y','Y/m/d'] as $format){$date=\DateTimeImmutable::createFromFormat('!'.$format,$s);$errors=\DateTimeImmutable::getLastErrors();if($date&&(!$errors||!($errors['warning_count']+$errors['error_count'])))return $date->format('Y-m-d');}
        return null;
    }
    private static function sheets(string $path): array {
        try{$reader=IOFactory::createReaderForFile($path);$book=$reader->load($path);}catch(\Throwable $e){throw new \RuntimeException('No se pudo abrir uno de los archivos Excel.',0,$e);}
        try{
            $result=[];
            foreach($book->getWorksheetIterator() as $sheet){
                $rows=$sheet->toArray(null,false,false,false);
                foreach($rows as $r=>&$row)foreach($row as $c=>&$value){
                    $cell=$sheet->getCell([$c+1,$r+1]);
                    if($cell->isFormula())$value=$cell->getOldCalculatedValue();
                    if(is_numeric($value)&&Date::isDateTime($cell))$value=Date::excelToDateTimeObject((float)$value)->format('Y-m-d');
                }
                unset($row,$value);$result[$sheet->getTitle()]=$rows;
            }
            return $result;
        }finally{$book->disconnectWorksheets();}
    }
    private static function locate(array $rows,array $aliases,array $required): ?array {
        foreach(array_slice($rows,0,30) as $r=>$row){$cols=[];foreach($row as $c=>$value){$key=self::header($value);foreach($aliases as $target=>$options)if(!isset($cols[$target])&&in_array($key,$options,true))$cols[$target]=$c;}
            if(!array_diff($required,array_keys($cols)))return [$r,$cols];}
        return null;
    }
    public static function readPayments(string $path): array {
        $payments=[];
        foreach(self::sheets($path) as $sheet=>$rows){
            $located=self::locate($rows,self::SYSTEM,['disco','fecha_obligacion','valor_dia','valor_total_exportado','estado','banco','comprobante_original']);if(!$located)continue;
            [$header,$cols]=$located;
            foreach(array_slice($rows,$header+1) as $i=>$row){
                $p=['hoja'=>$sheet,'fila'=>$header+$i+2];foreach(self::SYSTEM as $key=>$_)$p[$key]=self::clean($row[$cols[$key]??-1]??null);
                $p['comprobante_original']=self::identifier($row[$cols['comprobante_original']]??null);$p['comprobante_normalizado']=self::document($p['comprobante_original']);
                $disc=self::identifier($row[$cols['disco']]??null);$p['disco']=preg_match('/\d+/',$disc,$m)?str_pad((string)(int)$m[0],2,'0',STR_PAD_LEFT):$disc;
                foreach(['valor_dia','valor_total_exportado'] as $key)$p[$key]=self::cents($row[$cols[$key]]??null);
                foreach(['fecha_obligacion','fecha_registro'] as $key)$p[$key]=self::date($row[$cols[$key]??-1]??null);
                if(!($p['disco']||$p['estado']||$p['comprobante_original']||$p['fecha_obligacion']||$p['valor_dia'])||self::norm($p['estado'])==='ANULADO')continue;
                $p['valor_dia']??=0;$p['estado']=$p['estado']?:'SIN ESTADO';$p['banco']=$p['banco']?:'SIN ASIGNAR';$payments[]=$p;
            }
        }
        if(!$payments)throw new \RuntimeException('No se encontraron pagos conciliables. Revisa las columnas Disco, Fecha de obligación, Valor del día, Valor total, Estado, Banco y N.º de comprobante.');
        return $payments;
    }
    public static function readBanks(array $files): array {
        $movements=[];$warnings=[];$seen=[];
        foreach($files as $file){
            $hash=hash_file('sha256',$file['path']);
            if(isset($seen[$hash])){$warnings[]=$file['name'].': archivo idéntico a '.$seen[$hash].'; se procesó una sola vez.';continue;}$seen[$hash]=$file['name'];
            $found=false;
            foreach(self::sheets($file['path']) as $sheet=>$rows){
                $located=self::locate($rows,self::BANK,['fecha','documento_original','monto','concepto']);if(!$located)continue;$found=true;[$header,$cols]=$located;
                $last=0;foreach($rows[$header] as $c=>$value)if($value!==null)$last=$c+1;
                $headers=[];foreach(array_slice($rows[$header],0,$last) as $c=>$value)$headers[]=self::clean($value)?:'Columna '.($c+1);
                foreach(array_slice($rows,$header+1) as $i=>$row){
                    $original=array_slice($row,0,$last);if(!array_filter($original,fn($v)=>$v!==null&&self::clean($v)!==''))continue;
                    $m=['archivo'=>$file['name'],'hoja'=>$sheet,'fila'=>$header+$i+2,'encabezados_originales'=>$headers,'valores_originales'=>$original];
                    foreach(self::BANK as $key=>$_)$m[$key]=self::clean($row[$cols[$key]??-1]??null);
                    foreach(['documento_original','codigo'] as $key)$m[$key]=self::identifier($row[$cols[$key]??-1]??null);
                    $m['documento_normalizado']=self::document($m['documento_original']);$m['fecha']=self::date($row[$cols['fecha']]??null);
                    foreach(['monto','saldo'] as $key)$m[$key]=self::cents($row[$cols[$key]??-1]??null);
                    $m['es_credito']=in_array(self::norm($m['tipo']),['','C','CREDITO','CR'],true)&&$m['monto']!==null&&$m['monto']>0;$movements[]=$m;
                }
            }
            if(!$found)throw new \RuntimeException($file['name'].': no contiene una hoja con Fecha, Documento, Monto y Concepto.');
        }
        if(!$movements)throw new \RuntimeException('No se encontraron movimientos en los estados de cuenta.');
        return ['movements'=>$movements,'warnings'=>$warnings,'bank_files'=>count($seen)];
    }
    private static function unique(array $values,bool $sort=false): array {$values=array_values(array_unique(array_filter($values,fn($v)=>$v!==null&&$v!==''),SORT_STRING));if($sort)sort($values,SORT_STRING);return $values;}
    private static function state(array $values): string {$out=[];$seen=[];foreach($values as $v){$v=self::clean($v);$norm=self::norm($v);if($v!==''&&!isset($seen[$norm])){$seen[$norm]=true;$out[]=$v;}}return implode(' / ',$out)?:'SIN ESTADO';}
    public static function groups(array $payments): array {
        $groups=[];foreach($payments as $p){$key=$p['comprobante_normalizado']!==''?$p['comprobante_normalizado']:'SIN_COMPROBANTE::'.$p['hoja'].'::'.$p['fila'];$groups[$key][]=$p;}$result=[];
        foreach($groups as $key=>$rows){
            $dates=self::unique(array_column($rows,'fecha_obligacion'),true);$registered=self::unique(array_column($rows,'fecha_registro'),true);
            $total=array_sum(array_column($rows,'valor_dia'));$exported=array_values(array_filter(array_column($rows,'valor_total_exportado'),fn($v)=>$v!==null))[0]??null;
            if($total===0&&$exported!==null)$total=$exported;$notes=[];
            if($exported!==null&&abs($exported-$total)>1)$notes[]='Valor total exportado '.number_format($exported/100,2,'.','').' difiere de la suma diaria '.number_format($total/100,2,'.','');
            $notes=array_merge($notes,self::unique(array_column($rows,'motivo'),true));
            $result[]=['clave'=>(string)$key,'comprobante_original'=>self::unique(array_column($rows,'comprobante_original'))[0]??'','comprobante_normalizado'=>$rows[0]['comprobante_normalizado'],'pagos'=>$rows,
                'discos'=>implode(', ',self::unique(array_column($rows,'disco'),true)),'fechas_obligacion'=>implode('; ',array_map(fn($d)=>date('d/m/Y',strtotime($d)),$dates)),
                'fechas_registro'=>implode('; ',array_map(fn($d)=>date('d/m/Y',strtotime($d)),$registered)),'fecha_orden'=>$dates[0]??$registered[0]??null,
                'valor_total'=>$total,'valor_total_exportado'=>$exported,'estado'=>self::state(array_column($rows,'estado')),'banco'=>self::state(array_column($rows,'banco')),'cantidad_obligaciones'=>count($rows),'observacion_sistema'=>implode(' | ',$notes)];
        }return $result;
    }
    private static function suggestions(array $group,array $movements): array {
        $doc=$group['comprobante_normalizado'];if(strlen($doc)<4)return [];
        $dates=self::unique(array_column($group['pagos'],'fecha_registro'),true);$reference=$dates?end($dates):$group['fecha_orden'];$best=[];
        foreach($movements as $m){$candidate=$m['documento_normalizado'];
            if(!$m['es_credito']||$candidate===''||$candidate===$doc||abs($m['monto']-$group['valor_total'])>1||abs(strlen($doc)-strlen($candidate))>2)continue;
            $days=$reference&&$m['fecha']?(int)abs((strtotime($m['fecha'])-strtotime($reference))/86400):9999;if($days!==9999&&$days>45)continue;
            $distance=levenshtein($doc,$candidate);if($distance<1||$distance>2)continue;$score=[$distance,$days,abs(strlen($doc)-strlen($candidate))];
            if(!isset($best[$candidate])||$score<$best[$candidate]['score'])$best[$candidate]=['movement'=>$m,'distance'=>$distance,'score'=>$score];
        }
        $best=array_values($best);usort($best,fn($a,$b)=>($a['score']<=>$b['score'])?:strcmp($a['movement']['documento_normalizado'],$b['movement']['documento_normalizado']));
        return array_map(fn($v)=>[$v['movement'],$v['distance']],array_slice($best,0,3));
    }
    public static function reconcile(array $payments,array $movements): array {
        $index=[];foreach($movements as $m)if($m['es_credito']&&$m['documento_normalizado']!=='')$index[$m['documento_normalizado']][]=$m;
        $items=[];foreach(self::groups($payments) as $g){
            $matches=$index[$g['comprobante_normalizado']]??[];$amount=$matches?array_sum(array_column($matches,'monto')):null;
            $items[]=['grupo'=>$g,'movimientos'=>$matches,'fecha_banco'=>self::unique(array_column($matches,'fecha'),true)[0]??null,
                'documento_banco'=>implode(' / ',self::unique(array_column($matches,'documento_original'))),'monto_banco'=>$amount,
                'concepto_banco'=>implode(' | ',self::unique(array_column($matches,'concepto'))),'archivos_banco'=>implode(' | ',self::unique(array_column($matches,'archivo'))),
                'diferencia'=>$amount===null?null:$amount-$g['valor_total'],'saldo_anterior'=>null,'saldo_acumulado'=>null,'resultado'=>'','encontrado'=>(bool)$matches,'posibles_tipeo'=>$matches?[]:self::suggestions($g,$movements)];
        }
        $buses=[];foreach($items as $i=>$item)if($item['encontrado']&&!str_contains($item['grupo']['discos'],','))$buses[$item['grupo']['discos']][]=$i;
        foreach($buses as $indices){usort($indices,function($a,$b)use($items){$x=$items[$a];$y=$items[$b];return strcmp($x['fecha_banco']??'',$y['fecha_banco']??'')?:strcmp($x['grupo']['fecha_orden']??'',$y['grupo']['fecha_orden']??'')?:strcmp($x['grupo']['comprobante_normalizado'],$y['grupo']['comprobante_normalizado']);});$balance=0;
            foreach($indices as $i){$items[$i]['saldo_anterior']=$balance;$balance+=$items[$i]['diferencia']??0;$items[$i]['saldo_acumulado']=$balance;}}
        foreach($items as &$item){$g=$item['grupo'];
            if(!$item['encontrado'])$item['resultado']=str_contains(self::norm($g['banco']),'GUAYAQUIL')?'NO ENCONTRADO - REVISAR ESTADO DE CUENTA BANCO GUAYAQUIL':($g['comprobante_normalizado']===''?'NO ENCONTRADO - SIN N.º DE COMPROBANTE':(in_array(self::norm($g['banco']),['SIN ASIGNAR','SINASIGNAR'],true)?'NO ENCONTRADO - BANCO SIN ASIGNAR':'NO ENCONTRADO EN LOS ESTADOS DE CUENTA SELECCIONADOS'));
            elseif(array_filter(explode('/',$g['estado']),fn($s)=>self::norm($s)!=='APROBADO'))$item['resultado']='REVISAR - ESTADO '.self::norm($g['estado']);
            elseif(count($item['movimientos'])>1)$item['resultado']='REVISAR - DOCUMENTO REPETIDO EN BANCO';
            elseif(abs($item['diferencia'])<=1)$item['resultado']='CONCILIADO';
            elseif($item['diferencia']>1)$item['resultado']='APROBADO - SALDO A FAVOR';
            elseif($item['saldo_anterior']!==null&&$item['saldo_anterior']>0&&$item['saldo_acumulado']>=-1)$item['resultado']='CONCILIADO CON SALDO ANTERIOR';
            else $item['resultado']='REVISAR - VALOR FALTANTE';
        }unset($item);
        usort($items,fn($x,$y)=>strcmp($y['fecha_banco']??$y['grupo']['fecha_orden']??'',$x['fecha_banco']??$x['grupo']['fecha_orden']??'')?:strcmp($y['grupo']['discos'],$x['grupo']['discos'])?:strcmp($y['grupo']['comprobante_normalizado'],$x['grupo']['comprobante_normalizado']));return $items;
    }
    public static function run(string $payments,array $banks,string $output): array {
        $rows=self::readPayments($payments);$bank=self::readBanks($banks);$items=self::reconcile($rows,$bank['movements']);
        BankPaymentsExportService::write($items,$bank['movements'],$output);
        $found=count(array_filter($items,fn($i)=>$i['encontrado']));
        return ['pagos_leidos'=>count($rows),'comprobantes'=>count($items),'encontrados'=>$found,'no_encontrados'=>count($items)-$found,
            'revisar'=>count(array_filter($items,fn($i)=>$i['encontrado']&&$i['resultado']!=='CONCILIADO')),'posibles_tipeo'=>count(array_filter($items,fn($i)=>(bool)$i['posibles_tipeo'])),
            'movimientos'=>count($bank['movements']),'estados_cuenta'=>$bank['bank_files'],'warnings'=>$bank['warnings']];
    }
}
