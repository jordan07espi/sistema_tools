# Reporte Diario de Minutos Caídos

El módulo PHP se abre en `/minutos`. Genera PDF A3 horizontal, Excel RESUMEN y ZIP a partir de un reporte diario XLS (también HTML con extensión XLS), XLSX, XLSM o HTML. Las descargas quedan en el historial y requieren el permiso `minutes.download`.

## Configuración

Un administrador puede abrir `/puntos-control` para crear y editar líneas, rutas secundarias, base diaria, tarifa después de base y cobro directo. En los puntos puede establecer nombre, número opcional, S/NS, tarifa directa y tolerancia por caída. Los cambios se aplican a nuevas generaciones; cada trabajo guarda una copia de sus reglas.

La carga inicial contiene 16 líneas, 74 nombres de rutas y 22 configuraciones de puntos extraídas del Excel y las reglas activas del Python entregado. No hace falta subir nuevamente el Excel de puntos. Los puntos sin configuración usan S/NS del reporte y la regla general de la línea. Deshabilitar una configuración restaura ese comportamiento; para excluir del cobro se debe elegir NS.

Los nombres se comparan sin acentos ni puntuación. Se prioriza coincidencia exacta y número específico; después se permiten abreviaturas por coincidencia parcial para conservar las reglas Python. Las tolerancias requieren coincidencia exacta del nombre. Para LINEA 18, el punto 12 MAINPRO se muestra como POLICIA; su regla MAINPRO se usa si no existe configuración explícita POLICIA.

## Reglas conservadas

- Solo cuentan enteros positivos entre corchetes y texto rojo.
- La base se acumula por vehículo en el día; los puntos directos no consumen base.
- La tarifa directa de toda una línea prevalece sobre la individual.
- Las tolerancias se descuentan por cada caída, antes del cobro.
- NS y puntos sin etiqueta se ignoran, salvo configuración explícita.
- Vehículos con valor cero se omiten. Si todos son cero, se informa sin generar archivos.
- El archivo debe representar una sola línea principal y fecha. Se usa la columna Fecha; el nombre del archivo solo sirve cuando no existe esa columna.
- El PDF conserva la Multa de origen y presenta el importe calculado en el resumen.

## Instalación y actualización

Ejecutar `php bin/upgrade_minutes.php` con la conexión de la instalación configurada. Crea las tablas `minutes_lines`, `minutes_route_aliases`, `minutes_points` y carga la configuración una sola vez. Repetirlo conserva las ediciones. También está integrado en `bin/install.php` y `bin/upgrade_routes.php`.

El administrador tiene acceso completo. Monitoreo recibe inicialmente `minutes.view`, `minutes.generate` y `minutes.download`; pueden ajustarse en permisos. Solo administración puede editar reglas. La aplicación no añade un límite de tamaño a esta carga; siguen aplicando los límites PHP y del servidor.

## Validación local

`tests/prepare_minutes_reference.py` obtiene las referencias desde el Python y el Excel originales, y las guarda en `storage/minutes_validation/expected.json`. `tests/minutes_reference.php` compara 21 casos, incluyendo los cinco vehículos del archivo de ejemplo. `tests/minutes_integration.php` valida lectura del reporte, cálculos y exportaciones.

El ejemplo tiene fecha interna 2026-11-15 aunque su nombre indica 19_11_2026: genera dos vehículos por $3 y $1, y omite tres por valor cero. Se revisaron visualmente ambas páginas PDF.

`tests/minutes_http.php` valida vistas, CSRF, carga multipart, descargas, historial, edición de reglas y conservación de cambios al repetir la migración. Se ejecuta como www-data en Docker y elimina sus registros y archivos temporales. Requiere un administrador activo y la copia del ejemplo en `storage/minutes_validation/reporte_19_11_2026.xls`; usa LINEA 9999 exclusivamente como configuración temporal de prueba.
