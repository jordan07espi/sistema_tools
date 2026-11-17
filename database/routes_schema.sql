-- Catálogo de rutas integrado; independiente de CODIGO_RUTA.xlsx.
CREATE TABLE IF NOT EXISTS routes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(255) NOT NULL,
 normalized_name VARCHAR(255) NOT NULL UNIQUE,
 code_weekdays VARCHAR(50) NOT NULL DEFAULT '',
 code_weekend VARCHAR(50) NOT NULL DEFAULT '',
 code_saturday VARCHAR(50) NOT NULL DEFAULT '',
 code_sunday VARCHAR(50) NOT NULL DEFAULT '',
 cod_ruta VARCHAR(50) NOT NULL DEFAULT '',
 enabled TINYINT(1) NOT NULL DEFAULT 1,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS corridor_rules (
 id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
 corridor VARCHAR(60) NOT NULL,
 route_prefix VARCHAR(80) NOT NULL,
 tab_start INT NOT NULL,
 tab_end INT NOT NULL,
 UNIQUE KEY ux_corridor_route(corridor, route_prefix)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS unified_route_groups (
 id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
 group_code VARCHAR(40) NOT NULL UNIQUE,
 filename VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS unified_route_members (
 group_id INT UNSIGNED NOT NULL,
 route_normalized VARCHAR(255) NOT NULL,
 sort_order INT NOT NULL,
 PRIMARY KEY(group_id,route_normalized)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
