"""Prepara referencias del Python original sin modificar los archivos de entrada.
Uso: python tests/compare_fleets.py script_python carpeta_liquidaciones plan1 [plan2 ...]
Los datos privados temporales quedan en storage/fleet_validation (ignorado en Git).
"""
import dataclasses
import importlib.util
import json
import shutil
import sys
from pathlib import Path
from openpyxl import load_workbook

spec = importlib.util.spec_from_file_location('fleet_reference', sys.argv[1])
module = importlib.util.module_from_spec(spec)
sys.modules[spec.name] = module
spec.loader.exec_module(module)
root = Path(__file__).resolve().parents[1] / 'storage' / 'fleet_validation'
root.mkdir(parents=True, exist_ok=True)
details = []
for i, source in enumerate(Path(sys.argv[2]).glob('*')):
    if source.suffix.lower() in ('.xls', '.xlsx', '.csv', '.xlsm'):
        target = root / f'detail_{i}{source.suffix}'
        shutil.copy2(source, target)
        details.append(str(target))
liquidation, counts = module.cargar_liquidaciones(details)
print('Liquidaciones:', len(liquidation), 'pares fecha/bus; fechas:', ', '.join(str(d) for d in sorted(counts)))
cases = []
for i, source in enumerate(sys.argv[3:]):
    target = root / f'plan_{i}.xlsx'
    shutil.copy2(source, target)
    book = load_workbook(target, data_only=True)
    original = book.active
    dates = module.fechas_de_semana(module.buscar_texto_semana(original))
    cut = module.buscar_fila_corte(original)
    blocks = module.detectar_rutas(original, cut)
    # También incluye ceros, faltantes, duplicados y valores .5 en semanas completas.
    synthetic = root / f'synthetic_{i}.csv'
    with synthetic.open('w', encoding='utf-8', newline='') as f:
        f.write('Vehiculo;Fecha;Total Pasajeros\n')
        for day in dates:
            for bus in range(1, 120):
                if bus % 7 == 0:
                    continue
                value = 0 if bus % 5 == 0 else 100 + bus
                row = f'EJE{bus:03};{day};{value}\n'
                f.write(row)
                if bus % 3 == 0:
                    f.write(row)
    for kind, inputs in [('real', details), ('synthetic', [str(synthetic)])]:
        output = root / f'python_{i}_{kind}.xlsx'
        metrics = module.procesar_archivos(inputs, str(target), str(output))
        data, _ = module.cargar_liquidaciones(inputs)
        wb = load_workbook(target)
        ws = wb.active
        module.preparar_hoja_planificacion(ws, original, cut)
        summary, incidents = module.procesar_rutas(ws, original, blocks, dates, data)
        records = [{**r, **{d: dataclasses.asdict(r[d]) for d in module.DIAS}} for r in summary]
        cells = []
        for block in blocks:
            for d in range(7):
                for r in range(block.fila_inicio, block.fila_fin+1):
                    if module.es_turno_activo(original.cell(r,d*2+1).value) and module.obtener_numero_bus(original.cell(r,d*2+2).value):
                        cells.append([r,d*3+3,ws.cell(r,d*3+3).value])
        metrics.pop('salida')
        case = {'planning': target.name, 'details': [Path(p).name for p in inputs], 'kind': kind, 'metrics': metrics, 'summary': records, 'incidents': incidents, 'cells': cells}
        cases.append(case)
        wb.close()
        print(Path(source).name, kind, metrics)
    book.close()
(root / 'expected.json').write_text(json.dumps({'cases': cases}, default=str, ensure_ascii=False), encoding='utf-8')
