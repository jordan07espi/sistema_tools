INSERT IGNORE INTO role_permissions (role, permission) VALUES
('contabilidad','bankpayments.view'),('contabilidad','bankpayments.generate'),('contabilidad','bankpayments.download'),
('monitoreo','minutes.view'),('monitoreo','minutes.generate'),('monitoreo','minutes.download'),
('monitoreo','fleets.view'),('monitoreo','fleets.generate'),('monitoreo','fleets.download'),
('monitoreo','passengers.view'),('monitoreo','passengers.generate'),('monitoreo','passengers.download'),
('monitoreo','rotatives.view'),('monitoreo','rotatives.generate'),('monitoreo','rotatives.download'),
('secretaria','passengers.view'),('secretaria','passengers.download'),
('contabilidad','passengers.view'),('contabilidad','passengers.generate'),('contabilidad','passengers.download'),
('operativo','rotatives.view'),
('monitoreo','routes.view');
