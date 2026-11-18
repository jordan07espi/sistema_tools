-- Ejecutar una sola vez mediante bin/upgrade_rotatives.php.
UPDATE IGNORE routes SET name='LINEA 18 VIA QUEVEDO KM8 - TERMINAL T.', normalized_name='LINEA 18 VIA QUEVEDO KM8 - TERMINAL T.'
WHERE normalized_name='LINEA 18.- VIA QUEVEDO KM 7/8 - TERMINAL TERRESTRE';
UPDATE IGNORE routes SET name='LINEA 18 TERMINAL T. - VIA QUEVEDO KM8', normalized_name='LINEA 18 TERMINAL T. - VIA QUEVEDO KM8'
WHERE normalized_name='LINEA 18.- TERMINAL TERRESTRE - VIA QUEVEDO KM 7/8';
INSERT INTO routes (name,normalized_name,code_weekdays,code_weekend,code_saturday,code_sunday,cod_ruta) VALUES
('LINEA 18 VIA QUEVEDO KM8 - TERMINAL T.','LINEA 18 VIA QUEVEDO KM8 - TERMINAL T.','L18LV','','L18S','L18D','L18'),
('LINEA 18 TERMINAL T. - VIA QUEVEDO KM8','LINEA 18 TERMINAL T. - VIA QUEVEDO KM8','L18LV','','L18S','L18D','L18'),
('LINEA 19 BY PASS','LINEA 19 BY PASS','L19LV','','L19S','L19D','L19')
ON DUPLICATE KEY UPDATE code_weekdays=VALUES(code_weekdays),code_weekend=VALUES(code_weekend),code_saturday=VALUES(code_saturday),code_sunday=VALUES(code_sunday),cod_ruta=VALUES(cod_ruta);
INSERT INTO unified_route_groups (group_code,filename) VALUES ('LINEA 18','LINEA 18.csv')
ON DUPLICATE KEY UPDATE filename=VALUES(filename);
DELETE m FROM unified_route_members m JOIN unified_route_groups g ON g.id=m.group_id WHERE g.group_code='LINEA 18';
INSERT INTO unified_route_members (group_id,route_normalized,sort_order)
SELECT id,'LINEA 18 VIA QUEVEDO KM8 - TERMINAL T.',1 FROM unified_route_groups WHERE group_code='LINEA 18';
INSERT INTO unified_route_members (group_id,route_normalized,sort_order)
SELECT id,'LINEA 18 TERMINAL T. - VIA QUEVEDO KM8',2 FROM unified_route_groups WHERE group_code='LINEA 18';
