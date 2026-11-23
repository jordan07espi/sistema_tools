"""Read the user's Python and examples; prepare independent PHP comparison fixtures."""
import importlib.util,sys,json,shutil
from pathlib import Path
from dataclasses import asdict
from decimal import Decimal
from datetime import date,datetime,timedelta

spec=importlib.util.spec_from_file_location('bank_reference',sys.argv[1]);m=importlib.util.module_from_spec(spec);sys.modules[spec.name]=m;spec.loader.exec_module(m)
root=Path(__file__).resolve().parents[1];out=root/'storage/bank_validation';out.mkdir(exist_ok=True)
def convert(v):
    if isinstance(v,Decimal):return int(v*100)
    if isinstance(v,(date,datetime)):return v.isoformat()[:10]
    if isinstance(v,dict):return {k:convert(x) for k,x in v.items()}
    if isinstance(v,(list,tuple)):return [convert(x) for x in v]
    return v
def save(name,payments,movements):
    items=m.conciliar(payments,movements)
    data={'payments':[asdict(p) for p in payments],'movements':[asdict(x) for x in movements],'items':[asdict(x) for x in items]}
    (out/(name+'.json')).write_text(json.dumps(convert(data),ensure_ascii=False),encoding='utf-8')
    return items
payments=m.leer_pagos_sistema(sys.argv[2]);movements=m.leer_movimientos_banco(sys.argv[3:]);items=save('real',payments,movements)
shutil.copyfile(sys.argv[2],out/'payments.xlsx')
for i,path in enumerate(sys.argv[3:]):shutil.copyfile(path,out/('bank_'+str(i)+'.xlsx'))
(out/'names.json').write_text(json.dumps([Path(p).name for p in sys.argv[3:]],ensure_ascii=False),encoding='utf-8')
m.generar_excel(str(out/'python.xlsx'),items,movements)
print('Real:',len(payments),'pagos,',len(movements),'movimientos,',len(items),'comprobantes,',sum(x.encontrado for x in items),'encontrados')

def payment(doc,value,day,disc='01',status='APROBADO',bank='PICHINCHA',total=None):
    return m.PagoSistema('Pagos',day,disc,date(2026,10,day),Decimal(value),Decimal(total) if total else None,date(2026,10,day),status,bank,doc,m.normalizar_documento(doc))
def movement(doc,value,day,kind='C',file='bank.xlsx'):
    return m.MovimientoBanco(file,'Banco',day,date(2026,10,day),doc,m.normalizar_documento(doc),Decimal(value),'Transferencia',tipo=kind,es_credito=kind=='C' and Decimal(value)>0,encabezados_originales=['Fecha','Documento','Monto','Codigo','Concepto'],valores_originales=['2026-10-%02d'%day,doc,float(value),'001','Transferencia'])
p=[payment('0001001','4',1,total='10'),payment('1001','6',2,total='10'),payment('1002','15',3),payment('1003','20',4),payment('1004','5',5),payment('1005','7',6,status='PENDIENTE'),payment('1006','8',7),payment('2001','9',8),payment('','3',9),payment('3001','2',10,bank='GUAYAQUIL'),payment('4001','2',11,bank='SIN ASIGNAR'),payment('987654','4',12),payment('5001','1',13),payment('6001','0',14,total='12'),payment('7001','1',15,disc='02'),payment('7001','2',16,disc='03')]
b=[movement('1001','10',1),movement('1002','20',3),movement('1003','15',4),movement('1004','1',5),movement('1005','7',6),movement('1006','4',7),movement('1006','4',7,file='second.xlsx'),movement('2001','9',8,kind='D'),movement('987655','4',12),movement('987664','4',13),movement('987674','5',13),movement('5001','1.01',13),movement('6001','12',14),movement('7001','3',16)]
save('edge',p,b);print('Synthetic:',len(p),'pagos,',len(b),'movimientos')
