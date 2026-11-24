-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 24-11-2026 a las 16:40:45
-- Versión del servidor: 10.11.16-MariaDB-cll-lve
-- Versión de PHP: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ejecuttr_sistema_tools`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `artifacts`
--

CREATE TABLE `artifacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `job_id` bigint(20) UNSIGNED NOT NULL,
  `file_type` varchar(20) NOT NULL,
  `filename` varchar(200) NOT NULL,
  `relative_path` varchar(250) NOT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `artifacts`
--

INSERT INTO `artifacts` (`id`, `job_id`, `file_type`, `filename`, `relative_path`, `file_size`, `created_at`) VALUES
(1, 1, 'zip', 'PASAJEROS_20261102_180133_EXCEL_Y_PDF.zip', 'exports/b9f099f046706799b717123f79bc92f312dd952d.zip', 265150, '2026-11-02 23:01:34'),
(2, 1, 'xlsx', 'PASAJEROS_20261102_180133.xlsx', 'exports/e1140a2fac3498e5fbf2f68c47bdd639f7c0fc1b.xlsx', 122204, '2026-11-02 23:01:34'),
(3, 1, 'pdf', 'PASAJEROS_20261102_180133.pdf', 'exports/df798a84e15e81ca28f410ec981b650d5176777f.pdf', 154671, '2026-11-02 23:01:34'),
(4, 2, 'zip', 'PASAJEROS_20261102_180942_EXCEL_Y_PDF.zip', 'exports/0a85769188cb2d4198cd34583166ca519e8ef2d7.zip', 534152, '2026-11-02 23:09:47'),
(5, 2, 'xlsx', 'PASAJEROS_20261102_180942.xlsx', 'exports/e5c57d87437729df6d5ecaca04593110d1becf87.xlsx', 447393, '2026-11-02 23:09:47'),
(6, 2, 'pdf', 'PASAJEROS_20261102_180942.pdf', 'exports/91badf5c95b7608af9667ab1c2a6903babd9ed11.pdf', 157781, '2026-11-02 23:09:47'),
(7, 3, 'zip', 'ROTATIVOS_2026-11-02_AL_2026-11-08_SANTA_MARTHA.zip', 'exports/0b70fee3d45ad00ee560ad52279b7317853ea749.zip', 17474, '2026-11-04 16:38:30'),
(8, 4, 'zip', 'PASAJEROS_20261104_121317_EXCEL_Y_PDF.zip', 'exports/5d9dd04d554c353d4a18c3430610620ddf666baa.zip', 298903, '2026-11-04 17:13:19'),
(9, 4, 'xlsx', 'PASAJEROS_20261104_121317.xlsx', 'exports/afdacbf54591787110cf9231e5eec46563e95699.xlsx', 155072, '2026-11-04 17:13:19'),
(10, 4, 'pdf', 'PASAJEROS_20261104_121317.pdf', 'exports/7776a9bc2738ceb702683933e852067809812cdb.pdf', 156278, '2026-11-04 17:13:19'),
(11, 5, 'zip', 'PASAJEROS_20261104_121452_EXCEL_Y_PDF.zip', 'exports/bb26696d91c7e1c9e3e48ab4a1c1054a778684c4.zip', 534157, '2026-11-04 17:14:58'),
(12, 5, 'xlsx', 'PASAJEROS_20261104_121452.xlsx', 'exports/413ac7ca50fde673c8e9de53491f12205763dce3.xlsx', 447392, '2026-11-04 17:14:58'),
(13, 5, 'pdf', 'PASAJEROS_20261104_121452.pdf', 'exports/e3dcb6ef7c47d713d9c8a828d4ba4c59bb338e76.pdf', 157781, '2026-11-04 17:14:58'),
(14, 6, 'zip', 'ROTATIVOS_2026-11-02_AL_2026-11-08_SANTA_MARTHA.zip', 'exports/24074b4f626358ffd22c485c2d5fcf0ec4201b19.zip', 17474, '2026-11-06 13:24:59'),
(15, 7, 'zip', 'ROTATIVOS_2026-11-09_AL_2026-11-15_QUEVEDO.zip', 'exports/950cf1d9d9dda0cecaa88a638571b64575715551.zip', 11208, '2026-11-09 15:34:54'),
(16, 8, 'zip', 'ROTATIVOS_2026-11-18_QUEVEDO.zip', 'exports/7930e454b65782c092d82d07a0d4495248e780f2.zip', 1934, '2026-11-18 15:37:57'),
(17, 9, 'zip', 'ROTATIVOS_2026-11-16_SANTA_MARTHA.zip', 'exports/fa8e574c02ee8e2e1059c93ee37c857f063cc70e.zip', 2696, '2026-11-19 21:45:44'),
(18, 10, 'zip', 'ROTATIVOS_2026-11-16_SANTA_MARTHA.zip', 'exports/b537fcee15ba01e5290860855109ee068e232f25.zip', 2696, '2026-11-19 21:46:06'),
(19, 11, 'zip', 'ROTATIVOS_2026-11-16_SANTA_MARTHA.zip', 'exports/8147b06121cebf212e8d2c2b3458167d11b4f7e7.zip', 1933, '2026-11-19 21:48:20'),
(20, 12, 'zip', 'ROTATIVOS_2026-11-16_AL_2026-11-22_SANTA_MARTHA.zip', 'exports/7f48bb518b3ff6c4609c63db7f6a3084bf4366dc.zip', 14701, '2026-11-20 17:43:19'),
(21, 13, 'xlsx', 'CONSOLIDADO_PAGOS_BANCARIOS_2026-11-20_130258.xlsx', 'exports/89fb7a0eb3b38d11b1e049717c043fe34565dae7.xlsx', 51402, '2026-11-20 18:02:58'),
(22, 14, 'zip', 'ROTATIVOS_2026-11-16_AL_2026-11-22_SANTA_MARTHA.zip', 'exports/fdcb694a726b73ff53b5c5f31091e1f970f8ee60.zip', 14701, '2026-11-21 02:59:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detail`)),
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `detail`, `ip`, `created_at`) VALUES
(1, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '190.10.170.23', '2026-11-02 22:34:41'),
(2, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '190.10.170.23', '2026-11-02 22:55:46'),
(3, 1, 'users.created', '{\"cedula\":\"2351048703\",\"role\":\"operativo\",\"initial_password\":\"cedula\"}', '190.10.170.23', '2026-11-02 22:57:38'),
(4, 1, 'users.updated', '{\"user_id\":2,\"role\":\"monitoreo\",\"active\":1}', '190.10.170.23', '2026-11-02 22:58:01'),
(5, 1, 'users.created', '{\"cedula\":\"1718739384\",\"role\":\"secretaria\",\"initial_password\":\"cedula\"}', '190.10.170.23', '2026-11-02 22:58:40'),
(6, 1, 'permissions.updated', '[]', '190.10.170.23', '2026-11-02 22:59:04'),
(7, 1, 'users.created', '{\"cedula\":\"1718798471\",\"role\":\"contabilidad\",\"initial_password\":\"cedula\"}', '190.10.170.23', '2026-11-02 22:59:43'),
(8, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '190.10.170.23', '2026-11-02 23:00:17'),
(9, 2, 'passengers.generated', '{\"records\":158,\"income_cents\":5188,\"formats\":[\"zip\",\"xlsx\",\"pdf\"]}', '190.10.170.23', '2026-11-02 23:01:34'),
(10, 2, 'artifact.download', '{\"id\":3}', '190.10.170.23', '2026-11-02 23:01:37'),
(11, 2, 'auth.logout', '[]', '190.10.170.23', '2026-11-02 23:05:11'),
(12, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '190.10.170.23', '2026-11-02 23:05:35'),
(13, 1, 'auth.logout', '[]', '190.10.170.23', '2026-11-02 23:05:44'),
(14, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '190.10.170.23', '2026-11-02 23:06:42'),
(15, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '190.10.170.23', '2026-11-02 23:08:02'),
(16, 2, 'passengers.generated', '{\"records\":9117,\"income_cents\":566546,\"formats\":[\"zip\",\"xlsx\",\"pdf\"]}', '190.10.170.23', '2026-11-02 23:09:47'),
(17, 2, 'artifact.download', '{\"id\":6}', '190.10.170.23', '2026-11-02 23:10:04'),
(18, 2, 'artifact.download', '{\"id\":5}', '190.10.170.23', '2026-11-02 23:14:35'),
(19, 4, 'auth.login', '{\"cedula\":\"1718798471\"}', '181.199.59.137', '2026-11-02 23:51:02'),
(20, 4, 'auth.login', '{\"cedula\":\"1718798471\"}', '181.199.59.137', '2026-11-04 01:13:07'),
(21, 3, 'auth.login', '{\"cedula\":\"1718739384\"}', '190.95.132.189', '2026-11-04 02:12:31'),
(22, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '186.4.146.201', '2026-11-04 15:09:27'),
(23, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-04 15:56:27'),
(24, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-04 16:38:19'),
(25, 1, 'rotatives.generated', '{\"date\":\"2026-11-02_AL_2026-11-08\",\"day\":\"TODOS\",\"count\":57}', '186.4.146.201', '2026-11-04 16:38:30'),
(26, 1, 'artifact.download', '{\"id\":7}', '186.4.146.201', '2026-11-04 16:38:42'),
(27, 3, 'auth.login', '{\"cedula\":\"1718739384\"}', '186.4.146.201', '2026-11-04 17:11:02'),
(28, 3, 'passengers.generated', '{\"records\":1054,\"income_cents\":75203,\"formats\":[\"zip\",\"xlsx\",\"pdf\"]}', '186.4.146.201', '2026-11-04 17:13:19'),
(29, 3, 'artifact.download', '{\"id\":9}', '186.4.146.201', '2026-11-04 17:13:29'),
(30, 3, 'passengers.generated', '{\"records\":9117,\"income_cents\":566546,\"formats\":[\"zip\",\"xlsx\",\"pdf\"]}', '186.4.146.201', '2026-11-04 17:14:58'),
(31, 3, 'artifact.download', '{\"id\":13}', '186.4.146.201', '2026-11-04 17:15:08'),
(32, 3, 'artifact.download', '{\"id\":12}', '186.4.146.201', '2026-11-04 17:15:50'),
(33, 3, 'auth.logout', '[]', '186.4.146.201', '2026-11-04 17:33:29'),
(34, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '190.63.116.137', '2026-11-04 22:28:21'),
(35, 1, 'auth.logout', '[]', '190.63.116.137', '2026-11-04 22:28:44'),
(36, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '190.63.116.137', '2026-11-04 22:28:59'),
(37, 2, 'artifact.download', '{\"id\":3}', '190.63.116.137', '2026-11-04 22:29:57'),
(38, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-05 14:14:18'),
(39, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-05 17:12:39'),
(40, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '186.4.146.201', '2026-11-05 17:13:07'),
(41, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '190.10.170.23', '2026-11-06 13:24:01'),
(42, 1, 'rotatives.generated', '{\"date\":\"2026-11-02_AL_2026-11-08\",\"day\":\"TODOS\",\"count\":57}', '190.10.170.23', '2026-11-06 13:24:59'),
(43, 1, 'artifact.download', '{\"id\":14}', '190.10.170.23', '2026-11-06 13:25:11'),
(44, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-09 15:33:34'),
(45, 1, 'rotatives.generated', '{\"date\":\"2026-11-09_AL_2026-11-15\",\"day\":\"TODOS\",\"count\":35}', '186.4.146.201', '2026-11-09 15:34:54'),
(46, 1, 'artifact.download', '{\"id\":15}', '186.4.146.201', '2026-11-09 15:35:07'),
(47, 3, 'auth.login', '{\"cedula\":\"1718739384\"}', '186.4.146.201', '2026-11-10 16:58:18'),
(48, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-10 22:00:30'),
(49, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-12 13:19:18'),
(50, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-17 14:02:40'),
(51, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-17 15:00:37'),
(52, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '186.4.146.201', '2026-11-17 15:01:44'),
(53, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-18 15:37:27'),
(54, 1, 'rotatives.generated', '{\"date\":\"2026-11-18\",\"day\":\"MIERCOLES\",\"count\":7}', '186.4.146.201', '2026-11-18 15:37:57'),
(55, 1, 'artifact.download', '{\"id\":16}', '186.4.146.201', '2026-11-18 15:37:59'),
(56, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-19 16:52:46'),
(57, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '186.4.146.201', '2026-11-19 16:53:17'),
(58, 1, 'permissions.updated', '[]', '186.4.146.201', '2026-11-19 16:53:47'),
(59, 1, 'permissions.updated', '[]', '186.4.146.201', '2026-11-19 16:54:58'),
(60, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-19 21:16:56'),
(61, 1, 'auth.logout', '[]', '186.4.146.201', '2026-11-19 21:30:24'),
(62, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-19 21:30:33'),
(63, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-19 21:31:13'),
(64, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-19 21:39:31'),
(65, 1, 'auth.logout', '[]', '186.4.146.201', '2026-11-19 21:43:18'),
(66, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-19 21:43:39'),
(67, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '186.4.146.201', '2026-11-19 21:43:57'),
(68, 2, 'rotatives.generated', '{\"date\":\"2026-11-16\",\"day\":\"LUNES\",\"count\":8}', '186.4.146.201', '2026-11-19 21:45:44'),
(69, 2, 'rotatives.generated', '{\"date\":\"2026-11-16\",\"day\":\"LUNES\",\"count\":8}', '186.4.146.201', '2026-11-19 21:46:06'),
(70, 2, 'artifact.download', '{\"id\":18}', '186.4.146.201', '2026-11-19 21:46:07'),
(71, 2, 'rotatives.generated', '{\"date\":\"2026-11-16\",\"day\":\"LUNES\",\"count\":7}', '186.4.146.201', '2026-11-19 21:48:20'),
(72, 2, 'artifact.download', '{\"id\":19}', '186.4.146.201', '2026-11-19 21:48:26'),
(73, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-20 17:06:11'),
(74, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '186.4.146.201', '2026-11-20 17:06:21'),
(75, 1, 'permissions.updated', '[]', '186.4.146.201', '2026-11-20 17:07:30'),
(76, 1, 'minutes.line_saved', '{\"id\":1,\"configuration\":{\"id\":1,\"code\":\"6\",\"name\":\"LINEA 06\",\"base_minutes\":8,\"base_rate_cents\":50,\"direct_rate_cents\":null,\"enabled\":1,\"updated_at\":\"2026-11-20 12:12:15\",\"aliases\":[{\"id\":1,\"line_id\":1,\"name\":\"LINEA 06\",\"normalized_name\":\"LINEA 06\"},{\"id\":2,\"line_id\":1,\"name\":\"06A RETORNO\",\"normalized_name\":\"06A RETORNO\"},{\"id\":3,\"line_id\":1,\"name\":\"06A SUBIDA\",\"normalized_name\":\"06A SUBIDA\"},{\"id\":4,\"line_id\":1,\"name\":\"06B RETORNO\",\"normalized_name\":\"06B RETORNO\"},{\"id\":5,\"line_id\":1,\"name\":\"06B SUBIDA\",\"normalized_name\":\"06B SUBIDA\"},{\"id\":6,\"line_id\":1,\"name\":\"CRISTO VIVE-TERMINAL TERRESTRE N\",\"normalized_name\":\"CRISTO VIVE TERMINAL TERRESTRE N\"},{\"id\":7,\"line_id\":1,\"name\":\"UNION CIVICA-TERMINAL\",\"normalized_name\":\"UNION CIVICA TERMINAL\"}],\"points\":[]}}', '186.4.146.201', '2026-11-20 17:12:15'),
(77, 1, 'minutes.point_saved', '{\"id\":1,\"line_id\":1,\"values\":{\"id\":\"0\",\"line_id\":\"1\",\"name\":\"G.AKI\",\"point_number\":\"0\",\"sanction\":\"inherit\",\"rate\":\"2.00\",\"tolerance_minutes\":\"0\",\"enabled\":\"1\"}}', '186.4.146.201', '2026-11-20 17:13:20'),
(78, 1, 'rotatives.generated', '{\"date\":\"2026-11-16_AL_2026-11-22\",\"day\":\"TODOS\",\"count\":49}', '186.4.146.201', '2026-11-20 17:43:19'),
(79, 1, 'artifact.download', '{\"id\":20}', '186.4.146.201', '2026-11-20 17:43:22'),
(80, 1, 'permissions.updated', '[]', '186.4.146.201', '2026-11-20 18:02:00'),
(81, 1, 'permissions.updated', '[]', '186.4.146.201', '2026-11-20 18:02:00'),
(82, 2, 'auth.login', '{\"cedula\":\"2351048703\"}', '186.4.146.201', '2026-11-20 18:02:19'),
(83, 2, 'bankpayments.generated', '{\"pagos_leidos\":827,\"comprobantes\":369,\"encontrados\":216,\"no_encontrados\":153,\"revisar\":3,\"posibles_tipeo\":0,\"movimientos\":530,\"estados_cuenta\":1,\"warnings\":[]}', '186.4.146.201', '2026-11-20 18:02:58'),
(84, 2, 'artifact.download', '{\"id\":21}', '186.4.146.201', '2026-11-20 18:03:07'),
(85, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '190.10.170.23', '2026-11-21 02:59:17'),
(86, 1, 'rotatives.generated', '{\"date\":\"2026-11-16_AL_2026-11-22\",\"day\":\"TODOS\",\"count\":49}', '190.10.170.23', '2026-11-21 02:59:50'),
(87, 1, 'artifact.download', '{\"id\":22}', '190.10.170.23', '2026-11-21 03:01:29'),
(88, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-23 13:34:53'),
(89, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '190.63.114.203', '2026-11-23 19:12:50'),
(90, 1, 'auth.login', '{\"cedula\":\"2350637217\"}', '186.4.146.201', '2026-11-24 17:29:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `corridor_rules`
--

CREATE TABLE `corridor_rules` (
  `id` int(10) UNSIGNED NOT NULL,
  `corridor` varchar(60) NOT NULL,
  `route_prefix` varchar(80) NOT NULL,
  `tab_start` int(11) NOT NULL,
  `tab_end` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `corridor_rules`
--

INSERT INTO `corridor_rules` (`id`, `corridor`, `route_prefix`, `tab_start`, `tab_end`) VALUES
(1, 'Quevedo', 'LINEA 23A', 4, 6),
(2, 'Santa Martha', 'LINEA 23B', 6, 10),
(4, 'Santa Martha', 'LINEA 19', 4, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(40) NOT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`parameters`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `jobs`
--

INSERT INTO `jobs` (`id`, `user_id`, `module`, `parameters`, `created_at`) VALUES
(1, 2, 'passengers', '{\"type\":\"mes\",\"year\":2026,\"month\":1,\"day\":26,\"flota_completa\":true,\"hoja\":\"1792693898184_demanda_pasajeros\"}', '2026-11-02 23:01:34'),
(2, 2, 'passengers', '{\"type\":\"anio\",\"year\":2026,\"month\":10,\"day\":26,\"flota_completa\":true,\"hoja\":\"1792693898184_demanda_pasajeros\"}', '2026-11-02 23:09:47'),
(3, 1, 'rotatives', '{\"day\":\"TODOS\",\"corridor\":\"Santa Martha\",\"start_date\":\"2026-11-02\"}', '2026-11-04 16:38:30'),
(4, 3, 'passengers', '{\"type\":\"mes\",\"year\":2026,\"month\":10,\"day\":26,\"flota_completa\":true,\"hoja\":\"1792693898184_demanda_pasajeros\"}', '2026-11-04 17:13:19'),
(5, 3, 'passengers', '{\"type\":\"anio\",\"year\":2026,\"month\":10,\"day\":26,\"flota_completa\":true,\"hoja\":\"1792693898184_demanda_pasajeros\"}', '2026-11-04 17:14:58'),
(6, 1, 'rotatives', '{\"day\":\"TODOS\",\"corridor\":\"Santa Martha\",\"start_date\":\"2026-11-02\"}', '2026-11-06 13:24:59'),
(7, 1, 'rotatives', '{\"day\":\"TODOS\",\"corridor\":\"Quevedo\",\"start_date\":\"2026-11-09\"}', '2026-11-09 15:34:54'),
(8, 1, 'rotatives', '{\"day\":\"Miércoles\",\"corridor\":\"Quevedo\",\"start_date\":\"2026-11-16\"}', '2026-11-18 15:37:57'),
(9, 2, 'rotatives', '{\"day\":\"Lunes\",\"corridor\":\"Santa Martha\",\"start_date\":\"2026-11-16\"}', '2026-11-19 21:45:44'),
(10, 2, 'rotatives', '{\"day\":\"Lunes\",\"corridor\":\"Santa Martha\",\"start_date\":\"2026-11-16\"}', '2026-11-19 21:46:06'),
(11, 2, 'rotatives', '{\"day\":\"Lunes\",\"corridor\":\"Santa Martha\",\"start_date\":\"2026-11-16\"}', '2026-11-19 21:48:20'),
(12, 1, 'rotatives', '{\"day\":\"TODOS\",\"corridor\":\"Santa Martha\",\"start_date\":\"2026-11-16\"}', '2026-11-20 17:43:19'),
(13, 2, 'bankpayments', '{\"pagos_leidos\":827,\"comprobantes\":369,\"encontrados\":216,\"no_encontrados\":153,\"revisar\":3,\"posibles_tipeo\":0,\"movimientos\":530,\"estados_cuenta\":1,\"warnings\":[],\"payments\":\"pagos-2026-11-20.xlsx\",\"statements\":[\"MOV PICHINCHA OCTUBRE 2026.xlsx\"]}', '2026-11-20 18:02:58'),
(14, 1, 'rotatives', '{\"day\":\"TODOS\",\"corridor\":\"Santa Martha\",\"start_date\":\"2026-11-16\"}', '2026-11-21 02:59:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cedula` char(10) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `cedula`, `ip`, `created_at`) VALUES
(5, '1714309844', '186.4.146.201', '2026-11-19 21:43:27'),
(1, '1719422402', '123.200.236.212', '2026-11-02 23:26:44'),
(4, '2350637217', '190.63.116.137', '2026-11-04 22:28:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `minutes_lines`
--

CREATE TABLE `minutes_lines` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `base_minutes` int(10) UNSIGNED NOT NULL DEFAULT 8,
  `base_rate_cents` int(10) UNSIGNED NOT NULL DEFAULT 50,
  `direct_rate_cents` int(10) UNSIGNED DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `minutes_lines`
--

INSERT INTO `minutes_lines` (`id`, `code`, `name`, `base_minutes`, `base_rate_cents`, `direct_rate_cents`, `enabled`, `updated_at`) VALUES
(1, '6', 'LINEA 06', 8, 50, NULL, 1, '2026-11-20 17:12:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `minutes_points`
--

CREATE TABLE `minutes_points` (
  `id` int(10) UNSIGNED NOT NULL,
  `line_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `normalized_name` varchar(255) NOT NULL,
  `point_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sanction` varchar(8) NOT NULL DEFAULT 'inherit',
  `rate_cents` int(10) UNSIGNED DEFAULT NULL,
  `tolerance_minutes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `minutes_points`
--

INSERT INTO `minutes_points` (`id`, `line_id`, `name`, `normalized_name`, `point_number`, `sanction`, `rate_cents`, `tolerance_minutes`, `enabled`, `updated_at`) VALUES
(1, 1, 'G.AKI', 'G AKI', 0, 'inherit', 200, 0, 1, '2026-11-20 17:13:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `minutes_route_aliases`
--

CREATE TABLE `minutes_route_aliases` (
  `id` int(10) UNSIGNED NOT NULL,
  `line_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `normalized_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `minutes_route_aliases`
--

INSERT INTO `minutes_route_aliases` (`id`, `line_id`, `name`, `normalized_name`) VALUES
(1, 1, 'LINEA 06', 'LINEA 06'),
(2, 1, '06A RETORNO', '06A RETORNO'),
(3, 1, '06A SUBIDA', '06A SUBIDA'),
(4, 1, '06B RETORNO', '06B RETORNO'),
(5, 1, '06B SUBIDA', '06B SUBIDA'),
(6, 1, 'CRISTO VIVE-TERMINAL TERRESTRE N', 'CRISTO VIVE TERMINAL TERRESTRE N'),
(7, 1, 'UNION CIVICA-TERMINAL', 'UNION CIVICA TERMINAL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role` varchar(30) NOT NULL,
  `permission` varchar(90) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `role_permissions`
--

INSERT INTO `role_permissions` (`role`, `permission`) VALUES
('contabilidad', 'bankpayments.download'),
('contabilidad', 'bankpayments.generate'),
('contabilidad', 'bankpayments.view'),
('contabilidad', 'fleets.download'),
('contabilidad', 'fleets.generate'),
('contabilidad', 'fleets.view'),
('contabilidad', 'minutes.download'),
('contabilidad', 'minutes.generate'),
('contabilidad', 'minutes.view'),
('contabilidad', 'passengers.download'),
('contabilidad', 'passengers.generate'),
('contabilidad', 'passengers.view'),
('contabilidad', 'rotatives.download'),
('contabilidad', 'rotatives.generate'),
('contabilidad', 'rotatives.view'),
('monitoreo', 'bankpayments.download'),
('monitoreo', 'bankpayments.generate'),
('monitoreo', 'bankpayments.view'),
('monitoreo', 'fleets.download'),
('monitoreo', 'fleets.generate'),
('monitoreo', 'fleets.view'),
('monitoreo', 'minutes.download'),
('monitoreo', 'minutes.generate'),
('monitoreo', 'minutes.view'),
('monitoreo', 'passengers.download'),
('monitoreo', 'passengers.generate'),
('monitoreo', 'passengers.view'),
('monitoreo', 'rotatives.download'),
('monitoreo', 'rotatives.generate'),
('monitoreo', 'rotatives.view'),
('monitoreo', 'routes.view'),
('operativo', 'bankpayments.download'),
('operativo', 'bankpayments.generate'),
('operativo', 'bankpayments.view'),
('operativo', 'fleets.download'),
('operativo', 'fleets.generate'),
('operativo', 'fleets.view'),
('operativo', 'minutes.download'),
('operativo', 'minutes.generate'),
('operativo', 'minutes.view'),
('operativo', 'passengers.download'),
('operativo', 'passengers.generate'),
('operativo', 'passengers.view'),
('operativo', 'rotatives.download'),
('operativo', 'rotatives.generate'),
('operativo', 'rotatives.view'),
('secretaria', 'bankpayments.download'),
('secretaria', 'bankpayments.generate'),
('secretaria', 'bankpayments.view'),
('secretaria', 'fleets.download'),
('secretaria', 'fleets.generate'),
('secretaria', 'fleets.view'),
('secretaria', 'minutes.download'),
('secretaria', 'minutes.generate'),
('secretaria', 'minutes.view'),
('secretaria', 'passengers.download'),
('secretaria', 'passengers.generate'),
('secretaria', 'passengers.view'),
('secretaria', 'rotatives.download'),
('secretaria', 'rotatives.generate'),
('secretaria', 'rotatives.view');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `routes`
--

CREATE TABLE `routes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `normalized_name` varchar(255) NOT NULL,
  `code_weekdays` varchar(50) NOT NULL DEFAULT '',
  `code_weekend` varchar(50) NOT NULL DEFAULT '',
  `code_saturday` varchar(50) NOT NULL DEFAULT '',
  `code_sunday` varchar(50) NOT NULL DEFAULT '',
  `cod_ruta` varchar(50) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `routes`
--

INSERT INTO `routes` (`id`, `name`, `normalized_name`, `code_weekdays`, `code_weekend`, `code_saturday`, `code_sunday`, `cod_ruta`, `enabled`, `updated_at`) VALUES
(1, 'LINEA 7 CADENA-HOSPITAL SD', 'LINEA 7 CADENA-HOSPITAL SD', 'L07LV', 'L07SDF', '', '', 'L007', 1, '2026-11-02 22:27:55'),
(2, 'LINEA 10A BELLAVISTA -KASAMA', 'LINEA 10A BELLAVISTA -KASAMA', 'L10ALV', '', '', 'L10AD', 'L10A', 1, '2026-11-02 22:27:55'),
(3, 'LINEA 10B ARROYOS-KASAMA', 'LINEA 10B ARROYOS-KASAMA', 'L10BLV', '', 'L10BS', '', 'L10B', 1, '2026-11-02 22:27:55'),
(4, 'LINEA 16 CHIGUILPE, MAYA- TERMINAL T.', 'LINEA 16 CHIGUILPE, MAYA- TERMINAL T.', 'L16LV', '', 'L16S', 'L16D', 'L16', 1, '2026-11-02 22:27:55'),
(5, 'LINEA 17 UTE ,QUEZADA -PORTAL', 'LINEA 17 UTE ,QUEZADA -PORTAL', 'L17ABLV', '', 'L17ABS', 'L17ABD', 'L17AB', 1, '2026-11-02 22:27:55'),
(6, 'LINEA 20 CIUDAD VERDE -TERMINAL', 'LINEA 20 CIUDAD VERDE -TERMINAL', 'L20LV', 'L20SDF', '', '', 'L20', 1, '2026-11-02 22:27:55'),
(7, 'LINEA 23A JUAN EULOGIO -TSACHILA', 'LINEA 23A JUAN EULOGIO -TSACHILA', 'L23ALV', '', 'L23AS', 'L23AD', 'L23A', 1, '2026-11-02 22:27:55'),
(8, 'LINEA 23B JUAN EULOGIO-16 DE MARZO', 'LINEA 23B JUAN EULOGIO-16 DE MARZO', 'L23BLV', '', 'L23BS', 'L23BD', 'L23B', 1, '2026-11-02 22:27:55'),
(9, 'LINEA 24 ANILLO VIAL', 'LINEA 24 ANILLO VIAL', 'L24LV', 'L24SDF', '', '', 'L24', 1, '2026-11-02 22:27:55'),
(10, 'LINEA 6 A/B.-  CRISTO VIVE/UNION CIVICA - TERMINAL TERRESTRE', 'LINEA 6 A/B.- CRISTO VIVE/UNION CIVICA - TERMINAL TERRESTRE', 'L06ABLV', '', 'L06ABS', 'L06ABD', 'L06AB', 1, '2026-11-02 22:27:55'),
(11, 'LINEA 11 A.-  PROLETARIADO - HOSPITAL', 'LINEA 11 A.- PROLETARIADO - HOSPITAL', 'L11ALV', '', 'L11AS', 'L11AD', 'L11A', 1, '2026-11-02 22:27:55'),
(12, 'LINEA 11 B.-  NUEVA PROVINCIA - HOSPITAL', 'LINEA 11 B.- NUEVA PROVINCIA - HOSPITAL', 'L11BLV', '', 'L11BS', 'L11BD', 'L11B', 1, '2026-11-02 22:27:55'),
(13, 'LINEA 6C GIRASOLES - HOSPITAL', 'LINEA 6C GIRASOLES - HOSPITAL', 'L6CLV', '', 'L06CS', 'L06CS', '06C', 1, '2026-11-02 22:27:55'),
(14, 'LINEA 17.-  UTE - PORTAL - QUEZADA', 'LINEA 17.- UTE - PORTAL - QUEZADA', 'L17ABLV', '', 'L17ABS', 'L17ABD', 'L17AB', 1, '2026-11-02 22:27:55'),
(15, 'LINEA 18.-   VIA QUEVEDO KM 7/8 - TERMINAL TERRESTRE', 'LINEA 18.- VIA QUEVEDO KM 7/8 - TERMINAL TERRESTRE', 'L18LV', '', 'L18S', 'L18D', 'L18', 1, '2026-11-02 22:27:55'),
(16, 'LINEA 18.-  TERMINAL TERRESTRE - VIA QUEVEDO KM 7/8', 'LINEA 18.- TERMINAL TERRESTRE - VIA QUEVEDO KM 7/8', 'L18LV', '', 'L18S', 'L18D', 'L18', 1, '2026-11-02 22:27:55'),
(17, 'LINEA 23A.-  JUAN EULOGIO - TSACHILA', 'LINEA 23A.- JUAN EULOGIO - TSACHILA', 'L23ALV', '', 'L23AS', 'L23AD', 'L23A', 1, '2026-11-02 22:27:55'),
(18, 'LINEA 23B.-  JUAN EULOGIO - 16 DE MARZO', 'LINEA 23B.- JUAN EULOGIO - 16 DE MARZO', 'L23BLV', '', 'L23BS', 'L23BD', 'L23B', 1, '2026-11-02 22:27:55'),
(19, 'LINEA 18 VIA QUEVEDO KM8 - TERMINAL T.', 'LINEA 18 VIA QUEVEDO KM8 - TERMINAL T.', 'L18LV', '', 'L18S', 'L18D', 'L18', 1, '2026-11-18 15:36:05'),
(20, 'LINEA 18 TERMINAL T. - VIA QUEVEDO KM8', 'LINEA 18 TERMINAL T. - VIA QUEVEDO KM8', 'L18LV', '', 'L18S', 'L18D', 'L18', 1, '2026-11-18 15:36:05'),
(21, 'LINEA 19 BY PASS', 'LINEA 19 BY PASS', 'L19LV', '', 'L19S', 'L19D', 'L19', 1, '2026-11-18 15:36:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `unified_route_groups`
--

CREATE TABLE `unified_route_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `group_code` varchar(40) NOT NULL,
  `filename` varchar(100) NOT NULL
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
  `group_id` int(10) UNSIGNED NOT NULL,
  `route_normalized` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `unified_route_members`
--

INSERT INTO `unified_route_members` (`group_id`, `route_normalized`, `sort_order`) VALUES
(1, 'LINEA 18 TERMINAL T. - VIA QUEVEDO KM8', 2),
(1, 'LINEA 18 VIA QUEVEDO KM8 - TERMINAL T.', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cedula` char(10) NOT NULL,
  `nombres` varchar(120) NOT NULL,
  `apellidos` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'operativo',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `cedula`, `nombres`, `apellidos`, `password_hash`, `role`, `active`, `must_change_password`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, '2350637217', 'JORDAN ENRIQUE', 'ESPINOSA VINUEZA', '$2y$12$IAI3ETFn42eznKnuMVdIxeldKvLfCZJ660qQEOI7WGqw0JDig4ce.', 'administrador', 1, 0, '2026-11-24 12:29:25', '2026-11-02 22:27:19', '2026-11-24 17:29:25'),
(2, '2351048703', 'ISIDRO FERNANDO', 'TOAPAXI BURGOS', '$2y$10$lLNMcCIxsqTnjl4r9iY7eeL8orCej/RI43K4uACvdnZcF5dK5WvKm', 'monitoreo', 1, 0, '2026-11-20 13:02:19', '2026-11-02 22:57:38', '2026-11-20 18:02:19'),
(3, '1718739384', 'MARÍA VIVIANA', 'ZAMBRANO GRANDA', '$2y$10$K3btV7dYWltgYGkXN0MWf.dwXAG.worsEFifuXJ/j.DIyU7tLhqFO', 'secretaria', 1, 0, '2026-11-10 11:58:18', '2026-11-02 22:58:40', '2026-11-10 16:58:18'),
(4, '1718798471', 'RICARDO', 'ULLAURI', '$2y$10$NmNATYWfOOYgZeaGTyEaweah/R9tcKNidfYCDXRl9KUD5JqjQrLuS', 'contabilidad', 1, 0, '2026-11-03 20:13:07', '2026-11-02 22:59:43', '2026-11-04 01:13:07');

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
-- Indices de la tabla `minutes_lines`
--
ALTER TABLE `minutes_lines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `minutes_points`
--
ALTER TABLE `minutes_points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_minutes_point` (`line_id`,`normalized_name`,`point_number`);

--
-- Indices de la tabla `minutes_route_aliases`
--
ALTER TABLE `minutes_route_aliases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `normalized_name` (`normalized_name`),
  ADD KEY `line_id` (`line_id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT de la tabla `corridor_rules`
--
ALTER TABLE `corridor_rules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `minutes_lines`
--
ALTER TABLE `minutes_lines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `minutes_points`
--
ALTER TABLE `minutes_points`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `minutes_route_aliases`
--
ALTER TABLE `minutes_route_aliases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `routes`
--
ALTER TABLE `routes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `unified_route_groups`
--
ALTER TABLE `unified_route_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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

--
-- Filtros para la tabla `minutes_points`
--
ALTER TABLE `minutes_points`
  ADD CONSTRAINT `minutes_points_ibfk_1` FOREIGN KEY (`line_id`) REFERENCES `minutes_lines` (`id`);

--
-- Filtros para la tabla `minutes_route_aliases`
--
ALTER TABLE `minutes_route_aliases`
  ADD CONSTRAINT `minutes_route_aliases_ibfk_1` FOREIGN KEY (`line_id`) REFERENCES `minutes_lines` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
