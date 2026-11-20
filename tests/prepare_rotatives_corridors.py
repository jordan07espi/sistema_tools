"""Read the supplied Python and workbook; write comparison fixtures, not configuration.
Usage: python tests/prepare_rotatives_corridors.py App_Rotativo.py report_minutes.py points.xlsx
"""
import csv, importlib.util, io, json, sys
from pathlib import Path
from datetime import date, timedelta
from openpyxl import Workbook

def module(name, path):
    spec=importlib.util.spec_from_file_location(name,path)
    value=importlib.util.module_from_spec(spec)
    sys.modules[name]=value
    spec.loader.exec_module(value)
    return value

m=module('rotatives_updated',sys.argv[1])
cases=[]
for corridor in ['QUEVEDO','SANTA MARTHA']:
    for index,day in enumerate(m.DIAS):
        for line,name in [('19','LINEA 19 BY PASS'),('23A','LINEA 23A JUAN EULOGIO -TSACHILA'),('23B','LINEA 23B JUAN EULOGIO-16 DE MARZO')]:
            for reserved in [False,True]:
                headings=[]
                for i,d in enumerate(m.DIAS):headings += [d+' '+str(16+i),None]
                rows=[[name],headings]
                # Trailing rows exceed the special range; P, RH and empty turns exercise gaps.
                for turn,bus in ([(1,'RH'),('P',21),(3,'88R'),('',99),(4,14),(5,15),(6,16)] if reserved else [(i,10+i) for i in range(1,8)]):rows.append([v for _ in range(7) for v in (turn,bus)])
                ws=Workbook().active
                for row in rows:ws.append(row)
                records,_=m.leer_registros_bloque(ws,2,len(rows),index*2+1,index*2+2,'L'+line,name,corridor)
                mapping={'codigo':'L'+line+'LV','codigo_sdf':'','codigo_sf':'L'+line+'S','codigo_df':'L'+line+'D','codRuta':'L'+line}
                code,source=m.obtener_codigo_segun_dia(mapping,day)
                output=io.StringIO();csv.writer(output,delimiter=';',lineterminator='\n').writerows(m.crear_datos_csv(str(date(2026,11,16)+timedelta(days=index)),m.obtener_horario_segun_fuente(source),code,records))
                cases.append({'rows':rows,'codes':[['RUTA','CODIGO','CODIGO SDF','CODIGO SF','CODIGO DF','codRuta'],[name,mapping['codigo'],'',mapping['codigo_sf'],mapping['codigo_df'],mapping['codRuta']]],'settings':{'day':day,'corridor':corridor,'start_date':'2026-11-16'},'filename':name+'.csv','expected':'\ufeff'+output.getvalue()})

n=module('minutes_updated',sys.argv[2]);rules=n.cargar_reglas_puntos(sys.argv[3]);_,groups=n.cargar_catalogo_rutas(sys.argv[3])
seed=[]
for name,aliases in groups.items():
    _,rule=n.buscar_regla_para_ruta_principal(name,rules);code=n.codigo_linea_canonico(name)
    points=[{'name':p['etiqueta'],'normalized_name':key,'point_number':0,'sanction':'inherit','rate_cents':round(p['valor']*100),'tolerance_minutes':0} for key,p in rule['puntos_especiales'].items()]
    if code=='23A':points += [{'name':label,'normalized_name':n.obtener_nombre_punto(label),'point_number':0,'sanction':'inherit','rate_cents':None,'tolerance_minutes':1} for label in n.PUNTOS_TOLERANCIA_LINEA_23A]
    seed.append({'code':code,'name':name,'base':rule['base'],'base_rate_cents':100 if code=='24' else 50,'direct_rate_cents':100 if code in ('23A','23B') else None,'aliases':[{'name':a,'normalized_name':n.normalizar_para_comparar(a)} for a in dict.fromkeys(aliases)],'points':points})
root=Path(__file__).resolve().parents[1];dest=root/'storage/minutes_validation';dest.mkdir(exist_ok=True)
(dest/'rotatives_corridors.json').write_text(json.dumps(cases,ensure_ascii=False),encoding='utf-8')
(dest/'points_current.json').write_text(json.dumps(seed,ensure_ascii=False),encoding='utf-8')
old=json.loads((root/'database/minutes_seed.json').read_text(encoding='utf-8'))
print(f'{len(cases)} casos CSV calculados por Python. Excel y configuración inicial coinciden: {seed==old}.')
