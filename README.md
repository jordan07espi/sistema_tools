# Corrección 30/10/2026 — Exportación Excel y diseño del informe

**Causa confirmada del error 5297E1D3:** `Worksheet\SheetView` no tiene `setShowGridLines()`; ahora la llamada es `Worksheet::setShowGridlines(false)`. La constante `PageSetup::PAPERSIZE_A4` también se mantiene correctamente como entero.

- Se conservan el Excel, PDF y ZIP con ambos formatos dentro de la misma operación. Las descargas individuales permanecen disponibles.
- **PDF:** logo de `Assets/images/logo-ejecuttrans.png` centrado sobre el título; letras de valores de la tabla aumentadas de 7 px a 9,2 px y encabezados de 6,9 px a 8,1 px. El reporte seguirá paginándose cuando no quepa en una sola hoja A4 apaisada. Se mantienen las dos firmas y el pie institucional.
- **Excel:** logo centrado encima del título, encabezados reorganizados y datos a 10 pt; se mantienen ambas hojas (`RESUMEN POR VEHICULO` y `DETALLE FILTRADO`) y los apartados de firmas.
- **Prueba de regresión dentro de Docker:** `docker compose exec web php tests/passengers_integration.php`. Comprueba escritura y relectura de XLSX, creación de PDF y ZIP y campos de aprobaciones. Si la prueba falla, su mensaje identifica la excepción.
- **Instalación:** respaldar código, MySQL y carpeta `storage/`; sustituir solo código; `docker compose up -d --build`; recargar la PWA si conserva una versión anterior. No borres los volúmenes ni importes el SQL nuevamente.
- **Validación en este entorno:** sintaxis PHP y prueba de reglas de pasajeros/rotativos. No se ejecutó Composer/Docker, por lo que la exportación binaria completa debe confirmarse con la prueba anterior en tu equipo.

---

# Actualización de Reporte de Pasajeros y recursos institucionales (30 octubre 2026)

- **Error real identificado:** en `storage/logs/passengers.log` PhpSpreadsheet falló al ejecutar `PageSetup::setPaperSize('A4')`. Corregido con `PageSetup::PAPERSIZE_A4` (constante de la biblioteca) para que el Excel se exporte correctamente.
- Al generar el reporte, ahora se intentan **ambos formatos en una misma operación** y, cuando ambos existen, se entrega también un archivo `ZIP` con los dos. Se conservan los enlaces individuales para `.xlsx` y `.pdf`. Si falla uno, se permite descargar el otro con diagnóstico, sin afirmar falsamente que se generaron ambos.
- **Firmas PDF:** más espacio libre después de la tabla; ambas firmas muestran la línea, `Aprobado por` y en otra línea `Departamento Monitoreo` o `Gerencia`. El Excel incluye el mismo texto y espacio para firmar.
- El formulario ya **no solicita un logotipo**. Tanto Excel como PDF leen directamente `Assets/images/logo-ejecuttrans.png`.
- `Assets/icons/icon-192x192.png` y `Assets/icons/icon-512x512.png` son el origen para iconos PWA, favicon y acceso directo. Al iniciar Docker, `bin/sync_assets.php` los copia a `public/assets/icons/` y sincroniza la imagen para cabecera y login. La carpeta `Assets` se monta en Docker para conservar los cambios del proyecto.
- Los reportes generados se guardan en `storage/exports` y se registran en MySQL como antes. **No es necesaria ninguna migración SQL**; se conservan los usuarios, roles, claves, rutas y volúmenes anteriores.
- **Actualización:** resguardar base y carpeta `storage`; reemplazar el código de la carpeta `sistema_tools`; ejecutar `docker compose up -d --build`. Nunca usar `docker compose down -v`.
- **Prueba dentro de Docker:** `docker compose exec web php tests/passengers_integration.php` comprueba el reporte con datos sintéticos. Opcionalmente se puede indicar la ruta a un Excel de prueba. Este ZIP no incluye documentos privados ni exportaciones históricas, para no sobrescribir datos reales.
- En este entorno se verificó la sintaxis del proyecto y los tests de lógica; **la generación completa mediante Composer/Docker debe verificarse en tu equipo**.

---

# Corrección del reporte de pasajeros (30 de octubre de 2026)

