<?php
/** Pruebas de reglas de acceso, sin necesidad de MySQL. */
$base=dirname(__DIR__);
$index=file_get_contents($base.'/public/index.php');
$auth=file_get_contents($base.'/app/Controllers/AuthController.php');
$users=file_get_contents($base.'/app/Controllers/UserController.php');
$docker=file_get_contents($base.'/docker-compose.yml');
assert(strpos($index, "must_change_password")===false, 'No debe existir redirección obligatoria');
assert(strpos($auth, "'redirect'=>'/dashboard'")!==false, 'La redirección debe ser al panel');
assert(strpos($users, 'password_hash($cedula,PASSWORD_DEFAULT)')!==false, 'La clave inicial debe ser la cedula cifrada');
assert(strpos($users, 'must_change_password=1')===false, 'El reset no debe exigir cambio');
assert(strpos($docker, 'disable_forced_password_change.php')!==false, 'La migración debe correr al iniciar Docker');
$hash=password_hash('0123456789',PASSWORD_DEFAULT);
assert(password_verify('0123456789',$hash), 'Cedula/cedula debe verificarse');
assert(!password_verify('otra',$hash), 'No debe aceptar otra contraseña');
echo "OK: inicio cédula/cédula sin cambio forzado.\n";
