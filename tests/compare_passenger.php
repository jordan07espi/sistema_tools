<?php
if (!function_exists('mb_strtoupper')) { function mb_strtoupper(string $s,string $encoding='UTF-8'): string {return strtoupper(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s));} }
if (!function_exists('transliterator_transliterate')) { function transliterator_transliterate(string $rules,string $s): string {return (string)iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);} }
spl_autoload_register(function(string $class): void {
    if (str_starts_with($class,'App\\')) {
        $file=dirname(__DIR__).'/app/'.str_replace('\\','/',substr($class,4)).'.php';
        if (is_file($file))require $file;
    }
});
$r=json_decode(file_get_contents($argv[1]),true);
$rep=App\Services\PassengerService::processRows($r,['type'=>'anio','year'=>2026],true);
file_put_contents($argv[2],json_encode($rep,JSON_UNESCAPED_UNICODE));
echo 'Vehículos: '.count($rep['summary'])."\nPasajeros: ".$rep['totals']['TOTAL PASAJEROS']."\nRecaudación (centavos): ".$rep['totals']['TOTAL RECAUDADO']."\nRegistros: ".$rep['filtered_count']."\n";