- El Excel de ejemplo trae **9.128 movimientos** en la hoja `1792693898184_demanda_pasajeros`, con encabezados en portugués. Se validó el cálculo de 2026: **9.117 registros**, **19.654 pasajeros**, **$5.665,46**.
- Lectura optimizada: el sistema carga exclusivamente la hoja de movimientos, sin importar todas las tablas dinámicas del libro.
- Exportación optimizada: el detalle se escribe en grupos, con coordenadas estándar y estilo de bordes compatible.
- Excel y PDF se intentan por separado. Si uno falla, el otro puede descargarse; se presenta una advertencia con referencia.
- Los errores técnicos se registran en `storage/logs/passengers.log` con una referencia y etapa del proceso, sin revelar datos privados de los pasajeros.
- Para consultar fallos en Docker: `docker compose logs --tail=100 web` o `docker compose exec web sh -c "tail -n 30 storage/logs/passengers.log"`.
- Al actualizar, conserva tu carpeta `storage` y el volumen MySQL. El ZIP distribuye solo `.gitkeep` en `storage/`, sin archivos de usuarios. No requiere SQL adicional ni `.env`.
- El código fue verificado sin un daemon Docker; la generación completa debe validarse en tu instalación.

---

# Sistema Tools — portal de herramientas y administración separada

## Ajuste de acceso por cédula / cédula (30 de octubre de 2026)

- **Se eliminó la redirección obligatoria a `/password`.** Los usuarios que tienen su contraseña configurada igual a su cédula ingresan directamente a `/dashboard`.
- Al crear personal o usar el botón «Restablecer a cédula», la contraseña queda igual al número de cédula y el servidor la guarda como hash, **sin forzar un cambio posterior**.
- Cuando el contenedor inicia, `bin/disable_forced_password_change.php` limpia la bandera heredada de cambio obligatorio para todos los usuarios. **No reescribe contraseñas existentes.**
- La opción «Cambiar contraseña» en la cuenta queda disponible únicamente si el usuario decide cambiarla voluntariamente.
- La clave existente del administrador **permanece intacta**, igual que las credenciales personalizadas de otros usuarios. Si alguno ya cambió su clave, el administrador puede restaurarla a cédula desde «Personal y permisos».
- No se modifica el diseño del portal, las rutas, las exportaciones ni la PWA. No requiere `.env` ni ejecutar SQL manualmente.

**Nota de seguridad:** cédula/cédula es una credencial fácilmente predecible. Úsala solo en un entorno controlado; para publicar el sistema conviene habilitar contraseñas personales o un segundo factor de autenticación.

## Cambios de acceso, diseño centrado y PWA (29 de octubre de 2026)

- **Ingreso del personal nuevo:** usuario = cédula (10 dígitos) y contraseña = la misma cédula. Se guarda mediante `password_hash` en MySQL. **No se solicita actualizarla al ingresar**; el usuario entra directamente al panel y puede seguir utilizando cédula/cédula.
- **Usuarios existentes y administrador:** conservan su contraseña actual; no se reescribe ningún hash al actualizar el código. El botón **Restablecer a cédula** devuelve el acceso del usuario seleccionado a cédula/cédula, sin cambio obligatorio. La acción no permite que el administrador se restablezca su propia contraseña.
- **Sin cambio obligatorio:** al iniciar Docker, `bin/disable_forced_password_change.php` pone a 0 las banderas heredadas `must_change_password`, sin modificar ningún hash. La opción manual **Cambiar contraseña** sigue siendo voluntaria.
- **Panel administrativo:** herramientas, rutas, personal, formularios y ventanas emergentes centrados. Los campos mantienen etiquetas a la izquierda para facilitar su lectura, pero las tarjetas y los formularios se centran en la pantalla.
- **Tailwind CSS local:** `public/assets/css/tailwind.css` está compilado dentro del proyecto y no depende del script CDN de Tailwind.
- **PWA instalable:** `manifest.webmanifest`, iconos de 192 y 512 px, registro de `service-worker.js` y botón Instalar cuando el navegador lo permite. Solo se guardan en caché recursos estáticos públicos. La página offline es informativa; no es posible generar PDFs/CSV sin conexión al servidor. Para instalar fuera de localhost se necesita HTTPS.

## Cambios de interfaz (29 de octubre de 2026)

- **Usuarios no administradores:** sin sidebar. Utilizan la cabecera horizontal, el catálogo de herramientas con filtros (Todas, Reportes, Operaciones y Mis archivos) y las pantallas de carga tipo iLovePDF, con selección o arrastre de Excel. Únicamente ven módulos permitidos por su rol.
- **Administrador:** mantiene el sidebar del estilo Sistema de Minutos, ordenado por Panel de control, Herramientas, Administración y Mi cuenta.
- **Personal y permisos (`/usuarios`):** la lista de usuarios queda visible; **Nuevo usuario**, **Permisos por rol** y **Editar usuario** abren ventanas modales. Los permisos no son un ítem independiente del sidebar. `/permisos` redirige al modal dentro de `/usuarios`.
- **Configuración de rutas (`/rutas`):** permanece en el panel administrativo con los códigos editables de MySQL, la creación de líneas, SDF/SF/DF y reglas especiales. Los usuarios normales no pueden entrar por URL; el servidor aplica la misma restricción para la administración de personal.
- **Seguridad:** las restricciones se comprueban en PHP, no solo ocultando enlaces. Los roles normales conservan los permisos operativos de vista, generación y descarga de las herramientas. Cambiar permisos no modifica la contraseña ni los datos de los usuarios.

