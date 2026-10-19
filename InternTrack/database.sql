-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: interntrack
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
-- Current Database: `interntrack`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `interntrack` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `interntrack`;

--
-- Table structure for table `absensi`
--

DROP TABLE IF EXISTS `absensi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `absensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `penempatan_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpa') NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_absensi_siswa` (`siswa_id`),
  KEY `fk_absensi_penempatan` (`penempatan_id`),
  CONSTRAINT `fk_absensi_penempatan` FOREIGN KEY (`penempatan_id`) REFERENCES `penempatan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_absensi_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi`
--

LOCK TABLES `absensi` WRITE;
/*!40000 ALTER TABLE `absensi` DISABLE KEYS */;
INSERT INTO `absensi` VALUES (1,1,1,'2026-10-01','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(2,1,1,'2026-10-02','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(3,1,1,'2026-10-05','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(4,1,1,'2026-10-06','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(5,1,1,'2026-10-07','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(6,1,1,'2026-10-08','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(7,1,1,'2026-10-09','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(8,1,1,'2026-10-12','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(9,1,1,'2026-10-13','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(10,1,1,'2026-10-14','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(11,2,2,'2026-10-01','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(12,2,2,'2026-10-02','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(13,2,2,'2026-10-05','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(14,2,2,'2026-10-06','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(15,2,2,'2026-10-07','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(16,2,2,'2026-10-08','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(17,2,2,'2026-10-09','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(18,2,2,'2026-10-12','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(19,2,2,'2026-10-13','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(20,2,2,'2026-10-14','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(21,3,3,'2026-10-01','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(22,3,3,'2026-10-02','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(23,3,3,'2026-10-05','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(24,3,3,'2026-10-06','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(25,3,3,'2026-10-07','Izin','Urusan keluarga','2026-10-19 02:24:08'),(26,3,3,'2026-10-08','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(27,3,3,'2026-10-09','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(28,3,3,'2026-10-12','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(29,3,3,'2026-10-13','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(30,3,3,'2026-10-14','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(31,4,4,'2026-10-01','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(32,4,4,'2026-10-02','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(33,4,4,'2026-10-05','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(34,4,4,'2026-10-06','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(35,4,4,'2026-10-07','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(36,4,4,'2026-10-08','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(37,4,4,'2026-10-09','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(38,4,4,'2026-10-12','Izin','Kepentingan sekolah','2026-10-19 02:24:08'),(39,4,4,'2026-10-13','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(40,4,4,'2026-10-14','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(41,5,5,'2026-10-01','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(42,5,5,'2026-10-02','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(43,5,5,'2026-10-05','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(44,5,5,'2026-10-06','Sakit','Demam surat dokter','2026-10-19 02:24:08'),(45,5,5,'2026-10-07','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(46,5,5,'2026-10-08','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(47,5,5,'2026-10-09','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(48,5,5,'2026-10-12','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(49,5,5,'2026-10-13','Sakit','Demam surat dokter','2026-10-19 02:24:08'),(50,5,5,'2026-10-14','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(51,6,6,'2026-10-01','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(52,6,6,'2026-10-02','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(53,6,6,'2026-10-05','Sakit','Flu','2026-10-19 02:24:08'),(54,6,6,'2026-10-06','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(55,6,6,'2026-10-07','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(56,6,6,'2026-10-08','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(57,6,6,'2026-10-09','Sakit','Flu','2026-10-19 02:24:08'),(58,6,6,'2026-10-12','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(59,6,6,'2026-10-13','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(60,6,6,'2026-10-14','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(61,7,7,'2026-10-01','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(62,7,7,'2026-10-02','Izin','Acara keluarga','2026-10-19 02:24:08'),(63,7,7,'2026-10-05','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(64,7,7,'2026-10-06','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(65,7,7,'2026-10-07','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(66,7,7,'2026-10-08','Izin','Acara keluarga','2026-10-19 02:24:08'),(67,7,7,'2026-10-09','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(68,7,7,'2026-10-12','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(69,7,7,'2026-10-13','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(70,7,7,'2026-10-14','Alpa','Tanpa keterangan','2026-10-19 02:24:08'),(71,8,8,'2026-10-01','Izin','Keluarga sakit','2026-10-19 02:24:08'),(72,8,8,'2026-10-02','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(73,8,8,'2026-10-05','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(74,8,8,'2026-10-06','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(75,8,8,'2026-10-07','Izin','Keluarga sakit','2026-10-19 02:24:08'),(76,8,8,'2026-10-08','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(77,8,8,'2026-10-09','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(78,8,8,'2026-10-12','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(79,8,8,'2026-10-13','Alpa','Tidak hadir','2026-10-19 02:24:08'),(80,8,8,'2026-10-14','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(81,9,9,'2026-10-01','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(82,9,9,'2026-10-02','Alpa','Tanpa kabar','2026-10-19 02:24:08'),(83,9,9,'2026-10-05','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(84,9,9,'2026-10-06','Alpa','Tanpa kabar','2026-10-19 02:24:08'),(85,9,9,'2026-10-07','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(86,9,9,'2026-10-08','Alpa','Tanpa kabar','2026-10-19 02:24:08'),(87,9,9,'2026-10-09','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(88,9,9,'2026-10-12','Sakit','Sakit kepala','2026-10-19 02:24:08'),(89,9,9,'2026-10-13','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(90,9,9,'2026-10-14','Sakit','Sakit kepala','2026-10-19 02:24:08'),(91,10,10,'2026-10-01','Alpa','Tanpa keterangan','2026-10-19 02:24:08'),(92,10,10,'2026-10-02','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(93,10,10,'2026-10-05','Alpa','Tanpa keterangan','2026-10-19 02:24:08'),(94,10,10,'2026-10-06','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(95,10,10,'2026-10-07','Sakit','Istirahat dokter','2026-10-19 02:24:08'),(96,10,10,'2026-10-08','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(97,10,10,'2026-10-09','Alpa','Tanpa keterangan','2026-10-19 02:24:08'),(98,10,10,'2026-10-12','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08'),(99,10,10,'2026-10-13','Sakit','Istirahat dokter','2026-10-19 02:24:08'),(100,10,10,'2026-10-14','Hadir','Masuk kerja tepat waktu','2026-10-19 02:24:08');
/*!40000 ALTER TABLE `absensi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal`
--

DROP TABLE IF EXISTS `jurnal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `penempatan_id` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `kegiatan` text NOT NULL,
  `hasil` text NOT NULL,
  `kendala` text DEFAULT NULL,
  `catatan_pembimbing` text DEFAULT NULL,
  `status_validasi` enum('Menunggu','Disetujui','Revisi') DEFAULT 'Menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_jurnal_siswa` (`siswa_id`),
  KEY `fk_jurnal_penempatan` (`penempatan_id`),
  CONSTRAINT `fk_jurnal_penempatan` FOREIGN KEY (`penempatan_id`) REFERENCES `penempatan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_jurnal_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal`
--

LOCK TABLES `jurnal` WRITE;
/*!40000 ALTER TABLE `jurnal` DISABLE KEYS */;
INSERT INTO `jurnal` VALUES (1,1,1,'2026-10-19','Membuat Desain Database','ERD berhasil diselesaikan dan dinormalisasi','Kesulitan menentukan relasi kardinalitas many-to-many','Bagus, lanjutkan pembuatan tabel fisik','Disetujui','2026-10-19 02:24:08'),(2,2,2,'2026-10-19','Implementasi Modul Autentikasi','Halaman login dan middleware role siswa/admin selesai','Session timeout kadang belum ter-destroy sempurna','Pastikan session_destroy dipanggil saat logout','Disetujui','2026-10-19 02:24:08'),(3,3,3,'2026-10-19','Konfigurasi Web Server Apache & Nginx','Virtual host berhasil diuji dan berjalan optimal','Port conflict pada 8080','Ganti port pada berkas konfigurasi httpd.conf','Disetujui','2026-10-19 02:24:08'),(4,4,4,'2026-10-19','Slicing Desain Figma ke HTML5 & Tailwind CSS','Tampilan responsif untuk desktop dan mobile selesai','Breakpoint mobile navigation perlu penyesuaian',NULL,'Menunggu','2026-10-19 02:24:08'),(5,5,5,'2026-10-19','Pembuatan REST API Endpoint Siswa','Endpoint CRUD JSON berhasil diuji via Postman','Format error validation belum seragam','Harap melengkapi hasil dokumentasi foto kegiatan','Revisi','2026-10-19 02:24:08'),(6,6,6,'2026-10-18','Testing dan Debugging Query SQL','Query pencarian dan filter berjalan di bawah 50ms','Slow query pada tabel absensi tanpa index','Tambahkan index pada kolom tanggal dan siswa_id','Disetujui','2026-10-19 02:24:08'),(7,7,7,'2026-10-18','Pemasangan Kabel Jaringan LAN & Crimping RJ-45','10 kabel UTP lolos pengujian LAN tester','Satu konektor pin 3 tidak tembus','Potong ulang dan pastikan urutan T568B presisi','Disetujui','2026-10-19 02:24:08'),(8,8,8,'2026-10-17','Instalasi dan Konfigurasi Mikrotik RouterOS','Bandwidth management queue tree dan hotspot aktif','IP pool sempat overlap','Cek subnet mask pada DHCP Server range','Disetujui','2026-10-19 02:24:08'),(9,9,9,'2026-10-17','Backup dan Replikasi Database MySQL','Automated script crontab mysqldump berjalan harian','Penyimpanan lokal cepat penuh',NULL,'Menunggu','2026-10-19 02:24:08'),(10,10,10,'2026-10-16','Penyusunan Dokumentasi API & Manual Pengguna','Dokumentasi format OpenAPI/Swagger rampung','Belum ada panduan pemula berbahasa Indonesia','Harap melengkapi hasil dokumentasi foto kegiatan','Revisi','2026-10-19 02:24:08'),(11,11,11,'2026-10-16','Membuat Desain Database','ERD berhasil diselesaikan dan dinormalisasi','Kesulitan menentukan relasi kardinalitas many-to-many','Bagus, lanjutkan pembuatan tabel fisik','Disetujui','2026-10-19 02:24:08'),(12,12,12,'2026-10-15','Implementasi Modul Autentikasi','Halaman login dan middleware role siswa/admin selesai','Session timeout kadang belum ter-destroy sempurna','Pastikan session_destroy dipanggil saat logout','Disetujui','2026-10-19 02:24:08'),(13,13,13,'2026-10-15','Konfigurasi Web Server Apache & Nginx','Virtual host berhasil diuji dan berjalan optimal','Port conflict pada 8080','Ganti port pada berkas konfigurasi httpd.conf','Disetujui','2026-10-19 02:24:08'),(14,14,14,'2026-10-14','Slicing Desain Figma ke HTML5 & Tailwind CSS','Tampilan responsif untuk desktop dan mobile selesai','Breakpoint mobile navigation perlu penyesuaian',NULL,'Menunggu','2026-10-19 02:24:08'),(15,15,15,'2026-10-14','Pembuatan REST API Endpoint Siswa','Endpoint CRUD JSON berhasil diuji via Postman','Format error validation belum seragam','Harap melengkapi hasil dokumentasi foto kegiatan','Revisi','2026-10-19 02:24:08'),(16,16,16,'2026-10-13','Testing dan Debugging Query SQL','Query pencarian dan filter berjalan di bawah 50ms','Slow query pada tabel absensi tanpa index','Tambahkan index pada kolom tanggal dan siswa_id','Disetujui','2026-10-19 02:24:08'),(17,17,17,'2026-10-12','Pemasangan Kabel Jaringan LAN & Crimping RJ-45','10 kabel UTP lolos pengujian LAN tester','Satu konektor pin 3 tidak tembus','Potong ulang dan pastikan urutan T568B presisi','Disetujui','2026-10-19 02:24:08'),(18,18,18,'2026-10-11','Instalasi dan Konfigurasi Mikrotik RouterOS','Bandwidth management queue tree dan hotspot aktif','IP pool sempat overlap','Cek subnet mask pada DHCP Server range','Disetujui','2026-10-19 02:24:08'),(19,19,19,'2026-10-10','Backup dan Replikasi Database MySQL','Automated script crontab mysqldump berjalan harian','Penyimpanan lokal cepat penuh',NULL,'Menunggu','2026-10-19 02:24:08'),(20,20,20,'2026-10-09','Penyusunan Dokumentasi API & Manual Pengguna','Dokumentasi format OpenAPI/Swagger rampung','Belum ada panduan pemula berbahasa Indonesia','Harap melengkapi hasil dokumentasi foto kegiatan','Revisi','2026-10-19 02:24:08'),(21,21,21,'2026-10-08','Membuat Desain Database','ERD berhasil diselesaikan dan dinormalisasi','Kesulitan menentukan relasi kardinalitas many-to-many','Bagus, lanjutkan pembuatan tabel fisik','Disetujui','2026-10-19 02:24:08'),(22,22,22,'2026-10-07','Implementasi Modul Autentikasi','Halaman login dan middleware role siswa/admin selesai','Session timeout kadang belum ter-destroy sempurna','Pastikan session_destroy dipanggil saat logout','Disetujui','2026-10-19 02:24:08'),(23,23,23,'2026-10-06','Konfigurasi Web Server Apache & Nginx','Virtual host berhasil diuji dan berjalan optimal','Port conflict pada 8080','Ganti port pada berkas konfigurasi httpd.conf','Disetujui','2026-10-19 02:24:08'),(24,24,24,'2026-10-05','Slicing Desain Figma ke HTML5 & Tailwind CSS','Tampilan responsif untuk desktop dan mobile selesai','Breakpoint mobile navigation perlu penyesuaian',NULL,'Menunggu','2026-10-19 02:24:08'),(25,25,25,'2026-10-04','Pembuatan REST API Endpoint Siswa','Endpoint CRUD JSON berhasil diuji via Postman','Format error validation belum seragam','Harap melengkapi hasil dokumentasi foto kegiatan','Revisi','2026-10-19 02:24:08'),(26,1,1,'2026-10-03','Testing dan Debugging Query SQL','Query pencarian dan filter berjalan di bawah 50ms','Slow query pada tabel absensi tanpa index','Tambahkan index pada kolom tanggal dan siswa_id','Disetujui','2026-10-19 02:24:08'),(27,2,2,'2026-10-02','Pemasangan Kabel Jaringan LAN & Crimping RJ-45','10 kabel UTP lolos pengujian LAN tester','Satu konektor pin 3 tidak tembus','Potong ulang dan pastikan urutan T568B presisi','Disetujui','2026-10-19 02:24:08'),(28,3,3,'2026-10-01','Instalasi dan Konfigurasi Mikrotik RouterOS','Bandwidth management queue tree dan hotspot aktif','IP pool sempat overlap','Cek subnet mask pada DHCP Server range','Disetujui','2026-10-19 02:24:08'),(29,4,4,'2026-09-30','Backup dan Replikasi Database MySQL','Automated script crontab mysqldump berjalan harian','Penyimpanan lokal cepat penuh',NULL,'Menunggu','2026-10-19 02:24:08'),(30,5,5,'2026-09-29','Penyusunan Dokumentasi API & Manual Pengguna','Dokumentasi format OpenAPI/Swagger rampung','Belum ada panduan pemula berbahasa Indonesia','Harap melengkapi hasil dokumentasi foto kegiatan','Revisi','2026-10-19 02:24:08'),(31,6,6,'2026-09-28','Membuat Desain Database','ERD berhasil diselesaikan dan dinormalisasi','Kesulitan menentukan relasi kardinalitas many-to-many','Bagus, lanjutkan pembuatan tabel fisik','Disetujui','2026-10-19 02:24:08'),(32,7,7,'2026-09-27','Implementasi Modul Autentikasi','Halaman login dan middleware role siswa/admin selesai','Session timeout kadang belum ter-destroy sempurna','Pastikan session_destroy dipanggil saat logout','Disetujui','2026-10-19 02:24:08'),(33,8,8,'2026-09-26','Konfigurasi Web Server Apache & Nginx','Virtual host berhasil diuji dan berjalan optimal','Port conflict pada 8080','Ganti port pada berkas konfigurasi httpd.conf','Disetujui','2026-10-19 02:24:08'),(34,9,9,'2026-09-25','Slicing Desain Figma ke HTML5 & Tailwind CSS','Tampilan responsif untuk desktop dan mobile selesai','Breakpoint mobile navigation perlu penyesuaian',NULL,'Menunggu','2026-10-19 02:24:08'),(35,10,10,'2026-09-24','Pembuatan REST API Endpoint Siswa','Endpoint CRUD JSON berhasil diuji via Postman','Format error validation belum seragam','Harap melengkapi hasil dokumentasi foto kegiatan','Revisi','2026-10-19 02:24:08');
/*!40000 ALTER TABLE `jurnal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `monitoring`
--

DROP TABLE IF EXISTS `monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `penempatan_id` int(11) NOT NULL,
  `pembimbing_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tanggal_monitoring` date NOT NULL,
  `catatan` text NOT NULL,
  `nilai` int(11) NOT NULL,
  `rekomendasi` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_monitoring_penempatan` (`penempatan_id`),
  KEY `fk_monitoring_pembimbing` (`pembimbing_id`),
  KEY `fk_monitoring_siswa` (`siswa_id`),
  CONSTRAINT `fk_monitoring_pembimbing` FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbing` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_monitoring_penempatan` FOREIGN KEY (`penempatan_id`) REFERENCES `penempatan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_monitoring_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monitoring`
--

LOCK TABLES `monitoring` WRITE;
/*!40000 ALTER TABLE `monitoring` DISABLE KEYS */;
INSERT INTO `monitoring` VALUES (1,1,1,1,'2026-08-15','Siswa mampu beradaptasi cepat dengan tim pengembang industri dan menguasai framework Laravel.',92,'Pertahankan inisiatif dan eksplorasi arsitektur microservices.','2026-10-19 02:24:08'),(2,2,2,2,'2026-08-18','Kedisiplinan jam kerja sangat baik, aktif bertanya saat ada kendala teknis konfigurasi fiber optik.',88,'Tingkatkan kecepatan terminasi kabel ODP.','2026-10-19 02:24:08'),(3,3,3,3,'2026-08-20','Kemampuan desain UI/UX kreatif dan estetis, prototype dapat dipahami oleh klien.',95,'Disarankan mengikuti sertifikasi desain standar industri.','2026-10-19 02:24:08'),(4,4,4,4,'2026-08-25','Paham konsep keamanan jaringan dan firewall rule, troubleshooting dilakukan secara sistematis.',87,'Latih dokumentasi topologi jaringan dengan lebih rapi.','2026-10-19 02:24:08'),(5,5,5,5,'2026-09-02','Menunjukkan kemajuan signifikan dalam pengkodean frontend React Native.',85,'Perlu mendalami state management seperti Redux atau Zustand.','2026-10-19 02:24:08'),(6,6,6,6,'2026-09-05','Komunikasi dengan mentor industri sangat kooperatif, hasil query database MySQL terstruktur.',89,'Lanjutkan belajar index tuning dan stored procedure.','2026-10-19 02:24:08'),(7,7,7,7,'2026-09-10','Kehadiran perlu ditingkatkan, ada beberapa hari izin. Namun pengerjaan tugas selesai.',74,'Wajib memperhatikan komitmen absensi dan jam masuk kantor.','2026-10-19 02:24:08'),(8,8,8,8,'2026-09-15','Tingkat kehadiran menurun, sering terlambat. Perlu pembimbingan intensif dari guru dan sekolah.',68,'Jadwalkan konseling dengan guru BK dan orang tua siswa.','2026-10-19 02:24:08'),(9,9,9,9,'2026-09-20','Siswa kurang aktif dan sering tidak masuk tanpa kabar (alpa). Performa di industri rendah.',55,'Surat peringatan pertama (SP1) dan panggilan orang tua ke sekolah.','2026-10-19 02:24:08'),(10,10,10,10,'2026-09-22','Kehadiran sangat rendah (<60%). Mentor industri menyampaikan keluhan disiplin.',50,'Evaluasi penempatan dan pembinaan langsung oleh waka humas/hubin.','2026-10-19 02:24:08'),(11,11,1,11,'2026-09-25','Penguasaan hardware server dan cabling sangat rapi, proaktif dalam tugas lapangan.',90,'Lanjutkan ke konfigurasi virtualization Proxmox.','2026-10-19 02:24:08'),(12,12,2,12,'2026-09-28','Dapat mengonfigurasi router Cisco dengan baik, etos kerja profesional.',86,'Pelajari routing protocol BGP dan OSPF lebih mendalam.','2026-10-19 02:24:08'),(13,13,3,13,'2026-10-02','Mampu membuat mock up web landing page e-commerce dengan interaksi interaktif.',91,'Eksplorasi animasi micro-interactions dengan Tailwind.','2026-10-19 02:24:08'),(14,14,4,14,'2026-10-05','Menguasai instalasi Linux Debian Server untuk kebutuhan web hosting internal.',84,'Pelajari otomatisasi server dengan Ansible script.','2026-10-19 02:24:08'),(15,15,5,15,'2026-10-10','Mampu berkolaborasi git branch dengan tim industri tanpa ada conflict merge.',93,'Sangat siap untuk langsung disalurkan kerja setelah lulus.','2026-10-19 02:24:08');
/*!40000 ALTER TABLE `monitoring` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pembimbing`
--

DROP TABLE IF EXISTS `pembimbing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembimbing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `nip` varchar(30) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(25) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  KEY `fk_pembimbing_user` (`user_id`),
  CONSTRAINT `fk_pembimbing_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembimbing`
--

LOCK TABLES `pembimbing` WRITE;
/*!40000 ALTER TABLE `pembimbing` DISABLE KEYS */;
INSERT INTO `pembimbing` VALUES (1,2,'198501152010011001','Drs. Budi Santoso, M.Kom','081234567801','budi.santoso@smk.sch.id','2026-10-19 02:24:07'),(2,3,'198703222012012002','Siti Rahmawati, S.Pd','081234567802','siti.rahma@smk.sch.id','2026-10-19 02:24:07'),(3,4,'198207102008011003','Ahmad Fauzi, M.T','081234567803','ahmad.fauzi@smk.sch.id','2026-10-19 02:24:07'),(4,5,'199011052015032004','Dewi Lestari, S.Kom','081234567804','dewi.lestari@smk.sch.id','2026-10-19 02:24:07'),(5,6,'198404182009021005','Hendra Gunawan, S.Kom','081234567805','hendra.g@smk.sch.id','2026-10-19 02:24:07'),(6,7,'198909122014022006','Rina Anggraini, M.Kom','081234567806','rina.ang@smk.sch.id','2026-10-19 02:24:07'),(7,8,'198602252010031007','Eko Prasetyo, S.Pd','081234567807','eko.prasetyo@smk.sch.id','2026-10-19 02:24:07'),(8,9,'199208302018012008','Nurul Hidayah, S.Kom','081234567808','nurul.h@smk.sch.id','2026-10-19 02:24:07'),(9,10,'198305142008031009','Agus Wibowo, M.Cs','081234567809','agus.wibowo@smk.sch.id','2026-10-19 02:24:07'),(10,11,'198812202013022010','Fitri Handayani, S.Pd','081234567810','fitri.h@smk.sch.id','2026-10-19 02:24:07');
/*!40000 ALTER TABLE `pembimbing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penempatan`
--

DROP TABLE IF EXISTS `penempatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `penempatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siswa_id` int(11) NOT NULL,
  `perusahaan_id` int(11) NOT NULL,
  `pembimbing_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('Belum Mulai','Aktif','Selesai') NOT NULL DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_penempatan_siswa` (`siswa_id`),
  KEY `fk_penempatan_perusahaan` (`perusahaan_id`),
  KEY `fk_penempatan_pembimbing` (`pembimbing_id`),
  CONSTRAINT `fk_penempatan_pembimbing` FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbing` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_penempatan_perusahaan` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_penempatan_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penempatan`
--

LOCK TABLES `penempatan` WRITE;
/*!40000 ALTER TABLE `penempatan` DISABLE KEYS */;
INSERT INTO `penempatan` VALUES (1,1,1,1,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(2,2,2,2,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(3,3,3,3,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(4,4,4,4,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(5,5,5,5,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(6,6,6,6,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(7,7,7,7,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(8,8,8,8,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(9,9,9,9,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(10,10,10,10,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(11,11,1,1,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(12,12,2,2,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(13,13,3,3,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(14,14,4,4,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(15,15,5,5,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(16,16,6,6,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(17,17,7,7,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(18,18,8,8,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(19,19,9,9,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(20,20,10,10,'2026-07-01','2026-12-31','Aktif','2026-10-19 02:24:08'),(21,21,1,1,'2026-01-05','2026-06-30','Selesai','2026-10-19 02:24:08'),(22,22,2,2,'2026-01-05','2026-06-30','Selesai','2026-10-19 02:24:08'),(23,23,3,3,'2026-01-05','2026-06-30','Selesai','2026-10-19 02:24:08'),(24,24,4,4,'2026-11-01','2027-04-30','Belum Mulai','2026-10-19 02:24:08'),(25,25,5,5,'2026-11-01','2027-04-30','Belum Mulai','2026-10-19 02:24:08');
/*!40000 ALTER TABLE `penempatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perusahaan`
--

DROP TABLE IF EXISTS `perusahaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perusahaan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_perusahaan` varchar(120) NOT NULL,
  `bidang_usaha` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `kontak` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `penanggung_jawab` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perusahaan`
--

LOCK TABLES `perusahaan` WRITE;
/*!40000 ALTER TABLE `perusahaan` DISABLE KEYS */;
INSERT INTO `perusahaan` VALUES (1,'PT Digital Solusindo Nusantara','Software Development & IT Solution','Jl. Sudirman Kav. 24, Jakarta Pusat','021-5551234','info@digitalsolusindo.co.id','Bambang Triyono','2026-10-19 02:24:07'),(2,'PT Telkom Akses Regional','Telekomunikasi & Jaringan','Jl. Pajajaran No. 88, Bogor','0251-8334455','pkl@telkomakses.id','Rudi Hermawan','2026-10-19 02:24:07'),(3,'CV Kreasi Visual Studio','UI/UX & Multimedia Production','Jl. Gajah Mada No. 12, Bandung','022-7201928','halo@kreasivisual.com','Nadia Amanda','2026-10-19 02:24:07'),(4,'PT Prima Cyber Teknologi','Network Infrastructure & Cyber Security','Jl. Pemuda No. 45, Semarang','024-8451122','hrd@primacyber.com','Hadi Kusuma','2026-10-19 02:24:07'),(5,'PT Inovasi Media Kreatif','Web & Mobile App Development','Jl. Malioboro No. 102, Yogyakarta','0274-561234','career@inovasimedia.co.id','Dian Permana','2026-10-19 02:24:07'),(6,'PT Surya Sistem Integrator','ERP & Database Management','Jl. Basuki Rahmat No. 70, Surabaya','031-5349988','contact@suryasistem.com','Agung Wicaksono','2026-10-19 02:24:07'),(7,'PT Nexus Artificial Intelligence','AI & Data Science','Jl. Rasuna Said Blok X, Jakarta Selatan','021-5290123','admin@nexusai.co.id','Maya Susanti','2026-10-19 02:24:07'),(8,'CV Mitra Informatika Jaya','Computer Hardware & Networking','Jl. Gatot Subroto No. 33, Malang','0341-456789','mitrajaya@gmail.com','Surya Saputra','2026-10-19 02:24:07'),(9,'PT Quantum Cloud Solution','Cloud Computing & DevOps','Jl. Diponegoro No. 15, Denpasar','0361-234567','cloud@quantum.co.id','Wayan Artha','2026-10-19 02:24:07'),(10,'PT Global Solusi Webindo','E-Commerce & Digital Marketing','Jl. Ahmad Yani No. 90, Surakarta','0271-712345','info@solusiwebindo.com','Lestari Indah','2026-10-19 02:24:07');
/*!40000 ALTER TABLE `perusahaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `siswa`
--

DROP TABLE IF EXISTS `siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `nis` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(30) NOT NULL,
  `jurusan` varchar(60) NOT NULL,
  `no_hp` varchar(25) NOT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nis` (`nis`),
  KEY `fk_siswa_user` (`user_id`),
  CONSTRAINT `fk_siswa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `siswa`
--

LOCK TABLES `siswa` WRITE;
/*!40000 ALTER TABLE `siswa` DISABLE KEYS */;
INSERT INTO `siswa` VALUES (1,12,'102401','Aditya Pratama','XII RPL 1','Rekayasa Perangkat Lunak','082111110001','Jl. Cempaka No. 1, Semarang','2026-10-19 02:24:07'),(2,13,'102402','Bagus Wicaksono','XII RPL 1','Rekayasa Perangkat Lunak','082111110002','Jl. Melati No. 2, Semarang','2026-10-19 02:24:07'),(3,14,'102403','Citra Kirana','XII RPL 1','Rekayasa Perangkat Lunak','082111110003','Jl. Mawar No. 3, Semarang','2026-10-19 02:24:08'),(4,15,'102404','Dimas Anggara','XII RPL 1','Rekayasa Perangkat Lunak','082111110004','Jl. Anggrek No. 4, Semarang','2026-10-19 02:24:08'),(5,16,'102405','Eka Putri Rahayu','XII RPL 1','Rekayasa Perangkat Lunak','082111110005','Jl. Kenanga No. 5, Semarang','2026-10-19 02:24:08'),(6,17,'102406','Fajar Ramadhan','XII RPL 2','Rekayasa Perangkat Lunak','082111110006','Jl. Dahlia No. 6, Semarang','2026-10-19 02:24:08'),(7,18,'102407','Gilang Dirga','XII RPL 2','Rekayasa Perangkat Lunak','082111110007','Jl. Flamboyan No. 7, Semarang','2026-10-19 02:24:08'),(8,19,'102408','Hana Safitri','XII RPL 2','Rekayasa Perangkat Lunak','082111110008','Jl. Teratai No. 8, Semarang','2026-10-19 02:24:08'),(9,20,'102409','Indra Setiawan','XII RPL 2','Rekayasa Perangkat Lunak','082111110009','Jl. Kamboja No. 9, Semarang','2026-10-19 02:24:08'),(10,21,'102410','Joko Susilo','XII RPL 2','Rekayasa Perangkat Lunak','082111110010','Jl. Bougenville No. 10, Semarang','2026-10-19 02:24:08'),(11,22,'102411','Kartika Sari','XII TKJ 1','Teknik Komputer dan Jaringan','082111110011','Jl. Pandan No. 11, Semarang','2026-10-19 02:24:08'),(12,23,'102412','Lukman Hakim','XII TKJ 1','Teknik Komputer dan Jaringan','082111110012','Jl. Beringin No. 12, Semarang','2026-10-19 02:24:08'),(13,24,'102413','Mega Utami','XII TKJ 1','Teknik Komputer dan Jaringan','082111110013','Jl. Asoka No. 13, Semarang','2026-10-19 02:24:08'),(14,25,'102414','Naufal Rizky','XII TKJ 1','Teknik Komputer dan Jaringan','082111110014','Jl. Pinus No. 14, Semarang','2026-10-19 02:24:08'),(15,26,'102415','Olivia Zalianty','XII TKJ 1','Teknik Komputer dan Jaringan','082111110015','Jl. Cendana No. 15, Semarang','2026-10-19 02:24:08'),(16,27,'102416','Panji Gumilang','XII TKJ 2','Teknik Komputer dan Jaringan','082111110016','Jl. Merak No. 16, Semarang','2026-10-19 02:24:08'),(17,28,'102417','Qori Sandioriva','XII TKJ 2','Teknik Komputer dan Jaringan','082111110017','Jl. Rajawali No. 17, Semarang','2026-10-19 02:24:08'),(18,29,'102418','Rian Hidayat','XII TKJ 2','Teknik Komputer dan Jaringan','082111110018','Jl. Garuda No. 18, Semarang','2026-10-19 02:24:08'),(19,30,'102419','Salma Salsabila','XII TKJ 2','Teknik Komputer dan Jaringan','082111110019','Jl. Elang No. 19, Semarang','2026-10-19 02:24:08'),(20,31,'102420','Taufik Hidayat','XII TKJ 2','Teknik Komputer dan Jaringan','082111110020','Jl. Kasuari No. 20, Semarang','2026-10-19 02:24:08'),(21,32,'102421','Umar Wirahadi','XII SIJA 1','Sistem Informatika Jaringan dan Aplikasi','082111110021','Jl. Pahlawan No. 21, Semarang','2026-10-19 02:24:08'),(22,33,'102422','Vina Panduwinata','XII SIJA 1','Sistem Informatika Jaringan dan Aplikasi','082111110022','Jl. Veteran No. 22, Semarang','2026-10-19 02:24:08'),(23,34,'102423','Wahyu Hidayat','XII SIJA 1','Sistem Informatika Jaringan dan Aplikasi','082111110023','Jl. Diponegoro No. 23, Semarang','2026-10-19 02:24:08'),(24,35,'102424','Yoga Pratama','XII SIJA 1','Sistem Informatika Jaringan dan Aplikasi','082111110024','Jl. Gajah Mada No. 24, Semarang','2026-10-19 02:24:08'),(25,36,'102425','Zahra Amalia','XII SIJA 1','Sistem Informatika Jaringan dan Aplikasi','082111110025','Jl. Pemuda No. 25, Semarang','2026-10-19 02:24:08');
/*!40000 ALTER TABLE `siswa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','pembimbing','siswa') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$8Vbrrod8DDtU2qc.1EoUx.3VmHWuGd1AeVjZbGdiqM.yc33YzUFx2','Administrator InternTrack','admin','2026-10-19 02:24:07'),(2,'pembimbing1','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Drs. Budi Santoso, M.Kom','pembimbing','2026-10-19 02:24:07'),(3,'pembimbing2','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Siti Rahmawati, S.Pd','pembimbing','2026-10-19 02:24:07'),(4,'pembimbing3','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Ahmad Fauzi, M.T','pembimbing','2026-10-19 02:24:07'),(5,'pembimbing4','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Dewi Lestari, S.Kom','pembimbing','2026-10-19 02:24:07'),(6,'pembimbing5','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Hendra Gunawan, S.Kom','pembimbing','2026-10-19 02:24:07'),(7,'pembimbing6','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Rina Anggraini, M.Kom','pembimbing','2026-10-19 02:24:07'),(8,'pembimbing7','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Eko Prasetyo, S.Pd','pembimbing','2026-10-19 02:24:07'),(9,'pembimbing8','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Nurul Hidayah, S.Kom','pembimbing','2026-10-19 02:24:07'),(10,'pembimbing9','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Agus Wibowo, M.Cs','pembimbing','2026-10-19 02:24:07'),(11,'pembimbing10','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Fitri Handayani, S.Pd','pembimbing','2026-10-19 02:24:07'),(12,'siswa1','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Aditya Pratama','siswa','2026-10-19 02:24:07'),(13,'siswa2','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Bagus Wicaksono','siswa','2026-10-19 02:24:07'),(14,'siswa3','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Citra Kirana','siswa','2026-10-19 02:24:08'),(15,'siswa4','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Dimas Anggara','siswa','2026-10-19 02:24:08'),(16,'siswa5','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Eka Putri Rahayu','siswa','2026-10-19 02:24:08'),(17,'siswa6','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Fajar Ramadhan','siswa','2026-10-19 02:24:08'),(18,'siswa7','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Gilang Dirga','siswa','2026-10-19 02:24:08'),(19,'siswa8','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Hana Safitri','siswa','2026-10-19 02:24:08'),(20,'siswa9','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Indra Setiawan','siswa','2026-10-19 02:24:08'),(21,'siswa10','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Joko Susilo','siswa','2026-10-19 02:24:08'),(22,'siswa11','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Kartika Sari','siswa','2026-10-19 02:24:08'),(23,'siswa12','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Lukman Hakim','siswa','2026-10-19 02:24:08'),(24,'siswa13','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Mega Utami','siswa','2026-10-19 02:24:08'),(25,'siswa14','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Naufal Rizky','siswa','2026-10-19 02:24:08'),(26,'siswa15','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Olivia Zalianty','siswa','2026-10-19 02:24:08'),(27,'siswa16','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Panji Gumilang','siswa','2026-10-19 02:24:08'),(28,'siswa17','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Qori Sandioriva','siswa','2026-10-19 02:24:08'),(29,'siswa18','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Rian Hidayat','siswa','2026-10-19 02:24:08'),(30,'siswa19','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Salma Salsabila','siswa','2026-10-19 02:24:08'),(31,'siswa20','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Taufik Hidayat','siswa','2026-10-19 02:24:08'),(32,'siswa21','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Umar Wirahadi','siswa','2026-10-19 02:24:08'),(33,'siswa22','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Vina Panduwinata','siswa','2026-10-19 02:24:08'),(34,'siswa23','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Wahyu Hidayat','siswa','2026-10-19 02:24:08'),(35,'siswa24','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Yoga Pratama','siswa','2026-10-19 02:24:08'),(36,'siswa25','$2y$10$3Ts2g6eU2O83NH3w/AMDfeiYntHRiy/OMdDypEyfdrPXlCry.tZCW','Zahra Amalia','siswa','2026-10-19 02:24:08');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-19  9:24:27
