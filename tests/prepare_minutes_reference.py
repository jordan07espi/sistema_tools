"""Importa configuración y prepara casos de comparación desde el Python entregado.
Uso: python tests/prepare_minutes_reference.py SCRIPT_PY PUNTOS_XLSX [REPORTES...]
"""
import importlib.util
import json
import sys
from pathlib import Path

spec=importlib.util.spec_from_file_location('minutes_reference',sys.argv[1])
m=importlib.util.module_from_spec(spec)
sys.modules[spec.name]=m
spec.loader.exec_module(m)
rules=m.cargar_reglas_puntos(sys.argv[2])
aliases,groups=m.cargar_catalogo_rutas(sys.argv[2])
seed=[]
for name,group in groups.items():
    _,rule=m.buscar_regla_para_ruta_principal(name,rules)
    code=m.codigo_linea_canonico(name)
    points=[{'name':p['etiqueta'],'normalized_name':key,'point_number':0,'sanction':'inherit','rate_cents':round(p['valor']*100),'tolerance_minutes':0} for key,p in rule['puntos_especiales'].items()]
    if code=='23A':
        points += [{'name':label,'normalized_name':m.obtener_nombre_punto(label),'point_number':0,'sanction':'inherit','rate_cents':None,'tolerance_minutes':1} for label in m.PUNTOS_TOLERANCIA_LINEA_23A]
    seed.append({'code':code,'name':name,'base':rule['base'],'base_rate_cents':100 if code=='24' else 50,'direct_rate_cents':100 if code in ('23A','23B') else None,'aliases':[{'name':a,'normalized_name':m.normalizar_para_comparar(a)} for a in dict.fromkeys(group)],'points':points})
root=Path(__file__).resolve().parents[1]
(root/'database/minutes_seed.json').write_text(json.dumps(seed,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
tmp=root/'storage/minutes_validation'
tmp.mkdir(exist_ok=True)
cases=[]
for line in seed:
    name=line['name'];_,rule=m.buscar_regla_para_ruta_principal(name,rules)
    headers=['Vehículo','Ruta','Fecha','1.NORMAL (S)','2.IGNORADO (NS)','3.SIN ETIQUETA','4.NORMAL DOS (S)']
    headers += [f'{i+5}.{p["name"]} (S)' for i,p in enumerate(line['points'])]
    if line['code']=='23A': headers += [p+' (S)' for p in m.PUNTOS_TOLERANCIA_LINEA_23A]
    if line['code']=='18': headers += ['12.MAINPRO (S)','12.POLICIA (S)','9.POLICIA (S)']
    headers += ['Multa']
    rows=[];colors=[]
    for i in range(3):
        row=['EJE020',name,'2026-11-19']+[f'08:00 [{v}]' for v in ([10,8,5,2]+[i+1]*(len(headers)-8))]+['$ 0,00']
        row=row[:len(headers)]
        rows.append(row);colors.append(['black']*3+['red']*(len(headers)-4)+['black'])
    effective=m.renombrar_puntos_para_reporte(headers,m.obtener_columnas_control(headers),name)
    expected=m.calcular_minutos_vehiculo(effective,list(zip(rows,colors)),m.obtener_columnas_control(headers),rule['base'],rule['puntos_especiales'],linea=name)
    cases.append({'line':name,'headers':headers,'rows':rows,'colors':colors,'expected':expected})
for file in sys.argv[3:]:
    detection=m.detectar_ruta_y_fecha_automaticas(file,rules,aliases)
    headers,rows,colors=m.leer_archivo_xls(file)
    name=detection['ruta_principal'];rule=detection['regla'];indices=m.obtener_columnas_control(headers);vi=m.obtener_indice_vehiculo(headers)
    headers=m.renombrar_puntos_para_reporte(headers,indices,name)
    for vehicle in m.obtener_vehiculos(rows,vi):
        pairs=[(r,c) for r,c in zip(rows,colors) if r[vi].strip()==vehicle]
        expected=m.calcular_minutos_vehiculo(headers,pairs,indices,rule['base'],rule['puntos_especiales'],linea=name)
        cases.append({'line':name,'headers':headers,'rows':[p[0] for p in pairs],'colors':[p[1] for p in pairs],'expected':expected})
(tmp/'expected.json').write_text(json.dumps(cases,ensure_ascii=False,indent=2),encoding='utf-8')
print(f'{len(seed)} líneas, {sum(len(x["aliases"]) for x in seed)} rutas y {sum(len(x["points"]) for x in seed)} puntos configurados; {len(cases)} casos Python.')
