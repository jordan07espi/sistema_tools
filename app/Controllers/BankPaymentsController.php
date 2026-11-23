<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\{Auth,Http,Files,Audit};
use App\Services\BankPaymentsService;
final class BankPaymentsController {
    public static function index(): void {Auth::requirePermission('bankpayments.view');Http::view('bankpayments/index',['title'=>'Consolidar pagos bancarios','maxStatements'=>max(1,(int)ini_get('max_file_uploads')-1)]);}
    public static function generate(): never {
        Auth::requirePermission('bankpayments.generate');Http::verifyCsrf();set_time_limit(0);
        $uploads=[];$output=null;$status=200;
        try{
            $payments=Files::upload('pagos',['xlsx','xlsm'],null);$uploads[]=$payments;$batch=$_FILES['estados']??null;
            if(!$batch||!is_array($batch['name'])||!count($batch['name']))throw new \RuntimeException('Selecciona al menos un estado de cuenta bancario.');
            $count=count($batch['name']);$expected=filter_var($_POST['statement_count']??$count,FILTER_VALIDATE_INT);
            if($expected!==$count)throw new \RuntimeException('No llegaron todos los estados de cuenta. La selección supera la capacidad de carga del servidor; reduce la cantidad o revisa max_file_uploads.');
            $banks=[];
            foreach($batch['name'] as $i=>$name){foreach(['name','tmp_name','error','size','type'] as $key)$_FILES['_bank_statement'][$key]=$batch[$key][$i]??null;
                $file=Files::upload('_bank_statement',['xlsx','xlsm'],null);$uploads[]=$file;$banks[]=['path'=>$file,'name'=>basename((string)$name)];}
            unset($_FILES['_bank_statement']);[$output,$relative]=Files::exportPath('xlsx');
            $summary=BankPaymentsService::run($payments,$banks,$output);
            $files=Files::persistJob('bankpayments',$summary+['payments'=>basename((string)$_FILES['pagos']['name']),'statements'=>array_column($banks,'name')],[['type'=>'xlsx','name'=>'CONSOLIDADO_PAGOS_BANCARIOS_'.date('Y-m-d_His').'.xlsx','relative'=>$relative]]);$output=null;
            Audit::log('bankpayments.generated',$summary);$payload=['ok'=>true,'summary'=>$summary,'files'=>$files];
        }catch(\RuntimeException|\InvalidArgumentException $e){$status=422;$payload=['ok'=>false,'error'=>$e->getMessage()];}
        catch(\Throwable $e){error_log((string)$e);$status=500;$payload=['ok'=>false,'error'=>'No fue posible consolidar los pagos. Revisa los archivos y el registro del servidor.'];}
        finally{foreach($uploads as $file)if(is_file($file))unlink($file);if($output&&is_file($output))unlink($output);}
        Http::json($payload,$status);
    }
}
