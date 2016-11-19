-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `web_semestralka`;
CREATE DATABASE `web_semestralka` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci */;
USE `web_semestralka`;

DROP TABLE IF EXISTS `prispevky`;
CREATE TABLE `prispevky` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nazev` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `obsah` text COLLATE utf8_unicode_ci NOT NULL,
  `cas_vytvoreni` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stav` enum('recenzován','přijat','odmítnut') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'recenzován',
  `hodnoceni` float DEFAULT NULL,
  `priloha_url` varchar(2083) COLLATE utf8_unicode_ci NOT NULL,
  `uzivatele_nick` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`uzivatele_nick`),
  KEY `fk_prispevky_uzivatele1_idx` (`uzivatele_nick`),
  CONSTRAINT `fk_prispevky_uzivatele1` FOREIGN KEY (`uzivatele_nick`) REFERENCES `uzivatele` (`nick`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `prispevky` (`id`, `nazev`, `obsah`, `cas_vytvoreni`, `stav`, `hodnoceni`, `priloha_url`, `uzivatele_nick`) VALUES
(24,	'Připomenutí konání konference',	'Vážení a milí přátelé,\r\n\r\ndovolte mi, abych Vás touto cestou pozvat na letošní ročník vědecké konference, který se bude konat 4. - 8. 3. 2017 v Plzni. V nejbližších dnech se na našem webu budete moci dočíst bližší informace k programu a místu konání.\r\n\r\nTěším se na setkání!',	'2016-11-18 16:17:58',	'přijat',	1.25,	'',	'lukky'),
(25,	'Program konference',	'Program pro první den konference bude následující:<br />\r\n7:00 zahájení <br />\r\n8:00 snídaně a káva<br />\r\n9:00 prezentace <br />\r\n11:00 společný oběd<br />\r\n12:15 diskuse a závěr',	'2016-11-18 16:47:12',	'přijat',	1.58333,	'',	'lukky'),
(26,	'Doplňující informace',	'Dobrý den,<br />\r\npro ty kteří si chtějí přečíst podrobnější informace ohledně místa konání, programu a dalších věcí. Připravili jsme pro Vás soubor plný doplňujících informací.<br /><strong style=\"color:#FF0000;\">Viz příloha</strong>',	'2016-11-18 17:05:13',	'recenzován',	NULL,	'C:\\Users\\nouzo\\www\\FAV_WEB\\semestralka\\public\\prilohy\\prazdny.pdf',	'jirsak');

DROP TABLE IF EXISTS `recenze`;
CREATE TABLE `recenze` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uzivatele_nick` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `prispevky_id` int(11) NOT NULL,
  `vzhled` int(11) DEFAULT NULL,
  `srozumitelnost` int(11) DEFAULT NULL,
  `pravopis` int(11) DEFAULT NULL,
  `rozsah` int(11) DEFAULT NULL,
  `mezisoucet` float DEFAULT NULL,
  `cas_hodnoceni` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`uzivatele_nick`,`prispevky_id`),
  UNIQUE KEY `id` (`id`),
  KEY `fk_recenze_uzivatele1_idx` (`uzivatele_nick`),
  KEY `fk_recenze_prispevky1_idx` (`prispevky_id`),
  CONSTRAINT `fk_recenze_prispevky1` FOREIGN KEY (`prispevky_id`) REFERENCES `prispevky` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_recenze_uzivatele1` FOREIGN KEY (`uzivatele_nick`) REFERENCES `uzivatele` (`nick`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `recenze` (`id`, `uzivatele_nick`, `prispevky_id`, `vzhled`, `srozumitelnost`, `pravopis`, `rozsah`, `mezisoucet`, `cas_hodnoceni`) VALUES
(17,	'filda',	24,	1,	1,	1,	1,	1,	'2016-11-18 16:33:19'),
(20,	'filda',	25,	3,	1,	1,	1,	1.5,	'2016-11-18 16:54:42'),
(23,	'filda',	26,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL),
(18,	'hellby',	24,	1,	1,	1,	1,	1,	'2016-11-18 16:31:09'),
(21,	'hellby',	25,	3,	2,	1,	1,	1.75,	'2016-11-18 16:53:49'),
(19,	'peta',	24,	3,	1,	1,	2,	1.75,	'2016-11-18 16:33:48'),
(22,	'peta',	25,	2,	1,	1,	2,	1.5,	'2016-11-18 16:55:23');

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nazev` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `role` (`id`, `nazev`) VALUES
(1,	'administrátor'),
(2,	'recenzent'),
(3,	'autor'),
(4,	'Zablokovaný');

DROP TABLE IF EXISTS `uzivatele`;
CREATE TABLE `uzivatele` (
  `nick` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `heslo` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `jmeno` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `prijmeni` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT '3',
  PRIMARY KEY (`nick`),
  KEY `fk_uzivatele_role_idx` (`role_id`),
  CONSTRAINT `fk_uzivatele_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `uzivatele` (`nick`, `heslo`, `jmeno`, `prijmeni`, `email`, `role_id`) VALUES
('filda',	'7c222fb2927d828af22f592134e8932480637c0d',	'Filip',	'Toufar',	'filda23@gmail.com',	2),
('hellby',	'7c222fb2927d828af22f592134e8932480637c0d',	'Luboš',	'Helcelet',	'helcelet@seznam.cz',	2),
('jirsak',	'7c222fb2927d828af22f592134e8932480637c0d',	'Jan',	'Jirsák',	'jan.jirsak@seznam.cz',	3),
('lukky',	'7c222fb2927d828af22f592134e8932480637c0d',	'Lukáš',	'Šrédr',	'sreder.lukas@email.cz',	3),
('matmar',	'6c5da3cb4c1105a3ade3fff010772cf79435a59a',	'Martin',	'Matas',	'nouzovekonto@gmail.com',	1),
('peta',	'7c222fb2927d828af22f592134e8932480637c0d',	'Petr',	'Svoboda',	'svoboda.petr@yahoo.com',	2);

-- 2016-11-19 13:38:52