## Actualización de un sistema que ya funciona

1. **Haz copia de seguridad** del directorio actual y de MySQL. El volcado `sistema_tools_db.sql` que enviaste sirve como respaldo, pero **no necesitas importarlo nuevamente**.
2. Sustituye el código conservando tu carpeta **`storage/`** (contiene los archivos y exportaciones previos). Este ZIP trae solo los `.gitkeep` de almacenamiento para no redistribuir documentos privados.
3. Desde la carpeta `sistema_tools` ejecuta `docker compose up -d --build`.
4. Abre `http://localhost:8083`; el administrador verá el panel lateral y los roles normales el catálogo.
5. Si el navegador conserva el estilo anterior, recarga con `Ctrl + F5`; se incrementó el nombre de la caché del service worker PWA.

**No ejecutes `docker compose down -v`: perderías el volumen de datos de MySQL.** No hay cambios nuevos de esquema de base de datos para esta actualización. No se necesita archivo `.env`.

El Docker mantiene los contenedores `sistema_tools_php`, `sistema_tools_db`, `sistema_tools_pma`, la conexión `Config/conexion.php` y los puertos existentes. La contraseña de MySQL sigue la edición anterior para tu entorno local. Antes de publicar la plataforma en Internet, reemplaza las claves fijas y elimina accesos administrativos públicos a phpMyAdmin.

## Validaciones de esta edición

- `php -l` sobre todos los controladores, vistas y archivos PHP: correcto.
- `node --check` sobre los JavaScript: correcto.
- `php tests/logic.php`: validación básica de pasajeros, tarifas y rotativos correcta.
- Renderizado simulado de ambas vistas con comprobación de sidebar exclusivo de administrador, tarjetas de usuario y tres modales: correcto.
- **Pendiente en tu computadora:** ejecución con los contenedores MySQL/Apache y revisión visual real de las pantallas. No se han ejecutado contenedores Docker en este entorno.

---

# Sistema Tools — EJECUTTRANS (estilo Sistema de Minutos)

Esta versión reúne el **diseño de Sistema de Minutos** (menú lateral gris oscuro, azul para elementos activos, tarjetas blancas, login, menú móvil y navegación responsiva) con las **funciones de Sistema Herramientas** (catálogo persistente de rutas, códigos editables, reglas especiales y generación de rotativos de un día o toda la semana). Conserva el módulo de **Reporte de Pasajeros** y los módulos de usuarios, permisos e historial.

## Inicio rápido (sin .env)

Dentro de la carpeta que contiene `docker-compose.yml` ejecuta:

```powershell
docker compose up -d --build
```

- Aplicación: http://localhost:8083
- phpMyAdmin: http://localhost:8084
- MySQL: localhost:3309 desde el PC; `db:3306` dentro de Docker
- Contenedores: `sistema_tools_php`, `sistema_tools_db`, `sistema_tools_pma`
- Base de datos y volumen: `sistema_tools_db`, `sistema_tools_db_data`
- Conexión centralizada: `Config/conexion.php`, mismo estilo que Sistema de Minutos
- El usuario inicial del administrador es la **cédula que se acordó**; la contraseña inicial sigue la configuración de la edición anterior y se almacena exclusivamente como hash bcrypt en `database/seed_admin.sql`.

No se requiere `.env`. Se mantienen las contraseñas de base de datos fijas en `docker-compose.yml` y `Config/conexion.php` para desarrollo local, tal como solicitaste.

## Rotativos: ahora SIN CODIGO_RUTA.xlsx

**Rotativos a CSV** `/rotativos` exige solo el cuadro semanal (.xlsx/.xlsm), el corredor, el día (o **Todos los días**) y la fecha inicial del lunes. Los códigos se consultan desde MySQL. El ZIP incluye un CSV por ruta; si se seleccionan todos los días, los CSV se organizan en carpetas por fecha y día.

**Configuración de rutas** `/rutas` ofrece buscador, edición, alta y habilitación/deshabilitación. Cada ruta tiene:

- Nombre exacto de la línea que aparece en la columna A del cuadro semanal.
- Código **Lunes a Viernes**.
- **SDF** para sábado y domingo o códigos individuales **SF** (sábado) y **DF** (domingo).
- `codRuta` usado en las filas del CSV.

