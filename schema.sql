-- --------------------------------------------------------
-- 主机                            :localhost
-- Server version                :5.5.16 - MySQL Community Server (GPL)
-- Server OS                     :Win32
-- HeidiSQL 版本                   :7.0.0.4246
-- Created                       :2013-05-17 16:32:34
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table laiyitui_com.comment
DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `picture_id` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `deleted_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `picture_id` (`picture_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table laiyitui_com.comment: 0 rows
DELETE FROM `comment`;
/*!40000 ALTER TABLE `comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `comment` ENABLE KEYS */;


-- Dumping structure for table laiyitui_com.picture
DROP TABLE IF EXISTS `picture`;
CREATE TABLE IF NOT EXISTS `picture` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `picture` varchar(255) NOT NULL,
  `viewed` int(10) unsigned NOT NULL,
  `stared` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `deleted_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `viewed` (`viewed`),
  KEY `stared` (`stared`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- Dumping data for table laiyitui_com.picture: 0 rows
DELETE FROM `picture`;
/*!40000 ALTER TABLE `picture` DISABLE KEYS */;
/*!40000 ALTER TABLE `picture` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
