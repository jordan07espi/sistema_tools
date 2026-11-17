<?php
if (!function_exists('mb_strtoupper')) { function mb_strtoupper(string $s,string $encoding='UTF-8'): string {return strtoupper(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s));} }
if (!function_exists('transliterator_transliterate')) { function transliterator_transliterate(string $rules,string $s): string {return (string)iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);} }
spl_autoload_register(function(string $class): void {
    if (str_starts_with($class,'App\\')) {
        $file=dirname(__DIR__).'/app/'.str_replace('\\','/',substr($class,4)).'.php';
        if (is_file($file))require $file;
    }
});
$j=json_decode(file_get_contents($argv[1]),true);
$res=App\Services\RotativoService::generate($j['rows'],$j['codes'],$j['config']);
$dest=$argv[2];if (!is_dir($dest))mkdir($dest,0777,true);
foreach ($res['files'] as $name=>$s)file_put_contents($dest.'/'.$name,$s);
echo json_encode(['routes'=>count($res['files']),'warnings'=>$res['warnings']],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n";
