/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:guid)',
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '(DC2Type:guid)',
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activity_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_lan_mac` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_wifi_mac` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `publish_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_user_id_foreign` (`user_id`),
  CONSTRAINT `announcements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `kantor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `jam_masuk2` time DEFAULT NULL,
  `jam_keluar2` time DEFAULT NULL,
  `jam_masuk3` time DEFAULT NULL,
  `jam_keluar3` time DEFAULT NULL,
  `jam_masuk4` time DEFAULT NULL,
  `jam_keluar4` time DEFAULT NULL,
  `jam_masuk5` time DEFAULT NULL,
  `jam_keluar5` time DEFAULT NULL,
  `jam_masuk6` time DEFAULT NULL,
  `jam_keluar6` time DEFAULT NULL,
  `jam_masuk7` time DEFAULT NULL,
  `jam_keluar7` time DEFAULT NULL,
  `jam_masuk8` time DEFAULT NULL,
  `jam_keluar8` time DEFAULT NULL,
  `jam_masuk9` time DEFAULT NULL,
  `jam_keluar9` time DEFAULT NULL,
  `jam_masuk10` time DEFAULT NULL,
  `jam_keluar10` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_employee_id_foreign` (`employee_id`),
  CONSTRAINT `attendance_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `attendance_date` date DEFAULT NULL,
  `status` enum('Late','Ontime','Absent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID perangkat Fingerspot',
  `is_public_holiday` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_tables_attendance_date_index` (`attendance_date`),
  KEY `attendance_tables_user_id_attendance_date_index` (`user_id`,`attendance_date`),
  CONSTRAINT `attendance_tables_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendancetotal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendancetotal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` bigint unsigned NOT NULL,
  `month` date DEFAULT NULL,
  `total` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendancetotal_attendance_id_index` (`attendance_id`),
  CONSTRAINT `attendancetotal_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `banks_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banks_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banks_tables_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `brands_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_tables_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_tables_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories_tables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `company_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `npwp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `departments_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nickname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `departments_tables_manager_id_foreign` (`manager_id`),
  CONSTRAINT `departments_tables_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `edited_fingerprints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `edited_fingerprints` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scan_date` date NOT NULL,
  `in_1` time DEFAULT NULL,
  `device_1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_2` time DEFAULT NULL,
  `device_2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_3` time DEFAULT NULL,
  `device_3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_4` time DEFAULT NULL,
  `device_4` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_5` time DEFAULT NULL,
  `device_5` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_6` time DEFAULT NULL,
  `device_6` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_7` time DEFAULT NULL,
  `device_7` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_8` time DEFAULT NULL,
  `device_8` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_9` time DEFAULT NULL,
  `device_9` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_10` time DEFAULT NULL,
  `device_10` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_shifts_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_shifts_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shift_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('Scheduled','Cancelled','Swapped') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_shifts_tables_user_id_shift_id_date_unique` (`user_id`,`shift_id`,`date`),
  KEY `employee_shifts_tables_shift_id_foreign` (`shift_id`),
  CONSTRAINT `employee_shifts_tables_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_shifts_tables_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employees_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_pengenal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banks_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fingerprint_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_employee` enum('PKWT','DW','PKWTT','On Job Training','Resign','PKWT (Contract)') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `marriage` enum('Yes','No') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `child` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pending_telp_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('Male','Female','MD') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biological_mother_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` enum('Catholic Christian','Christian','Islam','Hindu','Confucian','Buddha') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `id_card_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_education` enum('Elementary School','Junior High School','Senior High School','Bachelor Degree','Vocational School','Masters degree','Diploma I','Diploma II','Diploma III','Lord','Diploma IV') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_kes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_ket` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pending_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varbinary(255) DEFAULT NULL,
  `status` enum('Active','Pending','Inactive','On Leave','Mutation','Resign') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `pin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grading_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_manager` tinyint(1) DEFAULT '0',
  `remaining` int DEFAULT '0',
  `approved` int DEFAULT '0',
  `pending` int DEFAULT '0',
  `total` int DEFAULT '12',
  `is_manager_store` tinyint(1) DEFAULT '0',
  `structure_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_tables_employee_pengenal_unique` (`employee_pengenal`),
  UNIQUE KEY `employees_tables_employee_name_unique` (`employee_name`),
  UNIQUE KEY `employees_tables_pin_unique` (`pin`),
  KEY `employees_tables_position_id_foreign` (`position_id`),
  KEY `employees_tables_store_id_foreign` (`store_id`),
  KEY `employees_tables_department_id_foreign` (`department_id`),
  KEY `employees_tables_fingerprint_id_foreign` (`fingerprint_id`),
  KEY `employees_tables_company_id_foreign` (`company_id`),
  KEY `employees_tables_banks_id_foreign` (`banks_id`),
  KEY `employees_tables_grading_id_foreign` (`grading_id`),
  KEY `employees_tables_level_id_foreign` (`level_id`),
  KEY `employees_tables_structure_id_foreign` (`structure_id`),
  CONSTRAINT `employees_tables_banks_id_foreign` FOREIGN KEY (`banks_id`) REFERENCES `banks_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_tables_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `company_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_tables_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_tables_fingerprint_id_foreign` FOREIGN KEY (`fingerprint_id`) REFERENCES `fingerprint_devices_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_tables_grading_id_foreign` FOREIGN KEY (`grading_id`) REFERENCES `grading` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_tables_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `employees_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_tables_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `position_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_tables_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_tables_structure_id_foreign` FOREIGN KEY (`structure_id`) REFERENCES `employees_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fingerprint_devices_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fingerprint_devices_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `store_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_sync` timestamp NULL DEFAULT NULL,
  `status` enum('Active','Inactive','Maintenance') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fingerprint_devices_tables_serial_number_unique` (`serial_number`),
  KEY `fingerprint_devices_tables_store_id_foreign` (`store_id`),
  CONSTRAINT `fingerprint_devices_tables_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `grading`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grading` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `grading_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `grading_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leave_requests_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_requests_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leave_type` enum('Cuti','Sakit','Izin','Lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('Pending','Approved','Rejected','Canceled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `approved_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_requests_tables_user_id_foreign` (`user_id`),
  KEY `leave_requests_tables_approved_by_foreign` (`approved_by`),
  KEY `leave_requests_tables_start_date_index` (`start_date`),
  KEY `leave_requests_tables_end_date_index` (`end_date`),
  KEY `leave_requests_tables_status_index` (`status`),
  CONSTRAINT `leave_requests_tables_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_requests_tables_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `masterproduct_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `masterproduct_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `plu` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `long_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `brand_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uom_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taxstatus_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statusproduct_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `good_stock` int DEFAULT NULL,
  `bad_stock` int DEFAULT NULL,
  `cogs` decimal(12,2) DEFAULT NULL,
  `retailprice` decimal(12,2) DEFAULT NULL,
  `memberbronzeprice` decimal(12,2) DEFAULT NULL,
  `membersilverprice` decimal(12,2) DEFAULT NULL,
  `membergoldprice` decimal(12,2) DEFAULT NULL,
  `memberplatinumprice` decimal(12,2) DEFAULT NULL,
  `min_stock` int DEFAULT NULL,
  `max_stock` int DEFAULT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `masterproduct_tables_plu_unique` (`plu`),
  KEY `masterproduct_tables_brand_id_foreign` (`brand_id`),
  KEY `masterproduct_tables_category_id_foreign` (`category_id`),
  KEY `masterproduct_tables_uom_id_foreign` (`uom_id`),
  KEY `masterproduct_tables_taxstatus_id_foreign` (`taxstatus_id`),
  KEY `masterproduct_tables_statusproduct_id_foreign` (`statusproduct_id`),
  CONSTRAINT `masterproduct_tables_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands_tables` (`id`),
  CONSTRAINT `masterproduct_tables_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories_tables` (`id`),
  CONSTRAINT `masterproduct_tables_statusproduct_id_foreign` FOREIGN KEY (`statusproduct_id`) REFERENCES `statusproduct_tables` (`id`),
  CONSTRAINT `masterproduct_tables_taxstatus_id_foreign` FOREIGN KEY (`taxstatus_id`) REFERENCES `taxstatus_tables` (`id`),
  CONSTRAINT `masterproduct_tables_uom_id_foreign` FOREIGN KEY (`uom_id`) REFERENCES `uoms_tables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `payrolls_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payrolls_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `daily_allowance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attendance` int DEFAULT NULL,
  `house_allowance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meal_allowance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transport_allowance` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bonus` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `overtime` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `late_fine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `punishment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `period` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_kes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bpjs_ket` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `debt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deductions` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `take_home` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `month_year` date DEFAULT NULL,
  `attachment_path` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `information` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payrolls_tables_employee_id_foreign` (`employee_id`),
  CONSTRAINT `payrolls_tables_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ph` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('Hindu','Non Hindu','All') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `position_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `position_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('Active','Pending','Inactive','Reject','On Review') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reason_reject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_1` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_2` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_summary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `key_respon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qualifications` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `position_tables_approval_1_foreign` (`approval_1`),
  KEY `position_tables_approval_2_foreign` (`approval_2`),
  CONSTRAINT `position_tables_approval_1_foreign` FOREIGN KEY (`approval_1`) REFERENCES `structures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `position_tables_approval_2_foreign` FOREIGN KEY (`approval_2`) REFERENCES `structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `public_holidays_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `public_holidays_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `holiday_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `year` smallint DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '1',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `public_holidays_tables_date_index` (`date`),
  KEY `public_holidays_tables_year_index` (`year`),
  KEY `public_holidays_tables_is_recurring_index` (`is_recurring`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shift_swaps_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shift_swaps_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `requester_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiver_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_shift_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_shift_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Canceled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Pending',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `approved_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_swaps_tables_requester_id_foreign` (`requester_id`),
  KEY `shift_swaps_tables_receiver_id_foreign` (`receiver_id`),
  KEY `shift_swaps_tables_original_shift_id_foreign` (`original_shift_id`),
  KEY `shift_swaps_tables_new_shift_id_foreign` (`new_shift_id`),
  KEY `shift_swaps_tables_approved_by_foreign` (`approved_by`),
  CONSTRAINT `shift_swaps_tables_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shift_swaps_tables_new_shift_id_foreign` FOREIGN KEY (`new_shift_id`) REFERENCES `employee_shifts_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shift_swaps_tables_original_shift_id_foreign` FOREIGN KEY (`original_shift_id`) REFERENCES `employee_shifts_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shift_swaps_tables_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shift_swaps_tables_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `shifts_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shifts_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `store_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shift_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `last_sync` timestamp NULL DEFAULT NULL,
  `is_holiday` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shifts_tables_store_id_foreign` (`store_id`),
  CONSTRAINT `shifts_tables_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `statusproduct_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statusproduct_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `statusproduct_tables_status_unique` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stores_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stores_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `phone_num` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `manager_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stores_tables_manager_id_foreign` (`manager_id`),
  CONSTRAINT `stores_tables_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `structure` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_manager` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `structure_employee_id_foreign` (`employee_id`),
  KEY `structure_level_id_foreign` (`level_id`),
  CONSTRAINT `structure_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `structure_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `employees_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `structures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `structures` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `structure_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_manager_store` tinyint(1) DEFAULT '0',
  `is_manager_department` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `structure_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `structures_company_id_foreign` (`company_id`),
  KEY `structures_department_id_foreign` (`department_id`),
  KEY `structures_position_id_foreign` (`position_id`),
  KEY `structures_store_id_foreign` (`store_id`),
  KEY `structures_parent_id_foreign` (`parent_id`),
  CONSTRAINT `structures_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `company_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `structures_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `structures_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `structures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `structures_position_id_foreign` FOREIGN KEY (`position_id`) REFERENCES `position_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `structures_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submissions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approver_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('Annual Leave','Sick Leave','Overtime','Maternity Leave') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leave_date_from` datetime DEFAULT NULL,
  `leave_date_to` datetime DEFAULT NULL,
  `duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_submissions` enum('Cash','TOIL') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_toil` time DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `submissions_employee_id_foreign` (`employee_id`),
  KEY `submissions_approver_id_foreign` (`approver_id`),
  CONSTRAINT `submissions_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `employees_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `submissions_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submissions_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approvel_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('Cuti Tahunan','Izin Sakit','Lembur','Cuti Melahirkan','Izin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `taxstatus_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taxstatus_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `taxstatus` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `terms` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_lan_mac` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_wifi_mac` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `terms_device_lan_mac_unique` (`device_lan_mac`),
  UNIQUE KEY `terms_device_wifi_mac_unique` (`device_wifi_mac`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `uoms_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uoms_tables` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uom_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uom` enum('Piece','Dozen','Pack','Box','Kg','Gram','Liter','MLiter','Meter','MMeter') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uoms_tables_uom_code_unique` (`uom_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT NULL,
  `device_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_sessions_session_id_unique` (`session_id`),
  KEY `user_sessions_user_id_foreign` (`user_id`),
  CONSTRAINT `user_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `terms_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_terms_id_foreign` (`terms_id`),
  KEY `users_employee_id_foreign` (`employee_id`),
  CONSTRAINT `users_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees_tables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_terms_id_foreign` FOREIGN KEY (`terms_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_100000_create_password_resets_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_04_03_112822_create_position_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_04_03_112823_create_stores_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_04_03_112829_create_departments_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_04_03_112830_create_employees_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_04_03_112831_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_04_03_112833_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_04_08_142612_add_relation',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_04_09_102544_add_timestamp_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_04_11_140510_add_managerid_relation_to_store_tables',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_04_11_145616_add_status_to_employees_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_04_12_092124_add_status_employee_to_employees_tables',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_04_12_113624_add_employee_pengenal_to_employees_tables',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_04_12_210921_create_fingerprint_devices_tables',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_04_12_211656_create_fingerprint_devices_tables',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_04_12_212455_add_another_to_stores_tables',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_04_12_213408_add_finger_to_employees_tables',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_04_12_214554_create_shifts_tables',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_04_12_214850_create_employee_shifts_tables',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_04_12_215602_create_attendance_tables',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_04_12_220135_create_public_holidays_tables',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_04_12_220352_create_leave_requests_tables',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_04_12_220723_create_shift_swaps_tables',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_04_14_104350_create_brands_tables',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_04_14_105203_create_categories_tables',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_04_14_112202_create_uoms_tables',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_04_14_112941_create_uoms_tables',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_04_14_151537_create_jobs_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_04_14_152023_create_jobs_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_04_14_152142_create_jobs_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_04_20_214523_create_taxstatus_tables',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_04_20_222146_create_statusproduct_tables',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_04_20_224218_create_masterproduct_tables',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_04_20_224911_add_another_to_masterproduct_tables',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_04_26_223250_create_payrolls_tables',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_04_27_141548_create_payrolls_tables',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_04_28_223918_create_jobs_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_04_28_224009_create_failed_jobs_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_05_05_111152_create_permission_tables',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_05_10_115158_create_company_tables',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_05_10_122437_add_foto_to_company_tables',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_05_10_231539_add_company_id_to_employees_tables',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_05_11_141115_create_banks_tables',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_05_11_141348_add_banks_id_to_employees_tables',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2025_05_16_000256_create_permission_tables',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_05_16_003407_create_permission_tables',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_06_12_152038_create_submissions_tables',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_07_05_173839_add_pin_to_employees_tables',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_07_05_200152_create_attendance',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_07_05_202543_add_kantor_to_attendance',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_07_14_161358_create_attendancetotal',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_07_22_113912_add_attachment_to_edited_fingerprints',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_09_21_113250_create_announcements_table',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_09_22_223838_grading',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_09_23_000553_structure',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_09_23_020201_add_grading_id_to_employees_tables',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2025_09_23_031913_add_end_date_to_announcements',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_09_23_041707_ph',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_09_24_144945_add_end_date_to_employees_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_09_24_145746_add_end_date_to_employees_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2025_09_24_153156_add_beberapa_fitur_untuk_sctructure_to_employees_table',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2025_09_25_110532_alter_end_date_nullable_on_employees_tables',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2025_09_28_192955_add_pending_employee_to_employees_tables',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2025_10_01_110630_add_beberapa_fitur_untuk_leave_to_employees_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_10_01_144348_alter_payrolls_on_payrolls_tables',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_10_04_154249_create_submission',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2025_10_05_225625_create_submissions',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2025_10_06_162759_alter_date_on_submissions',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2025_10_06_170954_add_status_to_submissions',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2025_10_13_110451_add_time_toil_to_submissions',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2025_10_13_123507_add_time_toil_to_submissions',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2025_10_13_132227_add_id_submissions_to_employees_tables',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2025_10_13_154453_add_time_toil_to_submissions',57);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2025_10_16_164117_add_is_manager_store_to_employees_tables',58);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2025_10_17_095613_add_notes_to_submissions',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2025_10_19_182909_create_activity_log_table',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2025_10_19_182910_add_event_column_to_activity_log_table',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2025_10_19_182911_add_batch_uuid_column_to_activity_log_table',60);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2025_10_19_204212_alter_causer_id_in_activity_log_table',61);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2025_10_19_204310_alter_subject_id_in_activity_log_table',62);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2025_10_20_102048_alter_causer_id_in_activity_log_table',63);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2025_10_20_102115_alter_subject_id_in_activity_log_table',63);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2025_10_21_113944_structures',64);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2025_10_21_125205_add_position_id_to_structures',65);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2025_10_21_134108_add_nickname_to_departments_tables',66);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2025_10_21_200003_add_nickname_to_company_tables',67);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2025_10_21_201937_add_nickname_to_stores_tables',68);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2025_10_22_021108_add_relation_store_to_structures',69);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2025_10_22_022901_add_parent_id_to_structures',70);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2025_10_22_025809_add_relation_parent_id_to_structures',71);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2025_10_22_101423_add_relation_structure_id_to_employees_tables',72);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2025_10_24_115931_add_photo_to_employees_tables',73);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2025_10_24_125504_add_beberapafitur_yang_akanditambahkan_untuk_menunjang_structuresnew_to_positions_tables',74);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2025_10_24_132621_add_beberapafitur_yang_akanditambahkan_untuk_menunjang_structuresnew_selainstatus_to_positions_tables',75);
