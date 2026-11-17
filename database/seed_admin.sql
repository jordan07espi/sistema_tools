-- Administrador inicial de entorno LOCAL. Contraseña: hash bcrypt (sin clave en texto plano).
-- Se ejecuta automáticamente al crear por primera vez el volumen MySQL.
-- No sobreescribe contraseñas existentes si vuelves a ejecutar el instalador.
INSERT INTO users
    (cedula, nombres, apellidos, password_hash, role, active, must_change_password)
SELECT
    '2350637217', 'JORDAN ENRIQUE', 'ESPINOSA VINUEZA',
    '$2y$12$IAI3ETFn42eznKnuMVdIxeldKvLfCZJ660qQEOI7WGqw0JDig4ce.',
    'administrador', 1, 0
WHERE NOT EXISTS (SELECT 1 FROM users WHERE cedula = '2350637217');
