<?php use App\Core\Http; ?>
<div class="w-full max-w-lg mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8"><div class="text-center mb-6"><h2 class="text-lg font-bold mb-2">Cambiar contraseña (opcional)</h2><p class="text-sm text-slate-500 mb-6">Esta opción es voluntaria. Puedes continuar utilizando tu cédula como contraseña si así se configuró tu cuenta. Si decides cambiarla, usa una nueva de al menos 12 caracteres.</p>
</div><form method="post" action="<?=Http::e(Http::url('/password'))?>" data-ajax class="space-y-4">
<input type="hidden" name="_csrf" value="<?=Http::e(Http::csrf())?>">
<label class="field">Contraseña actual<input type="password" class="input" name="current" required autocomplete="current-password"></label>
<label class="field">Nueva contraseña<input type="password" class="input" name="new" minlength="12" required autocomplete="new-password"></label>
<label class="field">Confirmar contraseña<input type="password" class="input" name="confirm" minlength="12" required autocomplete="new-password"></label>
<div data-feedback role="alert" class="hidden"></div><button type="submit" class="btn-primary w-full">Guardar contraseña</button>
</form></div>
