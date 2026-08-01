-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 01 Ağu 2026, 20:11:17
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `student_management_system`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `admin`
--

CREATE TABLE `admin` (
  `unvan` varchar(20) NOT NULL,
  `ad` varchar(30) NOT NULL,
  `soyad` varchar(30) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `kullaniciAdi` varchar(25) DEFAULT NULL,
  `bolum` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `admin`
--

INSERT INTO `admin` (`unvan`, `ad`, `soyad`, `sifre`, `kullaniciAdi`, `bolum`) VALUES
('akademisyen', 'Emre ', 'Kansu', '852', 'emrekansu', 'Fizik'),
('admin', 'Süleyman', 'Kızıltoprak', '$2y$10$DGnDaX8Qs00OKDffZ/KgieSoE59Am66lCRUhXCHgDYXFipNCZ8LS.', 'suleymankiziltoprak', 'Rektör'),
('akademisyen', 'Ceren', 'Çelik', '$2y$10$OZTZkZEwv2Sz0UlbCHNcquYXgOZ9rrFy1hPZpi3tgwSO6KUH/k2hW', 'cerencelik', 'Ayrık Matematik'),
('akademisyen', 'Kuzey', 'Terzi', '965', 'kuzeyterzi', 'Bilgisayar Mimarisi');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `bolum`
--

CREATE TABLE `bolum` (
  `bölüm` varchar(100) DEFAULT NULL,
  `fakülte` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `bolum`
--

INSERT INTO `bolum` (`bölüm`, `fakülte`) VALUES
('Bilgisayar Mühendisliği', 'Mühendislik Fakültesi'),
('Endüstri Mühendisliği', 'Mühendislik Fakültesi'),
('Matematik', 'Fen Fakültesi'),
('Ekonomi', 'İktisadi ve İdari Bilimler Fakültesi');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ders`
--

CREATE TABLE `ders` (
  `dersKodu` varchar(20) NOT NULL,
  `dersAdi` varchar(100) NOT NULL,
  `bolum` varchar(100) NOT NULL,
  `akademisyen` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ders`
--

INSERT INTO `ders` (`dersKodu`, `dersAdi`, `bolum`, `akademisyen`) VALUES
('FIZ101', 'Fizik I', 'Endüstri Mühendisliği', 'kuzeyterzi'),
('KIM101', 'Kimya I', 'Endüstri Mühendisliği', 'cerencelik'),
('TRH101', 'Atatürk İlkeleri', 'Bilgisayar Mühendisliği', 'emrekansu');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `duyuru`
--

CREATE TABLE `duyuru` (
  `duyuruId` int(11) NOT NULL,
  `baslik` varchar(150) NOT NULL,
  `icerik` text NOT NULL,
  `duyuruTuru` enum('genel','ders') NOT NULL DEFAULT 'genel',
  `hedefKitle` enum('ogrenci','akademisyen','herkes') NOT NULL DEFAULT 'ogrenci',
  `dersKodu` varchar(20) DEFAULT NULL,
  `yayinlayan` varchar(25) NOT NULL,
  `yayinlayanRol` enum('admin','akademisyen') NOT NULL,
  `olusturmaTarihi` datetime NOT NULL DEFAULT current_timestamp(),
  `guncellemeTarihi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `duyuru`
--

INSERT INTO `duyuru` (`duyuruId`, `baslik`, `icerik`, `duyuruTuru`, `hedefKitle`, `dersKodu`, `yayinlayan`, `yayinlayanRol`, `olusturmaTarihi`, `guncellemeTarihi`) VALUES
(1, 'Sistem Duyurusu', 'Öğrenci bilgi sistemi hafta sonu bakım çalışması nedeniyle kısa süreliğine kullanılamayacaktır.', 'genel', 'ogrenci', NULL, 'suleymankiziltoprak', 'admin', '2026-07-25 15:27:16', NULL),
(2, 'Ders Saati Değişikliği', 'TRH101 dersi bu hafta saat 13.00 yerine 14.00 tarihinde yapılacaktır.', 'ders', 'ogrenci', 'TRH101', 'emrekansu', 'akademisyen', '2026-07-25 15:27:25', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `duyuru_okunma`
--

CREATE TABLE `duyuru_okunma` (
  `id` int(11) NOT NULL,
  `duyuruId` int(11) NOT NULL,
  `kullaniciAdi` varchar(100) NOT NULL,
  `kullaniciRol` enum('ogrenci','akademisyen') NOT NULL,
  `okunmaTarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `fakulte`
--

CREATE TABLE `fakulte` (
  `fakülte` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `fakulte`
--

INSERT INTO `fakulte` (`fakülte`) VALUES
('Mühendislik'),
('Fen Edebiyat');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notlar`
--

CREATE TABLE `notlar` (
  `ogrenciNo` varchar(50) NOT NULL,
  `dersKodu` varchar(20) NOT NULL,
  `vize` decimal(5,2) DEFAULT NULL,
  `final` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `notlar`
--

INSERT INTO `notlar` (`ogrenciNo`, `dersKodu`, `vize`, `final`) VALUES
('23456', 'KIM101', 85.00, 40.00);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ogrenci`
--

CREATE TABLE `ogrenci` (
  `ogrenciNo` int(5) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `ad` varchar(30) DEFAULT NULL,
  `soyad` varchar(30) DEFAULT NULL,
  `bolum` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ogrenci`
--

INSERT INTO `ogrenci` (`ogrenciNo`, `sifre`, `ad`, `soyad`, `bolum`) VALUES
(12345, '$2y$10$yH1kTTzoDOldis7OAt.S/esAefX8p7q0yA3zLf6qQT7afTihz9rme', 'Ervanur ', 'Yıldız', 'Bilgisayar Mühendisliği'),
(23456, '$2y$10$.YE1valup1DumZRpjxgITOejcq3FUKKvD7CHL.GO509p0YRsX8ST2', 'Elif', 'Çakar', 'Endüstri Mühendisliği'),
(96385, '852', 'Kerem', 'Sayer', 'Mekatronik Mühendisliği'),
(98765, '987659', 'Zeynep', 'Yıldırım', 'Makine Mühendisliği');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ogrenci_ders`
--

CREATE TABLE `ogrenci_ders` (
  `ogrenciNo` varchar(50) NOT NULL,
  `dersKodu` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `ogrenci_ders`
--

INSERT INTO `ogrenci_ders` (`ogrenciNo`, `dersKodu`) VALUES
('12345', 'TRH101'),
('23456', 'KIM101'),
('96385', 'FIZ101');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `admin`
--
ALTER TABLE `admin`
  ADD UNIQUE KEY `kullaniciAdi` (`kullaniciAdi`);

--
-- Tablo için indeksler `ders`
--
ALTER TABLE `ders`
  ADD PRIMARY KEY (`dersKodu`);

--
-- Tablo için indeksler `duyuru`
--
ALTER TABLE `duyuru`
  ADD PRIMARY KEY (`duyuruId`),
  ADD KEY `fk_duyuru_ders` (`dersKodu`),
  ADD KEY `fk_duyuru_yayinlayan` (`yayinlayan`);

--
-- Tablo için indeksler `duyuru_okunma`
--
ALTER TABLE `duyuru_okunma`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_okunma` (`duyuruId`,`kullaniciAdi`,`kullaniciRol`);

--
-- Tablo için indeksler `notlar`
--
ALTER TABLE `notlar`
  ADD PRIMARY KEY (`ogrenciNo`,`dersKodu`);

--
-- Tablo için indeksler `ogrenci`
--
ALTER TABLE `ogrenci`
  ADD PRIMARY KEY (`ogrenciNo`);

--
-- Tablo için indeksler `ogrenci_ders`
--
ALTER TABLE `ogrenci_ders`
  ADD PRIMARY KEY (`ogrenciNo`,`dersKodu`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `duyuru`
--
ALTER TABLE `duyuru`
  MODIFY `duyuruId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `duyuru_okunma`
--
ALTER TABLE `duyuru_okunma`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `duyuru`
--
ALTER TABLE `duyuru`
  ADD CONSTRAINT `fk_duyuru_ders` FOREIGN KEY (`dersKodu`) REFERENCES `ders` (`dersKodu`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_duyuru_yayinlayan` FOREIGN KEY (`yayinlayan`) REFERENCES `admin` (`kullaniciAdi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `duyuru_okunma`
--
ALTER TABLE `duyuru_okunma`
  ADD CONSTRAINT `fk_duyuru_okunma_duyuru` FOREIGN KEY (`duyuruId`) REFERENCES `duyuru` (`duyuruId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
