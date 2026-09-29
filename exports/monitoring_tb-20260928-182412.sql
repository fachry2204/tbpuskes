-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: monitoring_tb
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `monitoring_tb`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `monitoring_tb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `monitoring_tb`;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entity_type` varchar(255) NOT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('monitoring-pasien-tb-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1789552122),('monitoring-pasien-tb-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1789552122;',1789552122);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cadres`
--

DROP TABLE IF EXISTS `cadres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cadres` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `nik` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `birth_place` varchar(255) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `phone` varchar(255) NOT NULL,
  `rt` varchar(3) NOT NULL,
  `rw` varchar(3) NOT NULL,
  `full_address` text NOT NULL,
  `working_area` varchar(255) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cadres_user_id_unique` (`user_id`),
  UNIQUE KEY `cadres_nik_unique` (`nik`),
  UNIQUE KEY `cadres_phone_unique` (`phone`),
  CONSTRAINT `cadres_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cadres`
--

LOCK TABLES `cadres` WRITE;
/*!40000 ALTER TABLE `cadres` DISABLE KEYS */;
INSERT INTO `cadres` VALUES (1,12,'3273016501900001','Nurhayati Rahma','Sukamaju','1990-02-01','P','6281390090001','004','009','Jl. Sehat Bersama No. 20, Sukamaju','Wilayah Binaan 1',NULL,1,'2026-09-15 12:24:38','2026-09-15 12:24:38',NULL),(2,13,'3273016501900002','Fitri Handayani','Sukamaju','1990-02-02','P','6281390090002','001','009','Jl. Sehat Bersama No. 21, Sukamaju','Wilayah Binaan 2',NULL,1,'2026-09-15 12:24:38','2026-09-15 12:24:38',NULL),(3,14,'3273016501900003','Ayu Lestari','Sukamaju','1990-02-03','P','6281390090003','001','009','Jl. Sehat Bersama No. 22, Sukamaju','Wilayah Binaan 3',NULL,1,'2026-09-15 12:24:38','2026-09-15 12:24:38',NULL),(4,15,'3273016501900004','Siti Rahmawati','Sukamaju','1990-02-04','P','6281390090004','002','009','Jl. Sehat Bersama No. 23, Sukamaju','Wilayah Binaan 4',NULL,1,'2026-09-15 12:24:38','2026-09-15 12:24:38',NULL),(5,16,'3273016501900005','Dian Puspita','Sukamaju','1990-02-05','P','6281390090005','002','009','Jl. Sehat Bersama No. 24, Sukamaju','Wilayah Binaan 5',NULL,1,'2026-09-15 12:24:38','2026-09-15 12:24:38',NULL),(6,17,'3273016501900006','Wulan Sari','Sukamaju','1990-02-06','P','6281390090006','002','009','Jl. Sehat Bersama No. 25, Sukamaju','Wilayah Binaan 6',NULL,1,'2026-09-15 12:24:39','2026-09-15 12:24:39',NULL),(7,18,'3273016501900007','Lilis Komariah','Sukamaju','1990-02-07','P','6281390090007','002','009','Jl. Sehat Bersama No. 26, Sukamaju','Wilayah Binaan 7',NULL,1,'2026-09-15 12:24:39','2026-09-15 12:24:39',NULL),(8,19,'3273016501900008','Rani Oktaviani','Sukamaju','1990-02-08','P','6281390090008','004','009','Jl. Sehat Bersama No. 27, Sukamaju','Wilayah Binaan 8',NULL,1,'2026-09-15 12:24:39','2026-09-15 12:24:39',NULL),(9,20,'3273016501900009','Maya Anggraini','Sukamaju','1990-02-09','P','6281390090009','001','009','Jl. Sehat Bersama No. 28, Sukamaju','Wilayah Binaan 9',NULL,1,'2026-09-15 12:24:39','2026-09-15 12:24:39',NULL),(10,21,'3273016501900010','Nisa Khairani','Sukamaju','1990-02-10','P','6281390090010','002','009','Jl. Sehat Bersama No. 29, Sukamaju','Wilayah Binaan 10',NULL,1,'2026-09-15 12:24:39','2026-09-15 12:24:39',NULL);
/*!40000 ALTER TABLE `cadres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `control_schedules`
--

DROP TABLE IF EXISTS `control_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `control_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint(20) unsigned NOT NULL,
  `treatment_place_id` bigint(20) unsigned NOT NULL,
  `control_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('scheduled','confirmed','attended','missed','rescheduled','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_by` bigint(20) unsigned NOT NULL,
  `attended_at` timestamp NULL DEFAULT NULL,
  `attendance_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `control_schedules_patient_id_foreign` (`patient_id`),
  KEY `control_schedules_treatment_place_id_foreign` (`treatment_place_id`),
  KEY `control_schedules_created_by_foreign` (`created_by`),
  KEY `control_schedules_control_date_index` (`control_date`),
  KEY `control_schedules_status_index` (`status`),
  CONSTRAINT `control_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `control_schedules_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `control_schedules_treatment_place_id_foreign` FOREIGN KEY (`treatment_place_id`) REFERENCES `treatment_places` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `control_schedules`
--

LOCK TABLES `control_schedules` WRITE;
/*!40000 ALTER TABLE `control_schedules` DISABLE KEYS */;
INSERT INTO `control_schedules` VALUES (1,10,1,'2026-09-18','08:25:00',NULL,'tes','tess ya','scheduled',1,NULL,NULL,'2026-09-15 12:23:05','2026-09-15 13:46:44');
/*!40000 ALTER TABLE `control_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medication_reports`
--

DROP TABLE IF EXISTS `medication_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint(20) unsigned NOT NULL,
  `patient_medication_plan_id` bigint(20) unsigned DEFAULT NULL,
  `schedule_time_id` bigint(20) unsigned DEFAULT NULL,
  `report_date` date NOT NULL,
  `scheduled_time` time DEFAULT NULL,
  `medication_taken` tinyint(1) NOT NULL,
  `not_taken_reason` text DEFAULT NULL,
  `has_side_effect` tinyint(1) NOT NULL DEFAULT 0,
  `side_effect_category` varchar(255) DEFAULT NULL,
  `side_effect_description` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `photo_hash` varchar(64) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `gps_accuracy` decimal(8,2) DEFAULT NULL,
  `formatted_address` text DEFAULT NULL,
  `device_reported_at` timestamp NULL DEFAULT NULL,
  `server_received_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('submitted','verified','rejected','follow_up') NOT NULL DEFAULT 'submitted',
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_slot_unique` (`patient_id`,`schedule_time_id`,`report_date`),
  KEY `medication_reports_patient_medication_plan_id_foreign` (`patient_medication_plan_id`),
  KEY `medication_reports_schedule_time_id_foreign` (`schedule_time_id`),
  KEY `medication_reports_verified_by_foreign` (`verified_by`),
  CONSTRAINT `medication_reports_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medication_reports_patient_medication_plan_id_foreign` FOREIGN KEY (`patient_medication_plan_id`) REFERENCES `patient_medication_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medication_reports_schedule_time_id_foreign` FOREIGN KEY (`schedule_time_id`) REFERENCES `medication_schedule_times` (`id`) ON DELETE SET NULL,
  CONSTRAINT `medication_reports_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medication_reports`
--

LOCK TABLES `medication_reports` WRITE;
/*!40000 ALTER TABLE `medication_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `medication_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medication_schedule_times`
--

DROP TABLE IF EXISTS `medication_schedule_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medication_schedule_times` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `patient_medication_plan_id` bigint(20) unsigned NOT NULL,
  `time_of_day` time DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `sequence` tinyint(3) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medication_schedule_times_patient_medication_plan_id_foreign` (`patient_medication_plan_id`),
  CONSTRAINT `medication_schedule_times_patient_medication_plan_id_foreign` FOREIGN KEY (`patient_medication_plan_id`) REFERENCES `patient_medication_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medication_schedule_times`
--

LOCK TABLES `medication_schedule_times` WRITE;
/*!40000 ALTER TABLE `medication_schedule_times` DISABLE KEYS */;
INSERT INTO `medication_schedule_times` VALUES (1,1,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(2,2,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(3,3,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(4,4,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(5,5,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(6,6,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(7,7,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(8,8,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(9,9,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(10,10,NULL,'Sekali sehari',1,'2026-09-15 22:21:12','2026-09-15 22:21:12');
/*!40000 ALTER TABLE `medication_schedule_times` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medications`
--

DROP TABLE IF EXISTS `medications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `strength` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon_png_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medications`
--

LOCK TABLES `medications` WRITE;
/*!40000 ALTER TABLE `medications` DISABLE KEYS */;
/*!40000 ALTER TABLE `medications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_15_000003_create_tb_core_tables',2),(5,'2026_09_15_152133_create_personal_access_tokens_table',3),(6,'2026_09_15_000004_create_operational_monitoring_tables',4),(7,'2026_09_15_000005_allow_untimed_once_daily_medication',5),(8,'2026_09_15_000006_create_push_subscriptions_table',6),(9,'2026_09_16_000001_make_plan_medication_optional',7),(10,'2026_09_16_000002_add_tb_diagnosis_to_patients_table',8),(11,'2026_09_16_000003_create_default_medication_schedules_for_existing_patients',9);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_medication_plans`
--

DROP TABLE IF EXISTS `patient_medication_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_medication_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint(20) unsigned NOT NULL,
  `medication_id` bigint(20) unsigned DEFAULT NULL,
  `dose` varchar(255) NOT NULL,
  `dose_unit` varchar(255) DEFAULT NULL,
  `frequency_per_day` tinyint(3) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_medication_plans_patient_id_foreign` (`patient_id`),
  KEY `patient_medication_plans_medication_id_foreign` (`medication_id`),
  CONSTRAINT `patient_medication_plans_medication_id_foreign` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`),
  CONSTRAINT `patient_medication_plans_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_medication_plans`
--

LOCK TABLES `patient_medication_plans` WRITE;
/*!40000 ALTER TABLE `patient_medication_plans` DISABLE KEYS */;
INSERT INTO `patient_medication_plans` VALUES (1,1,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(2,2,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(3,3,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(4,4,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(5,5,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(6,6,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(7,7,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(8,8,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(9,9,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12'),(10,10,NULL,'Sesuai anjuran',NULL,1,'2026-01-15',NULL,NULL,1,'2026-09-15 22:21:12','2026-09-15 22:21:12');
/*!40000 ALTER TABLE `patient_medication_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_notes`
--

DROP TABLE IF EXISTS `patient_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_notes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint(20) unsigned NOT NULL,
  `author_id` bigint(20) unsigned NOT NULL,
  `note` text NOT NULL,
  `note_date` date NOT NULL,
  `type` enum('general','medication','control','visit','follow_up') NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_notes_patient_id_foreign` (`patient_id`),
  KEY `patient_notes_author_id_foreign` (`author_id`),
  CONSTRAINT `patient_notes_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  CONSTRAINT `patient_notes_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_notes`
--

LOCK TABLES `patient_notes` WRITE;
/*!40000 ALTER TABLE `patient_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `patient_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `medical_record_number` varchar(255) DEFAULT NULL,
  `nik` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `birth_place` varchar(255) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `phone` varchar(255) NOT NULL,
  `rt` varchar(3) NOT NULL,
  `rw` varchar(3) NOT NULL,
  `full_address` text NOT NULL,
  `treatment_place_id` bigint(20) unsigned NOT NULL,
  `cadre_id` bigint(20) unsigned DEFAULT NULL,
  `treatment_start_date` date NOT NULL,
  `tb_diagnosis` varchar(255) DEFAULT NULL,
  `diagnosis_type` varchar(255) DEFAULT NULL,
  `daily_dose_frequency` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `status` enum('active','completed','paused','moved','deceased') NOT NULL DEFAULT 'active',
  `photo_path` varchar(255) DEFAULT NULL,
  `home_latitude` decimal(10,7) DEFAULT NULL,
  `home_longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_user_id_unique` (`user_id`),
  UNIQUE KEY `patients_nik_unique` (`nik`),
  UNIQUE KEY `patients_medical_record_number_unique` (`medical_record_number`),
  KEY `patients_treatment_place_id_foreign` (`treatment_place_id`),
  KEY `patients_cadre_id_foreign` (`cadre_id`),
  KEY `patients_phone_index` (`phone`),
  KEY `patients_status_index` (`status`),
  CONSTRAINT `patients_cadre_id_foreign` FOREIGN KEY (`cadre_id`) REFERENCES `cadres` (`id`) ON DELETE SET NULL,
  CONSTRAINT `patients_treatment_place_id_foreign` FOREIGN KEY (`treatment_place_id`) REFERENCES `treatment_places` (`id`),
  CONSTRAINT `patients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES (1,2,'RM-2026-01','3273011601900001','Siti Aminah','Sukamaju','1990-01-01','L','6281290090001','004','009','Jl. Sehat Bersama No. 1, Sukamaju',1,1,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:03','2026-09-15 12:24:39',NULL),(2,3,'RM-2026-02','3273011601900002','Budi Santoso','Sukamaju','1990-01-02','P','6281290090002','003','009','Jl. Sehat Bersama No. 2, Sukamaju',1,2,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:03','2026-09-15 12:24:39',NULL),(3,4,'RM-2026-03','3273011601900003','Rina Wati','Sukamaju','1990-01-03','P','6281290090003','004','009','Jl. Sehat Bersama No. 3, Sukamaju',1,3,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:03','2026-09-15 12:24:39',NULL),(4,5,'RM-2026-04','3273011601900004','Andi Pratama','Sukamaju','1990-01-04','L','6281290090004','004','009','Jl. Sehat Bersama No. 4, Sukamaju',1,4,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 12:24:39',NULL),(5,6,'RM-2026-05','3273011601900005','Nur Hayati','Sukamaju','1990-01-05','P','6281290090005','001','009','Jl. Sehat Bersama No. 5, Sukamaju',1,5,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 12:24:39',NULL),(6,7,'RM-2026-06','3273011601900006','Ahmad Fauzi','Sukamaju','1990-01-06','P','6281290090006','003','009','Jl. Sehat Bersama No. 6, Sukamaju',1,6,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 12:24:39',NULL),(7,8,'RM-2026-07','3273011601900007','Dewi Lestari','Sukamaju','1990-01-07','L','6281290090007','001','009','Jl. Sehat Bersama No. 7, Sukamaju',1,7,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 12:24:39',NULL),(8,9,'RM-2026-08','3273011601900008','Joko Susilo','Sukamaju','1990-01-08','P','6281290090008','002','009','Jl. Sehat Bersama No. 8, Sukamaju',1,8,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 12:24:39',NULL),(9,10,'RM-2026-09','3273011601900009','Maya Sari','Sukamaju','1990-01-09','P','6281290090009','001','009','Jl. Sehat Bersama No. 9, Sukamaju',1,9,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 12:24:39',NULL),(10,11,'RM-2026-10','3273011601900010','Hendra Wijaya','Sukamaju','1990-01-10','L','6281290090010','004','009','Jl. Sehat Bersama No. 10, Sukamaju',1,10,'2026-01-15',NULL,NULL,1,'active',NULL,NULL,NULL,'2026-09-15 12:14:05','2026-09-15 12:24:39',NULL);
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26200; en-US) PowerShell/7.6.5','1aefb6ec9f79be45ea58a66a4dd0cee5b7688ccbff59834b5869a026c1855d46','[\"*\"]',NULL,'2026-10-15 10:00:38','2026-09-15 10:00:38','2026-09-15 10:00:38'),(2,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26200; en-US) PowerShell/7.6.5','7089db5f7efc56a5b6c4969bb9b88f56498d5038f98b565045a2afac8f5190c1','[\"*\"]',NULL,'2026-10-15 11:16:42','2026-09-15 11:16:42','2026-09-15 11:16:42'),(3,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26200; en-US) PowerShell/7.6.5','6651b592ff331c6647cb771dee9fa1f5398945fd2fbae095f1836465ebb27f1b','[\"*\"]',NULL,'2026-10-15 11:18:13','2026-09-15 11:18:13','2026-09-15 11:18:13'),(4,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26200; en-US) PowerShell/7.6.5','18c1a2b4d1154159cdc90b3e777b5d4cfc3523d3a8829352493e3dfc0dc2d27a','[\"*\"]',NULL,'2026-10-15 11:21:06','2026-09-15 11:21:06','2026-09-15 11:21:06'),(5,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','14d01b5460b1dfc61ac7c3a8fd708a017498cfd8b0c9a4d4561fa4f2f269b1cd','[\"*\"]','2026-09-15 13:23:19','2026-10-15 11:23:12','2026-09-15 11:23:12','2026-09-15 13:23:19'),(6,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26200; en-US) PowerShell/7.6.5','41f09fbbc723c330b255d752688cd4059389c4c22eec1a3aaf1cac81a76fb2dd','[\"*\"]','2026-09-15 11:36:58','2026-10-15 11:36:58','2026-09-15 11:36:58','2026-09-15 11:36:58'),(7,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26200; en-US) PowerShell/7.6.5','98ac2cd589adc9b283764f5da314c093417277aca923f93e7d6d73e923c97920','[\"*\"]','2026-09-15 12:29:24','2026-10-15 12:29:24','2026-09-15 12:29:24','2026-09-15 12:29:24'),(8,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','52f5b2ae4ed0dbf0fb88048737d36fee56db37ae6e48ed8bf9c36f5b64657e89','[\"*\"]','2026-09-15 13:33:22','2026-10-15 13:26:04','2026-09-15 13:26:04','2026-09-15 13:33:22'),(9,'App\\Models\\User',17,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','1df5c3682a3779c39f1454fcf2996ee4188fe136afb5af6c9aad7220bfc57468','[\"*\"]','2026-09-15 13:33:40','2026-10-15 13:33:39','2026-09-15 13:33:39','2026-09-15 13:33:40'),(10,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','84d12df726c62eb3c3014731e8f0a8b581bba6779662dc99bf90ba2909558119','[\"*\"]','2026-09-15 14:34:18','2026-10-15 13:34:47','2026-09-15 13:34:47','2026-09-15 14:34:18'),(11,'App\\Models\\User',17,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','3db253b326e696f13b3fd8f0a40882dd16dbc2a51379419232a43853d0799e77','[\"*\"]','2026-09-15 15:04:16','2026-10-15 14:34:27','2026-09-15 14:34:27','2026-09-15 15:04:16'),(12,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','8c4fb706f7335e28ea8e6bbb5b96f7d17f46e10dd2f59bead805394fb871983c','[\"*\"]','2026-09-15 15:05:20','2026-10-15 15:05:11','2026-09-15 15:05:11','2026-09-15 15:05:20'),(13,'App\\Models\\User',11,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','c76e197f6d93612cdc341272ae2c96a6983394484a090f63475f3f0517e297e1','[\"*\"]','2026-09-15 15:24:38','2026-10-15 15:06:34','2026-09-15 15:06:34','2026-09-15 15:24:38'),(14,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','c3b885f22fe95e86b369e2759cf0f79bdb74f1c8e7c1a8a379566080dc7c1acc','[\"*\"]','2026-09-15 15:26:42','2026-10-15 15:26:36','2026-09-15 15:26:36','2026-09-15 15:26:42'),(15,'App\\Models\\User',6,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','6af6f8b1e921bfd9a8c36c370ff905ce4b01cd0b73f122c9dcabd9fbbb9f733d','[\"*\"]','2026-09-15 15:28:45','2026-10-15 15:27:04','2026-09-15 15:27:04','2026-09-15 15:28:45'),(16,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','a6c1a2441ddf14d2704aaf2c5cbf56d027339776e425ae4ac616a2f632f92bac','[\"*\"]','2026-09-15 22:16:21','2026-10-15 22:09:07','2026-09-15 22:09:07','2026-09-15 22:16:21'),(17,'App\\Models\\User',11,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','b90007c7aa5b9331a93b7f9ecd7d6557cce74ec1de354d9feff31545134293ef','[\"*\"]','2026-09-16 02:32:38','2026-10-15 22:16:38','2026-09-15 22:16:38','2026-09-16 02:32:38'),(18,'App\\Models\\User',1,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','608e0d1a5cef69b5bb9cfb5410792b5b21ed652d56608b32d5720699c70f8bc4','[\"*\"]','2026-09-16 03:01:51','2026-10-15 22:17:30','2026-09-15 22:17:30','2026-09-16 03:01:51'),(19,'App\\Models\\User',17,'web-Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','33974ee1171a0198db6c684e548e303056afd048acbef9f1264fb6e4eff2a09c','[\"*\"]','2026-09-16 02:47:44','2026-10-16 02:47:42','2026-09-16 02:47:42','2026-09-16 02:47:44');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `push_subscriptions`
--

DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `endpoint` text NOT NULL,
  `endpoint_hash` varchar(64) NOT NULL,
  `public_key` text NOT NULL,
  `auth_token` text NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `browser` varchar(255) DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_subscriptions_user_id_endpoint_hash_unique` (`user_id`,`endpoint_hash`),
  CONSTRAINT `push_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `push_subscriptions`
--

LOCK TABLES `push_subscriptions` WRITE;
/*!40000 ALTER TABLE `push_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('13OYI4PnZByIVmTaARqqXCnFkwrfCGjDq3dzQOhv',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoibktMVUo2dG5Mam5oZHZrazNPRHpIQW50QkFOUHZOeEdDd2xLNmxoZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAxMC9hZG1pbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789552769),('3huurfzZOStj2BYzxahSRr1xGMJfFwNkwVM137gD',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS092cjU2aWFMUHAzQWx4eHVwNHhmd1dORG9hM0VscnJiVHl4ZlRmRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAxMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789552875),('40pdgw055gqnKf9JcmWBV0dPDZxWlmQB1FyjeGlN',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUHp6T21sWkdwV25Dc2xnd1NENDVvRmFuR25Sa3ZDWE4xaTlOWVZENCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAxMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789551456),('A4T5dqD62VoHDr2hbpw9IDDSXCq9rMKenyogI0Ib',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoia1dCMVljWmN0RlE5YzhZSkhkQTVWd2xOMExaTTU1QlJDcjA4Qlp3MyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAxMC9hZG1pbi9yZXBvcnRzIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1789552910);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext NOT NULL,
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `side_effect_reports`
--

DROP TABLE IF EXISTS `side_effect_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `side_effect_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `medication_report_id` bigint(20) unsigned NOT NULL,
  `patient_id` bigint(20) unsigned NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `severity` enum('low','medium','high') NOT NULL DEFAULT 'low',
  `requires_follow_up` tinyint(1) NOT NULL DEFAULT 0,
  `follow_up_status` enum('open','contacted','resolved') NOT NULL DEFAULT 'open',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `side_effect_reports_medication_report_id_foreign` (`medication_report_id`),
  KEY `side_effect_reports_patient_id_foreign` (`patient_id`),
  KEY `side_effect_reports_assigned_to_foreign` (`assigned_to`),
  KEY `side_effect_reports_requires_follow_up_index` (`requires_follow_up`),
  CONSTRAINT `side_effect_reports_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `side_effect_reports_medication_report_id_foreign` FOREIGN KEY (`medication_report_id`) REFERENCES `medication_reports` (`id`) ON DELETE CASCADE,
  CONSTRAINT `side_effect_reports_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `side_effect_reports`
--

LOCK TABLES `side_effect_reports` WRITE;
/*!40000 ALTER TABLE `side_effect_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `side_effect_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `treatment_places`
--

DROP TABLE IF EXISTS `treatment_places`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `treatment_places` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `treatment_places`
--

LOCK TABLES `treatment_places` WRITE;
/*!40000 ALTER TABLE `treatment_places` DISABLE KEYS */;
INSERT INTO `treatment_places` VALUES (1,'Puskesmas Sukamaju','Puskesmas','Jl. Sehat Bersama No. 1','0210000000',NULL,NULL,1,'2026-09-15 12:14:03','2026-09-15 12:14:03');
/*!40000 ALTER TABLE `treatment_places` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_notifications`
--

DROP TABLE IF EXISTS `user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `related_entity_type` varchar(255) DEFAULT NULL,
  `related_entity_id` bigint(20) unsigned DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_notifications_user_id_foreign` (`user_id`),
  KEY `user_notifications_is_read_index` (`is_read`),
  CONSTRAINT `user_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_notifications`
--

LOCK TABLES `user_notifications` WRITE;
/*!40000 ALTER TABLE `user_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` enum('admin','staff','kader','pasien') NOT NULL DEFAULT 'pasien',
  `avatar_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  KEY `users_role_index` (`role`),
  KEY `users_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin','admin@mail.com',NULL,'$2y$10$wU8p4ieQ7dWcBC1W/Nwhn.cA1XH.fJ2/BBgQNOA.wHwHicdf09/wm','628111111111','admin',NULL,1,'2026-09-15 22:17:30',NULL,'2026-09-15 09:44:35','2026-09-16 02:59:24',NULL),(2,'Siti Aminah',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090001','pasien',NULL,1,NULL,NULL,'2026-09-15 12:14:03','2026-09-15 15:06:24',NULL),(3,'Budi Santoso',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090002','pasien',NULL,1,NULL,NULL,'2026-09-15 12:14:03','2026-09-15 15:06:24',NULL),(4,'Rina Wati',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090003','pasien',NULL,1,NULL,NULL,'2026-09-15 12:14:03','2026-09-15 15:06:24',NULL),(5,'Andi Pratama',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090004','pasien',NULL,1,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 15:06:24',NULL),(6,'Nur Hayati',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090005','pasien',NULL,1,'2026-09-15 15:27:04',NULL,'2026-09-15 12:14:04','2026-09-15 15:27:04',NULL),(7,'Ahmad Fauzi',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090006','pasien',NULL,1,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 15:06:24',NULL),(8,'Dewi Lestari',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090007','pasien',NULL,1,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 15:06:24',NULL),(9,'Joko Susilo',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090008','pasien',NULL,1,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 15:06:24',NULL),(10,'Maya Sari',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090009','pasien',NULL,1,NULL,NULL,'2026-09-15 12:14:04','2026-09-15 15:06:24',NULL),(11,'Hendra Wijaya',NULL,NULL,NULL,'$2y$12$Cf0ukqS3xtEYEdiM8gyMD.b6R3TMqmEvSCe9Gu3KWafJImGYWZxJO','6281290090010','pasien',NULL,1,'2026-09-15 22:16:38',NULL,'2026-09-15 12:14:05','2026-09-15 22:16:38',NULL),(12,'Nurhayati Rahma',NULL,NULL,NULL,'$2y$12$ttRjktBuJ6aKWUfc/BtgFeNHCu/kJjRHaY/r8fLw97f70ziXJlZGS','6281390090001','kader',NULL,1,NULL,NULL,'2026-09-15 12:24:38','2026-09-15 13:26:00',NULL),(13,'Fitri Handayani',NULL,NULL,NULL,'$2y$12$weDVl0agDTPN4t73zk5ytOe1YmReovN/Hqndd6iA4.Urgq5PO9SUW','6281390090002','kader',NULL,1,NULL,NULL,'2026-09-15 12:24:38','2026-09-15 13:26:00',NULL),(14,'Ayu Lestari',NULL,NULL,NULL,'$2y$12$5u4kWbsoI5sXHs93KKi8BOUfW0Tn6D5RC2hXrO.FN8sN1Gjj9IpWa','6281390090003','kader',NULL,1,NULL,NULL,'2026-09-15 12:24:38','2026-09-15 13:26:01',NULL),(15,'Siti Rahmawati',NULL,NULL,NULL,'$2y$12$rYR.vQiM9NPSdI7IWu60XubUKntiiDO63MQk6H7iW7CLDdxDrfwMm','6281390090004','kader',NULL,1,NULL,NULL,'2026-09-15 12:24:38','2026-09-15 13:26:01',NULL),(16,'Dian Puspita',NULL,NULL,NULL,'$2y$12$StNPKBlMH4b5d80WQmVA/.ulKb7xaMbSw.67ZifVK8aYjcnKQDYUK','6281390090005','kader',NULL,1,NULL,NULL,'2026-09-15 12:24:38','2026-09-15 13:26:01',NULL),(17,'Wulan Sari',NULL,NULL,NULL,'$2y$12$dDbRceKCtQkdjKF8jIBCq.DTB.xGSzBkvou/XiZQnRnBU6hJIEUF6','6281390090006','kader',NULL,1,'2026-09-16 02:47:42',NULL,'2026-09-15 12:24:39','2026-09-16 02:47:42',NULL),(18,'Lilis Komariah',NULL,NULL,NULL,'$2y$12$RlfbAErcO2eT9C53itP3GuEaxSC79BXfTg2wwZn.areuo90o3MxmC','6281390090007','kader',NULL,1,NULL,NULL,'2026-09-15 12:24:39','2026-09-15 13:26:01',NULL),(19,'Rani Oktaviani',NULL,NULL,NULL,'$2y$12$u5KJ8YRLphtmDk3zhaS6p.M3aqulEz2buNSFNwyPOEXqoHS2pXbK2','6281390090008','kader',NULL,1,NULL,NULL,'2026-09-15 12:24:39','2026-09-15 13:26:02',NULL),(20,'Maya Anggraini',NULL,NULL,NULL,'$2y$12$UH7GKlq8TRsPpDhGSQPqMu4gN.gXB1j29vUaaODm2x8JhQLDX8huu','6281390090009','kader',NULL,1,NULL,NULL,'2026-09-15 12:24:39','2026-09-15 13:26:02',NULL),(21,'Nisa Khairani',NULL,NULL,NULL,'$2y$12$d6e6y0xx41GvAQ2817N4/uZrLPecWKgPPlTiH2op5Ri3CYqCvWmYS','6281390090010','kader',NULL,1,NULL,NULL,'2026-09-15 12:24:39','2026-09-15 13:26:02',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'monitoring_tb'
--

--
-- Dumping routines for database 'monitoring_tb'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-28 18:24:12
