# Promedios de flotas — 19 de noviembre de 2026

El módulo está disponible en `/flotas`, desde el catálogo y los menús de Sistema Tools. Procesa una planificación semanal y una o varias liquidaciones, sin ejecutar Python en el servidor.

## Uso

1. Seleccionar un cuadro semanal `.xlsx` o `.xlsm`.
2. Seleccionar los archivos de Detalle Liquidación de esa semana. Se admiten `.xlsx`, `.xlsm`, `.xls` binario, `.xls` exportado como HTML y `.csv`. Se requieren las columnas Vehículo, Fecha y Total Pasajeros (incluidos los alias del Python).
3. Generar y descargar el Excel. También queda disponible en el historial, según los permisos del usuario.

Se admiten hasta 19 liquidaciones por envío, sin tope de tamaño para Detalle Liquidación en JavaScript ni en la validación PHP. La planificación mantiene su límite de 30 MB. PHP recibe archivos y peticiones multipart sin tope de bytes (`upload_max_filesize=0`, `post_max_size=0`); Apache utiliza `LimitRequestBody 0`. El tiempo de lectura de la carga y el tiempo de ejecución del cálculo de flotas tampoco tienen un corte fijo. La cantidad de archivos por envío es independiente del tamaño de cada archivo.

Docker carga `Config/uploads.ini`; para activar sus cambios basta `docker compose up -d --no-deps web`. En cPanel se incluyen `.user.ini` (PHP-FPM/CGI) y las directivas condicionales de `.htaccess` (mod_php). Se deben subir también los archivos ocultos. Si el proveedor impone límites mediante proxy, WAF o configuración administrativa de PHP, deberá ajustarlos desde el hosting: el código del proyecto no puede anularlos. El procesamiento sigue sujeto a la RAM y capacidad real del servidor; almacenamiento ilimitado no implica memoria ilimitada.

Referencias: [directivas de PHP](https://www.php.net/manual/en/ini.core.php), [implementación de carga PHP 8.3](https://github.com/php/php-src/blob/PHP-8.3/main/rfc1867.c) y [LimitRequestBody de Apache](https://httpd.apache.org/docs/2.4/mod/core.html#limitrequestbody).

## Resultado y reglas

- Conserva la planificación y añade PASAJEROS después de BUS para cada día. Excluye la sección PARADA OBLIGATORIA y mantiene las demás hojas del libro.
- Genera RESUMEN con totales, turnos activos, promedio por turno, indicadores semanales y cobertura por fecha; genera CONTROL con estimaciones y faltantes.
- Solo participan turnos numéricos con bus válido. Se aceptan reemplazos como `6R`, códigos `EJE006` y valores como `67 JAA3192`; se excluyen P, RH y reservas como R4.
- Se suman registros por fecha/bus, descartando registros idénticos por fecha/bus/pasajeros incluso entre archivos diferentes, igual que en el programa Python.
- Ceros y faltantes se estiman con el promedio de los datos reales de la misma ruta y día. Se usa redondeo a par en los empates, como Python. Sin datos reales, la celda queda vacía.
- Verde = real; amarillo = estimado; rojo = sin datos. Los totales incluyen estimaciones. El denominador del promedio incluye todos los turnos activos con bus válido, incluso los que quedan sin datos.
- Cada asignación de bus cuenta como turno: no se deduplican asignaciones entre rutas o sentidos. Es el comportamiento del Python original.
- Las fechas se leen del título. En semanas que cruzan de año, este módulo conserva la convención de `App_Promedio_Flotas.py`: el año escrito corresponde al inicio de la semana.
- La salida es `.xlsx`, sin macros. Las fórmulas de la planificación se convierten a sus valores almacenados antes de insertar columnas, como en Python; conviene guardar el cuadro desde Excel antes de cargarlo.

## Instalación existente

Actualizar código y ejecutar `php bin/upgrade_fleets.php`. En Docker: `docker compose exec -T web php bin/upgrade_fleets.php`. El arranque habitual también ejecuta la migración mediante `bin/upgrade_routes.php`.

La migración habilita `fleets.view`, `fleets.generate` y `fleets.download` para Monitoreo una sola vez; Administración conserva acceso global. Los demás roles se configuran desde Personal y permisos. Los cambios posteriores de permisos no se revierten al reiniciar. No se alteran usuarios, contraseñas ni reportes existentes.

Se actualizaron los dos puntos de entrada (`index.php` para la instalación en subcarpeta y `public/index.php` para Docker), los recursos JavaScript de ambas ubicaciones y la versión de caché PWA. En cPanel, ejecutar la migración desde la terminal con el mismo PHP configurado para el sitio. No volver a importar la base completa.

## Validación

- `php tests/fleets_logic.php`: reglas numéricas y de fecha, cuatro formatos de liquidación, múltiples hojas/tablas y deduplicación.
- `python tests/compare_fleets.py RUTA_PYTHON CARPETA_LIQUIDACIONES PLAN1 PLAN2 PLAN3` prepara referencias con el Python proporcionado. Requiere pandas, openpyxl y lxml solamente en el equipo de pruebas.
- `php tests/fleets_reference.php`: compara PHP con esas referencias, métricas, cada ruta/día, incidencias y todas las celdas de pasajeros después de escribir y reabrir los Excel. Los archivos privados de prueba se guardan en `storage/fleet_validation/`, excluido de Git.
- `docker compose exec -T -u www-data web php tests/fleets_http.php`: formulario y vistas, rechazo de CSRF, envío real multipart, historial, descarga y limpieza de cargas. Requiere las referencias anteriores y un administrador local existente; crea una sesión efímera y elimina el archivo y job de prueba. Los eventos de auditoría permanecen.
- Agregar `--large` al test HTTP carga un XLS HTML válido de más de 34 MB y verifica que el resultado conserve las mismas cifras, superando tanto el antiguo límite por archivo como el límite por petición.

Los seis casos de equivalencia (tres cuadros con datos reales y tres con datos simulados) coinciden con Python. La liquidación proporcionada contiene únicamente el **12/11/2026**, aunque su nombre menciona el 19/11. Para el cuadro 09–15/11 se obtuvieron 8 rutas, 468 turnos semanales, 36.204 pasajeros incluidos estimados, 10 asignaciones estimadas y 394 sin datos, con cobertura de 1/7 días. No representa una semana con liquidaciones completas. Los cuadros 02–08/11 y 26/10–01/11 carecen de liquidaciones coincidentes en ese archivo y quedan señalados sin datos.
