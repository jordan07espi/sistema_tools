<?php use App\Core\Http; ?>
<div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm">
<div class="flex justify-center mb-4"><img src="<?=Http::e(Http::url('/assets/logo-ejecuttrans.png'))?>" class="w-16 h-16 rounded-2xl" alt="Logo EJECUTTRANS"></div>
<h2 class="text-2xl text-gray-800 font-bold text-center mb-2">Acceso al Sistema</h2>
<p class="text-sm text-gray-500 text-center mb-6">Sistema Tools · EJECUTTRANS</p>
<form action="<?=Http::e(Http::url('/login'))?>" method="post" data-ajax data-redirect="<?=Http::e(Http::url('/dashboard'))?>" class="space-y-5">
<input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>">
<label class="field">Número de Cédula<input class="input" name="cedula" inputmode="numeric" pattern="[0-9]{10}" minlength="10" maxlength="10" autocomplete="username" required placeholder="Número de cédula"></label>
<label class="field">Contraseña<div class="relative"><input class="input pr-12" id="login-password" type="password" name="password" autocomplete="current-password" required placeholder="Contraseña"><button type="button" data-toggle-password class="absolute right-0 top-0 bottom-0 px-4 text-gray-500 hover:text-blue-600" aria-label="Mostrar contraseña"><i class="fa-solid fa-eye"></i></button></div></label>
<div data-feedback role="alert" class="hidden"></div>
<button type="submit" class="btn-primary w-full">Ingresar</button>
</form><p class="text-xs text-gray-500 mt-5 text-center">Personal nuevo: ingresa con tu <strong>cédula como usuario y contraseña</strong>, sin cambios obligatorios al entrar.</p><p class="text-xs text-gray-400 mt-2 text-center">Las contraseñas previamente personalizadas siguen vigentes. Si necesitas restablecer el acceso a cédula/cédula, solicita ayuda al administrador.</p>
</div>
