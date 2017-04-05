/*
SQLyog Community v11.22 (64 bit)
MySQL - 5.5.29-0ubuntu0.12.04.1 : Database - bntdev_2
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`bntdev_2` /*!40100 DEFAULT CHARACTER SET latin1 */;

/*Table structure for table `net_location_postcode` */

DROP TABLE IF EXISTS `net_location_postcode`;

CREATE TABLE `net_location_postcode` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `countrycode` varchar(2) NOT NULL DEFAULT '',
  `postcode` varchar(5) NOT NULL DEFAULT '',
  `suburb` varchar(48) NOT NULL DEFAULT '',
  `state` varchar(48) NOT NULL DEFAULT '',
  `statecode` varchar(48) NOT NULL DEFAULT '',
  `city` varchar(48) NOT NULL DEFAULT '',
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `fulltextcode` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `postcode` (`countrycode`,`postcode`),
  KEY `suburb` (`countrycode`,`suburb`(7)),
  FULLTEXT KEY `fulltextcode` (`fulltextcode`)
) ENGINE=MyISAM AUTO_INCREMENT=17847 DEFAULT CHARSET=utf8;

/*Data for the table `net_location_postcode` */


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;