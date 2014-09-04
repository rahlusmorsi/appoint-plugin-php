SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `uid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `start` timestamp NOT NULL,
  `duration` int(11) DEFAULT '0',
  `notes` text NOT NULL,
  `private` text NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `uid` (`uid`)
) TYPE=InnoDB  COMMENT='Store Appointments' AUTO_INCREMENT=18864 ;

CREATE TABLE IF NOT EXISTS `appointment_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `duration` int(11) NOT NULL DEFAULT '0',
  `restricted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) TYPE=InnoDB  COMMENT='appointment types' AUTO_INCREMENT=49 ;

CREATE TABLE IF NOT EXISTS `professionals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `un` varchar(60) NOT NULL DEFAULT '',
  `pw` varchar(200) NOT NULL DEFAULT '',
  `label` varchar(100) NOT NULL DEFAULT '',
  `name` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(64) NOT NULL DEFAULT '',
  `phone` varchar(32) NOT NULL DEFAULT '',
  `theme` varchar(32) NOT NULL DEFAULT 'default',
  `appoint_email` varchar(32) NOT NULL DEFAULT '',
  `appoint_sms` varchar(32) NOT NULL DEFAULT '',
  `appoint_phone` varchar(200) NOT NULL DEFAULT '',
  `password_required` tinyint(1) NOT NULL DEFAULT '0',
  `day_start` int(11) NOT NULL DEFAULT '9',
  `day_end` int(11) NOT NULL DEFAULT '17',
  `slot_type` int(11) NOT NULL DEFAULT '2',
  `default_mode` enum('monthly','weekly') NOT NULL DEFAULT 'monthly',
  `basic_setup` smallint(1) NOT NULL DEFAULT '0',
  `planner_setup` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) TYPE=InnoDB  COMMENT='Store all a Professional''s Details.' AUTO_INCREMENT=8 ;

CREATE TABLE IF NOT EXISTS `time_templates` (
  `slot_id` varchar(10) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT '0',
  `day` int(11) NOT NULL DEFAULT '0',
  `hour` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`slot_id`),
  KEY `pid` (`pid`),
  KEY `day` (`day`),
  KEY `hour` (`hour`)
) TYPE=InnoDB COMMENT='store time templates';

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0',
  `email` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `name` varchar(64) NOT NULL DEFAULT '',
  `phone` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) TYPE=InnoDB  AUTO_INCREMENT=2844 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