Se cargan **18 rutas iniciales**, provenientes de `sistema_herramientas.zip`, y las excepciones 23A/Quevedo (4–6) y 23B/Santa Martha (6–10). Las reglas especiales pueden modificarse en la misma pantalla. La unificación de línea 18 se mantiene mediante las tablas `unified_route_groups` y `unified_route_members`. La lógica omite P/RH y acepta reemplazos con sufijo R.

Cuando agregues una ruta, escribe **el nombre como aparece en el Excel** para que pueda identificarse. La desactivación mantiene el registro sin usarlo para generar CSV.

## Migración automática sin borrar MySQL

Al arrancar el contenedor PHP se ejecuta `bin/upgrade_routes.php` y después `bin/disable_forced_password_change.php`:

1. Crea las nuevas tablas `routes`, `corridor_rules`, `unified_route_groups`, `unified_route_members` solo si no existen.
2. Si el catálogo de rutas está vacío, lo rellena con las rutas y reglas de `sistema_herramientas`.
3. Si ya contiene rutas, **no reimporta códigos ni sobrescribe tus cambios**.

Los demás datos (`users`, `jobs`, `artifacts`, etc.) permanecen en el volumen existente. **NO uses `docker compose down -v`**: ese comando elimina el volumen de MySQL.

Comandos de soporte:

```powershell
docker compose ps
docker compose logs -f web
docker compose exec web php bin/upgrade_routes.php
docker compose exec web php tests/logic.php
docker compose down
```

## Estructura

```text
sistema_tools/
├─ Config/                # conexion.php, app.php
├─ app/
│  ├─ Controllers/        # Auth, Passenger, Rotativo, Route, Users ...
│  ├─ Services/           # Passenger, Rotativo, RouteRepository ...
│  ├─ Core/               # DB, Auth, Http, Audit, Files ...
│  └─ Views/              # layouts reutilizables, dashboard, rutas, rotativos ...
├─ database/              # schema, seeds, tablas de rutas
├─ bin/                   # upgrade_routes.php, scripts de mantenimiento
├─ public/assets/css/    # estilos separados
├─ public/assets/js/     # AJAX, menú y editores separados
├─ storage/               # exportaciones y uploads temporales
├─ Dockerfile
└─ docker-compose.yml
```

## Módulo Pasajeros y seguridad

Reporte de pasajeros conserva tarifas de **$0,39 completa** y **$0,19 media**, filtros de día, mes y año, Excel/PDF institucional con espacios para firmas, usuarios por cédula, contraseñas con hash, roles, autorizaciones, CSRF y auditoría. Para editar rutas, el usuario debe disponer de permiso `routes.manage`; para consultarlas, `routes.view`. Administrador cuenta con ambos.

**Aviso de seguridad:** la configuración MySQL `root` con clave fija y los puertos publicados solo son adecuados para pruebas locales. Antes de poner el proyecto en Internet, cambia las credenciales del administrador y de MySQL, limita el puerto de MySQL, usa un usuario SQL de privilegios mínimos y HTTPS.

## Verificaciones realizadas / límites

- PHP: revisión de sintaxis de todos los archivos PHP.
- Lógica de pasajeros/rotativos: `php tests/logic.php` satisfactoria.
- Comparación de nueve CSV reales del **viernes 23/10/2026 / Santa Martha** con el ejemplo original de Python: idéntico contenido (ignorando espacios finales/BOM).
- No se dispone de un daemon Docker/MySQL en este entorno; es necesario probar la instalación y la edición persistente de rutas en tu máquina local.

### Inicio en Docker local

Desde esta carpeta ejecuta `docker compose up -d --build` y abre
http://localhost:8083/login. MySQL y phpMyAdmin se publican solo en la interfaz
local. phpMyAdmin está disponible en http://localhost:8084.

La configuración de Docker se define en `docker-compose.yml`. El despliegue
en cPanel conserva el prefijo `/sistema_tools` y su configuración propia.

Para usar un respaldo completo de producción en una instalación local nueva,
copia el SQL a `database/respaldo-local.sql` y sustituye las tres entradas
`/docker-entrypoint-initdb.d/` del servicio `db` por una sola:
`./database/respaldo-local.sql:/docker-entrypoint-initdb.d/01_respaldo.sql:ro`.
Los scripts de inicio de MySQL se ejecutan al inicializar un volumen vacío.
Si ya tienes una base local que quieres conservar, usa un volumen nuevo
cambiando el nombre del volumen en ambas referencias del Compose.
#   s i s t e m a _ t o o l s  
 