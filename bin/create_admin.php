<?php
require dirname(__DIR__).'/vendor/autoload.php';
use App\Core\DB;
$cedula=$argv[1]??'';
if (!preg_match('/^\d{10}$/D',$cedula)) { fwrite(STDERR,"Uso: php bin/create_admin.php CEDULA_10_DIGITOS\n");exit(1); }
$nombres=trim((string)readline('Nombres: '));
$apellidos=trim((string)readline('Apellidos: '));
if (!$nombres || !$apellidos) { fwrite(STDERR,"Se requieren nombres y apellidos.\n");exit(1); }
$s=DB::pdo()->prepare('INSERT INTO users(cedula,nombres,apellidos,password_hash,role) VALUES(?,?,?,?,?)');
$s->execute([$cedula,mb_strtoupper($nombres),mb_strtoupper($apellidos),password_hash($cedula,PASSWORD_DEFAULT),'administrador']);
echo "Administrador creado con usuario = cédula y contraseña = cédula. No se exige modificarla.\n";
