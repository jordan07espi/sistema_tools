# Rotativos: actualización por corredor (20/11/2026)

## Horario alternativo para feriados

En Rotativos a CSV se puede elegir `Normal del día seleccionado`, `Usar horario de sábado` o `Usar horario de domingo / feriado`. La fecha, la columna de buses y los turnos se obtienen del día a procesar. El horario alternativo solo cambia el encabezado horario y el código de flota. Se mantiene la prioridad SDF: por ejemplo, un lunes con horario de domingo produce L16D / Domingo y L24SDF / Sabado y Domingo cuando esas son las configuraciones de las rutas.

Si se elige Todos los días, la selección se aplica a toda la semana. Para modificar un único feriado se debe seleccionar ese día. El ZIP identifica el horario alternativo y la selección queda guardada en el historial. No se requieren cambios en la base de datos. `tests/rotatives_schedule.php` verifica fechas, buses, SDF y semana completa.

Reglas verificadas contra `App_Rotativo.py`:

| Corredor | Línea | Tabs |
| --- | --- | --- |
| Quevedo | 23A | 4–6 |
| Santa Martha | 19 BY PASS | 4–6 |
| Santa Martha | 23B | 6–10 |

En estos rangos cada turno no vacío consume una posición, incluso P, RH o un bus inválido. No se exportan esos buses y no se desplazan las posiciones siguientes. Se detiene la lectura al completar el rango. Fuera de las reglas especiales, la numeración empieza en 1 y avanza por bus válido. Los reemplazos como 88R se convierten en EJE088.

La línea 18 une sus dos sentidos y continúa después del último turno activo del primer bloque. Las filas P/RH finales no desplazan el siguiente sentido; las reservas interiores mantienen su posición. No se exige un total fijo de 28: con 8 turnos en el primer sentido y 6 en el segundo, se exportan Tabs 1–14. Las líneas 18 y 19 mantienen códigos LV/S/D; las demás conservan la prioridad SDF sobre SF o DF.

## Actualización de una instalación existente

Después de subir los archivos, ejecutar `php bin/upgrade_rotatives_corridors.php` desde la raíz del proyecto con la conexión de esa instalación. Se actualizan las tres reglas una sola vez. Las ejecuciones posteriores conservan las ediciones hechas en Rutas. También se ejecuta desde `bin/upgrade_rotatives.php` y `bin/upgrade_routes.php`. Las instalaciones nuevas reciben las reglas desde `database/routes_seed.sql`.

## Puntos de control

Se cotejó el Excel entregado con la configuración inicial y con la base local: 16 líneas, 74 rutas y 22 configuraciones de puntos. No presentó diferencias de base, tarifas, nombres de rutas o puntos. Se conservaron los datos existentes. Estas reglas de cobro pertenecen al módulo de minutos y no modifican la numeración Tab de los CSV.

## Validación

`tests/prepare_rotatives_corridors.py` lee los scripts originales y el Excel y genera referencias temporales sin modificar el catálogo. `tests/rotatives_corridors.php` compara 84 CSV completos contra Python, tanto con las reglas internas como con MySQL, verifica la migración idempotente y coteja los puntos. `tests/rotatives_database.php` comprueba carga XLSX, ambos corredores durante una semana completa, línea 18, cambio de año y ZIP.
