-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 18-11-2026 a las 15:11:16
-- Versión del servidor: 8.4.10
-- Versión de PHP: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_tools_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `artifacts`
--

CREATE TABLE `artifacts` (
  `id` bigint UNSIGNED NOT NULL,
  `job_id` bigint UNSIGNED NOT NULL,
  `file_type` varchar(20) NOT NULL,
  `filename` varchar(200) NOT NULL,
  `relative_path` varchar(250) NOT NULL,
  `file_size` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `artifacts`
--

INSERT INTO `artifacts` (`id`, `job_id`, `file_type`, `filename`, `relative_path`, `file_size`, `created_at`) VALUES
(1, 1, 'zip', 'ROTATIVOS_2026-10-26_QUEVEDO.zip', 'exports/b129025f71d9b69837a21dc76483d8da990f68a6.zip', 2473, '2026-10-26 21:56:17'),
(2, 2, 'zip', 'ROTATIVOS_2026-10-26_AL_2026-11-01_QUEVEDO.zip', 'exports/81712f834ab61c6098362f5ed329ea36afd1354d.zip', 18063, '2026-10-26 22:18:14'),
(3, 3, 'zip', 'ROTATIVOS_2026-10-26_AL_2026-11-01_QUEVEDO.zip', 'exports/297b001c74b5604e15079289e7edf980b3497f2e.zip', 18063, '2026-10-30 15:03:05'),
(4, 4, 'pdf', 'PASAJEROS_20261030_102550.pdf', 'exports/b539d1434ac310aa0874768dedc2d3656f3daeca.pdf', 53104, '2026-10-30 15:25:51'),
(5, 5, 'pdf', 'PASAJEROS_20261030_112558.pdf', 'exports/f2d0caed06b4713d51dbc022cd952c594fee6e02.pdf', 156103, '2026-10-30 16:25:59'),
(6, 6, 'zip', 'PASAJEROS_20261030_122233_EXCEL_Y_PDF.zip', 'exports/533f9b4eb4f27f48ae1e81816ba0076ed2c4e294.zip', 534153, '2026-10-30 17:22:36'),
(7, 6, 'xlsx', 'PASAJEROS_20261030_122233.xlsx', 'exports/ff5e19a390fe70bdf97062e0236a30a4e51c1f17.xlsx', 447393, '2026-10-30 17:22:36'),
(8, 6, 'pdf', 'PASAJEROS_20261030_122233.pdf', 'exports/cdbe2302ebb4a7b0f8cff4253e944b9f14570567.pdf', 157781, '2026-10-30 17:22:36'),
(9, 7, 'zip', 'PASAJEROS_20261030_122319_EXCEL_Y_PDF.zip', 'exports/ac7967f72cb4a787c48911394e23df7e435c18a8.zip', 534152, '2026-10-30 17:23:21'),
(10, 7, 'xlsx', 'PASAJEROS_20261030_122319.xlsx', 'exports/f413dbc1fb2e3eed076546e2c5f463e75ebd9d8e.xlsx', 447394, '2026-10-30 17:23:21'),
(11, 7, 'pdf', 'PASAJEROS_20261030_122319.pdf', 'exports/d3e205f973b3c37f14dee77f0aa587274cb2fefd.pdf', 157781, '2026-10-30 17:23:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `detail` json DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `detail`, `ip`, `created_at`) VALUES
(1, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-26 21:49:58'),
(2, 1, 'rotatives.generated', '{\"day\": \"LUNES\", \"date\": \"2026-10-26\", \"count\": 8}', '172.26.0.1', '2026-10-26 21:56:17'),
(3, 1, 'artifact.download', '{\"id\": 1}', '172.26.0.1', '2026-10-26 21:56:20'),
(4, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-26 22:16:53'),
(5, 1, 'rotatives.generated', '{\"day\": \"TODOS\", \"date\": \"2026-10-26_AL_2026-11-01\", \"count\": 56}', '172.26.0.1', '2026-10-26 22:18:14'),
(6, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-29 16:45:53'),
(7, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-29 17:31:31'),
(8, 1, 'users.created', '{\"role\": \"monitoreo\", \"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-29 17:33:08'),
(9, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-29 17:33:49'),
(10, 2, 'auth.password_changed', '[]', '172.26.0.1', '2026-10-29 17:34:18'),
(11, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-29 17:37:20'),
(12, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-29 18:00:34'),
(13, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-29 18:02:42'),
(14, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-29 20:34:18'),
(15, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-29 20:34:47'),
(16, 1, 'users.password_reset', '{\"user_id\": 2}', '172.26.0.1', '2026-10-29 20:35:16'),
(17, 2, 'auth.logout', '[]', '172.26.0.1', '2026-10-29 20:35:21'),
(18, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-29 20:35:33'),
(19, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-30 15:01:41'),
(20, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-30 15:02:07'),
(21, 2, 'rotatives.generated', '{\"day\": \"TODOS\", \"date\": \"2026-10-26_AL_2026-11-01\", \"count\": 56}', '172.26.0.1', '2026-10-30 15:03:05'),
(22, 2, 'artifact.download', '{\"id\": 3}', '172.26.0.1', '2026-10-30 15:05:14'),
(23, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-30 15:20:32'),
(24, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-30 15:25:34'),
(25, 2, 'passengers.generated', '{\"formats\": [\"pdf\"], \"records\": 9117, \"income_cents\": 566546}', '172.26.0.1', '2026-10-30 15:25:51'),
(26, 2, 'artifact.download', '{\"id\": 4}', '172.26.0.1', '2026-10-30 15:26:01'),
(27, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-30 16:25:00'),
(28, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-30 16:25:12'),
(29, 2, 'passengers.generated', '{\"formats\": [\"pdf\"], \"records\": 9117, \"income_cents\": 566546}', '172.26.0.1', '2026-10-30 16:25:59'),
(30, 2, 'artifact.download', '{\"id\": 5}', '172.26.0.1', '2026-10-30 16:26:18'),
(31, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-10-30 17:21:52'),
(32, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-30 17:21:56'),
(33, 2, 'passengers.generated', '{\"formats\": [\"zip\", \"xlsx\", \"pdf\"], \"records\": 9117, \"income_cents\": 566546}', '172.26.0.1', '2026-10-30 17:22:36'),
(34, 2, 'artifact.download', '{\"id\": 7}', '172.26.0.1', '2026-10-30 17:23:07'),
(35, 2, 'passengers.generated', '{\"formats\": [\"zip\", \"xlsx\", \"pdf\"], \"records\": 9117, \"income_cents\": 566546}', '172.26.0.1', '2026-10-30 17:23:21'),
(36, 2, 'artifact.download', '{\"id\": 9}', '172.26.0.1', '2026-10-30 17:23:23'),
(37, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-10-30 17:48:02'),
(38, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-11-17 15:11:58'),
(39, 1, 'auth.login', '{\"cedula\": \"2350637217\"}', '172.26.0.1', '2026-11-18 15:02:00'),
(40, 2, 'auth.login', '{\"cedula\": \"1714309844\"}', '172.26.0.1', '2026-11-18 15:03:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `corridor_rules`
--

CREATE TABLE `corridor_rules` (
  `id` int UNSIGNED NOT NULL,
  `corridor` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `route_prefix` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tab_start` int NOT NULL,
  `tab_end` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `corridor_rules`
--

INSERT INTO `corridor_rules` (`id`, `corridor`, `route_prefix`, `tab_start`, `tab_end`) VALUES
(1, 'Quevedo', 'LINEA 23A', 4, 6),
(2, 'Santa Martha', 'LINEA 23B', 6, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `module` varchar(40) NOT NULL,
  `parameters` json NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `jobs`
--

INSERT INTO `jobs` (`id`, `user_id`, `module`, `parameters`, `created_at`) VALUES
(1, 1, 'rotatives', '{\"day\": \"Lunes\", \"corridor\": \"Quevedo\", \"start_date\": \"2026-10-26\"}', '2026-10-26 21:56:17'),
(2, 1, 'rotatives', '{\"day\": \"TODOS\", \"corridor\": \"Quevedo\", \"start_date\": \"2026-10-26\"}', '2026-10-26 22:18:14'),
(3, 2, 'rotatives', '{\"day\": \"TODOS\", \"corridor\": \"Quevedo\", \"start_date\": \"2026-10-26\"}', '2026-10-30 15:03:05'),
(4, 2, 'passengers', '{\"day\": 26, \"hoja\": \"1792693898184_demanda_pasajeros\", \"type\": \"anio\", \"year\": 2026, \"month\": 10, \"flota_completa\": true}', '2026-10-30 15:25:51'),
(5, 2, 'passengers', '{\"day\": 26, \"hoja\": \"1792693898184_demanda_pasajeros\", \"type\": \"anio\", \"year\": 2026, \"month\": 1, \"flota_completa\": true}', '2026-10-30 16:25:59'),
(6, 2, 'passengers', '{\"day\": 26, \"hoja\": \"1792693898184_demanda_pasajeros\", \"type\": \"anio\", \"year\": 2026, \"month\": 10, \"flota_completa\": true}', '2026-10-30 17:22:36'),
(7, 2, 'passengers', '{\"day\": 26, \"hoja\": \"1792693898184_demanda_pasajeros\", \"type\": \"anio\", \"year\": 2026, \"month\": 10, \"flota_completa\": true}', '2026-10-30 17:23:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint UNSIGNED NOT NULL,
  `cedula` char(10) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission` varchar(90) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `role_permissions`
--

INSERT INTO `role_permissions` (`role`, `permission`) VALUES
('contabilidad', 'passengers.download'),
('contabilidad', 'passengers.generate'),
('contabilidad', 'passengers.view'),
('monitoreo', 'passengers.download'),
('monitoreo', 'passengers.generate'),
('monitoreo', 'passengers.view'),
('monitoreo', 'rotatives.download'),
('monitoreo', 'rotatives.generate'),
('monitoreo', 'rotatives.view'),
('monitoreo', 'routes.view'),
('operativo', 'rotatives.view'),
('secretaria', 'passengers.download'),
('secretaria', 'passengers.view');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `routes`
--

CREATE TABLE `routes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `normalized_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_weekdays` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `code_weekend` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `code_saturday` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `code_sunday` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cod_ruta` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `routes`
--

INSERT INTO `routes` (`id`, `name`, `normalized_name`, `code_weekdays`, `code_weekend`, `code_saturday`, `code_sunday`, `cod_ruta`, `enabled`, `updated_at`) VALUES
(1, 'LINEA 7 CADENA-HOSPITAL SD', 'LINEA 7 CADENA-HOSPITAL SD', 'L07LV', 'L07SDF', '', '', 'L007', 1, '2026-10-26 22:16:45'),
(2, 'LINEA 10A BELLAVISTA -KASAMA', 'LINEA 10A BELLAVISTA -KASAMA', 'L10ALV', '', '', 'L10AD', 'L10A', 1, '2026-10-26 22:16:45'),
(3, 'LINEA 10B ARROYOS-KASAMA', 'LINEA 10B ARROYOS-KASAMA', 'L10BLV', '', 'L10BS', '', 'L10B', 1, '2026-10-26 22:16:45'),
(4, 'LINEA 16 CHIGUILPE, MAYA- TERMINAL T.', 'LINEA 16 CHIGUILPE, MAYA- TERMINAL T.', 'L16LV', '', 'L16S', 'L16D', 'L16', 1, '2026-10-26 22:16:45'),
(5, 'LINEA 17 UTE ,QUEZADA -PORTAL', 'LINEA 17 UTE ,QUEZADA -PORTAL', 'L17ABLV', '', 'L17ABS', 'L17ABD', 'L17AB', 1, '2026-10-26 22:16:45'),
(6, 'LINEA 20 CIUDAD VERDE -TERMINAL', 'LINEA 20 CIUDAD VERDE -TERMINAL', 'L20LV', 'L20SDF', '', '', 'L20', 1, '2026-10-26 22:16:45'),
(7, 'LINEA 23A JUAN EULOGIO -TSACHILA', 'LINEA 23A JUAN EULOGIO -TSACHILA', 'L23ALV', '', 'L23AS', 'L23AD', 'L23A', 1, '2026-10-26 22:16:45'),
(8, 'LINEA 23B JUAN EULOGIO-16 DE MARZO', 'LINEA 23B JUAN EULOGIO-16 DE MARZO', 'L23BLV', '', 'L23BS', 'L23BD', 'L23B', 1, '2026-10-26 22:16:45'),
(9, 'LINEA 24 ANILLO VIAL', 'LINEA 24 ANILLO VIAL', 'L24LV', 'L24SDF', '', '', 'L24', 1, '2026-10-26 22:16:45'),
(10, 'LINEA 6 A/B.-  CRISTO VIVE/UNION CIVICA - TERMINAL TERRESTRE', 'LINEA 6 A/B.- CRISTO VIVE/UNION CIVICA - TERMINAL TERRESTRE', 'L06ABLV', '', 'L06ABS', 'L06ABD', 'L06AB', 1, '2026-10-26 22:16:45'),
(11, 'LINEA 11 A.-  PROLETARIADO - HOSPITAL', 'LINEA 11 A.- PROLETARIADO - HOSPITAL', 'L11ALV', '', 'L11AS', 'L11AD', 'L11A', 1, '2026-10-26 22:16:45'),
(12, 'LINEA 11 B.-  NUEVA PROVINCIA - HOSPITAL', 'LINEA 11 B.- NUEVA PROVINCIA - HOSPITAL', 'L11BLV', '', 'L11BS', 'L11BD', 'L11B', 1, '2026-10-26 22:16:45'),
(13, 'LINEA 6C GIRASOLES - HOSPITAL', 'LINEA 6C GIRASOLES - HOSPITAL', 'L6CLV', '', 'L06CS', 'L06CS', '06C', 1, '2026-10-26 22:16:45'),
(14, 'LINEA 17.-  UTE - PORTAL - QUEZADA', 'LINEA 17.- UTE - PORTAL - QUEZADA', 'L17ABLV', '', 'L17ABS', 'L17ABD', 'L17AB', 1, '2026-10-26 22:16:45'),
(15, 'LINEA 18.-   VIA QUEVEDO KM 7/8 - TERMINAL TERRESTRE', 'LINEA 18.- VIA QUEVEDO KM 7/8 - TERMINAL TERRESTRE', 'L18LV', '', 'L18S', 'L18D', 'L18', 1, '2026-10-26 22:16:45'),
(16, 'LINEA 18.-  TERMINAL TERRESTRE - VIA QUEVEDO KM 7/8', 'LINEA 18.- TERMINAL TERRESTRE - VIA QUEVEDO KM 7/8', 'L18LV', '', 'L18S', 'L18D', 'L18', 1, '2026-10-26 22:16:45'),
(17, 'LINEA 23A.-  JUAN EULOGIO - TSACHILA', 'LINEA 23A.- JUAN EULOGIO - TSACHILA', 'L23ALV', '', 'L23AS', 'L23AD', 'L23A', 1, '2026-10-26 22:16:45'),
(18, 'LINEA 23B.-  JUAN EULOGIO - 16 DE MARZO', 'LINEA 23B.- JUAN EULOGIO - 16 DE MARZO', 'L23BLV', '', 'L23BS', 'L23BD', 'L23B', 1, '2026-10-26 22:16:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unified_route_groups`
--

CREATE TABLE `unified_route_groups` (
  `id` int UNSIGNED NOT NULL,
  `group_code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `unified_route_groups`
--

INSERT INTO `unified_route_groups` (`id`, `group_code`, `filename`) VALUES
(1, 'LINEA 18', 'LINEA 18.csv');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unified_route_members`
--

CREATE TABLE `unified_route_members` (
  `group_id` int UNSIGNED NOT NULL,
  `route_normalized` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `unified_route_members`
--

INSERT INTO `unified_route_members` (`group_id`, `route_normalized`, `sort_order`) VALUES
(1, 'LINEA 18.- TERMINAL TERRESTRE - VIA QUEVEDO KM 7/8', 2),
(1, 'LINEA 18.- VIA QUEVEDO KM 7/8 - TERMINAL TERRESTRE', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `cedula` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombres` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellidos` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operativo',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `cedula`, `nombres`, `apellidos`, `password_hash`, `role`, `active`, `must_change_password`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, '2350637217', 'JORDAN ENRIQUE', 'ESPINOSA VINUEZA', '$2y$12$IAI3ETFn42eznKnuMVdIxeldKvLfCZJ660qQEOI7WGqw0JDig4ce.', 'administrador', 1, 0, '2026-11-18 10:02:00', '2026-10-26 21:49:17', '2026-11-18 15:02:00'),
(2, '1714309844', 'PRUEBA', 'PRUEBA', '$2y$10$LrgriJyB/8YFpMTUT/cFouX6PL0OtXwmLxujCnHBmTlHUE7UH0fuW', 'monitoreo', 1, 0, '2026-11-18 10:03:03', '2026-10-29 17:33:08', '2026-11-18 15:03:03');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `artifacts`
--
ALTER TABLE `artifacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job` (`job_id`);

--
-- Indices de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`,`created_at`);

--
-- Indices de la tabla `corridor_rules`
--
ALTER TABLE `corridor_rules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_corridor_route` (`corridor`,`route_prefix`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`,`created_at`);

--
-- Indices de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attempts` (`cedula`,`ip`,`created_at`);

--
-- Indices de la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role`,`permission`);

--
-- Indices de la tabla `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `normalized_name` (`normalized_name`);

--
-- Indices de la tabla `unified_route_groups`
--
ALTER TABLE `unified_route_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_code` (`group_code`);

--
-- Indices de la tabla `unified_route_members`
--
ALTER TABLE `unified_route_members`
  ADD PRIMARY KEY (`group_id`,`route_normalized`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `artifacts`
--
ALTER TABLE `artifacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `corridor_rules`
--
ALTER TABLE `corridor_rules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `routes`
--
ALTER TABLE `routes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `unified_route_groups`
--
ALTER TABLE `unified_route_groups`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `artifacts`
--
ALTER TABLE `artifacts`
  ADD CONSTRAINT `artifacts_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`);

--
-- Filtros para la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
