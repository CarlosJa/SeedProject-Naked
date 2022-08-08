/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 10.3.35-MariaDB : Database - ochenta80_db123
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`ochenta80_db123` /*!40100 DEFAULT CHARACTER SET latin1 */;

/*Table structure for table `api_auth` */

DROP TABLE IF EXISTS `api_auth`;

CREATE TABLE `api_auth` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `userid` int(10) DEFAULT NULL,
  `apikey` varchar(255) DEFAULT NULL,
  `planid` int(10) DEFAULT NULL,
  `premium` int(10) DEFAULT NULL,
  `requests` int(10) DEFAULT 1,
  `active` int(10) DEFAULT 1,
  `created_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `UniqueAPI` (`apikey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `api_auth` */

/*Table structure for table `api_plans` */

DROP TABLE IF EXISTS `api_plans`;

CREATE TABLE `api_plans` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT '0',
  `productcode` varchar(255) DEFAULT NULL,
  `productcodeyear` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `html` text DEFAULT NULL,
  `monthly` decimal(5,2) DEFAULT 0.00,
  `yearly` decimal(5,2) DEFAULT 0.00,
  `limit_minute` int(1) DEFAULT 0,
  `limit_monthly` int(1) DEFAULT 0,
  `active` int(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

/*Data for the table `api_plans` */

insert  into `api_plans`(`id`,`code`,`productcode`,`productcodeyear`,`name`,`description`,`html`,`monthly`,`yearly`,`limit_minute`,`limit_monthly`,`active`) values (1,'starter','plan_DhbUlvsEfOJKaD','plan_DhbUIjKseU2Y6z','Starter',NULL,NULL,'4.95','50.00',0,60000,1);

/*Table structure for table `api_requests` */

DROP TABLE IF EXISTS `api_requests`;

CREATE TABLE `api_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `requesting_ip` varchar(255) DEFAULT NULL,
  `request` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `domainURI` varchar(255) DEFAULT NULL,
  `created_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `api_requests` */

/*Table structure for table `api_usage` */

DROP TABLE IF EXISTS `api_usage`;

CREATE TABLE `api_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT 0,
  `daily` int(11) DEFAULT 0,
  `monthly` int(11) DEFAULT 0,
  `yearly` int(11) DEFAULT 0,
  `dateof` date DEFAULT '0000-00-00',
  `created_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*Data for the table `api_usage` */

/*Table structure for table `config` */

DROP TABLE IF EXISTS `config`;

CREATE TABLE `config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `date_created` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

/*Data for the table `config` */

insert  into `config`(`id`,`key`,`value`,`date_created`) values (1,'stripe_secret_key','','2014-12-03 12:13:59'),(2,'stripe_publishable_key','','2014-12-03 12:13:59'),(3,'paypal_environment','sandbox','2014-12-11 02:24:45'),(4,'payment_type','input','2014-12-03 12:13:59'),(5,'https_redirect','0','2014-12-03 12:13:59'),(6,'email','','2014-12-03 12:13:59'),(7,'show_description','1','2014-12-03 12:13:59'),(8,'page_title','Stripe Advanced Payment Terminal','2014-12-03 12:13:59'),(9,'show_billing_address','1','2014-12-03 12:13:59'),(10,'name','','2014-12-03 23:49:55'),(11,'enable_paypal','1','2014-12-04 02:22:47'),(12,'enable_subscriptions','stripe_and_paypal','2014-12-04 04:03:15'),(13,'paypal_email','','2014-12-04 05:59:49'),(14,'subscription_length','0','2014-12-08 04:11:49'),(15,'subscription_interval','1','2014-12-08 04:13:06'),(16,'currency','USD','2014-12-29 11:29:16'),(17,'enable_trial','0','2014-12-31 00:48:23'),(18,'trial_days','7','2014-12-31 01:03:34'),(19,'notification_status','check','2014-12-31 00:48:23');

/*Table structure for table `orgcategory` */

DROP TABLE IF EXISTS `orgcategory`;

CREATE TABLE `orgcategory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '0',
  `active` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `orgcategory` */

insert  into `orgcategory`(`id`,`name`,`active`) values (1,'Agency',1);

/*Table structure for table `orgprofile` */

DROP TABLE IF EXISTS `orgprofile`;

CREATE TABLE `orgprofile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catid` int(11) DEFAULT 0,
  `name` varchar(255) DEFAULT '0',
  `address` varchar(255) DEFAULT '0',
  `address2` varchar(255) DEFAULT '0',
  `city` varchar(255) DEFAULT '0',
  `state` varchar(255) DEFAULT '0',
  `zip` int(11) DEFAULT 0,
  `phone` varchar(255) DEFAULT '0',
  `email` varchar(255) DEFAULT '0',
  `logo` varchar(255) DEFAULT '0',
  `website` varchar(255) DEFAULT '0',
  `json_social_media` text DEFAULT '0',
  `summary` text DEFAULT '0',
  `description` text DEFAULT '0',
  `auto_email` varchar(255) DEFAULT '0',
  `updated_date` datetime DEFAULT '0000-00-00 00:00:00',
  `taxid` varchar(255) DEFAULT '0',
  `payment_mode` varchar(11) DEFAULT '0',
  `routingnum` varchar(255) DEFAULT '0',
  `bankacctnum` varchar(255) DEFAULT '0',
  `bankname` varchar(255) DEFAULT '0',
  `nameonbank` varchar(255) DEFAULT '0',
  `active` int(1) DEFAULT 1,
  `agreement` varchar(100) DEFAULT '0',
  `ach_auth` varchar(100) DEFAULT '0',
  `createdate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=latin1;

/*Data for the table `orgprofile` */

insert  into `orgprofile`(`id`,`catid`,`name`,`address`,`address2`,`city`,`state`,`zip`,`phone`,`email`,`logo`,`website`,`json_social_media`,`summary`,`description`,`auto_email`,`updated_date`,`taxid`,`payment_mode`,`routingnum`,`bankacctnum`,`bankname`,`nameonbank`,`active`,`agreement`,`ach_auth`,`createdate`) values (106,1,'Snoopi','1515 S. Federal Hwl','Suite 2001','Boca Raton','Florida',33426,'3023570198','support@snoopi.io',NULL,NULL,NULL,NULL,NULL,NULL,'0000-00-00 00:00:00',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'2020-08-17 20:06:13');

/*Table structure for table `orguserjoin` */

DROP TABLE IF EXISTS `orguserjoin`;

CREATE TABLE `orguserjoin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT 0,
  `orgid` int(11) DEFAULT 0,
  `updated_date` datetime DEFAULT '0000-00-00 00:00:00',
  `created_date` datetime DEFAULT current_timestamp(),
  `active` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `orguserjoin` */

/*Table structure for table `permissions` */

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `perm_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perm_controller` varchar(50) NOT NULL DEFAULT '0',
  `perm_action` varchar(50) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT '0',
  `viewroleid` varchar(255) DEFAULT '0' COMMENT 'role that can see these options',
  `menu` int(11) DEFAULT 0,
  `menuorder` int(11) DEFAULT 0,
  PRIMARY KEY (`perm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=latin1;

/*Data for the table `permissions` */

insert  into `permissions`(`perm_id`,`perm_controller`,`perm_action`,`name`,`viewroleid`,`menu`,`menuorder`) values (20,'index','*','Dashboard','1,2,3,4',20,4),(21,'demos','*','Demos','0',21,0),(22,'apikeys','*','API Key','0',22,0),(23,'account','*','Account','0',23,1),(24,'_roles','*','apiRoles','0',1,0),(25,'_subscription','*','apiSubscription','0',0,0),(26,'_users','*','apiUsers','0',0,0),(27,'_test','*','Test','0',0,0),(28,'_dashboard','*','apiDashboard','0',0,0);

/*Table structure for table `role_perm` */

DROP TABLE IF EXISTS `role_perm`;

CREATE TABLE `role_perm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL DEFAULT 0,
  `perm_id` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `perm_id` (`perm_id`),
  CONSTRAINT `role_perm_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`),
  CONSTRAINT `role_perm_ibfk_2` FOREIGN KEY (`perm_id`) REFERENCES `permissions` (`perm_id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=latin1;

/*Data for the table `role_perm` */

insert  into `role_perm`(`id`,`role_id`,`perm_id`) values (50,4,20),(51,4,21),(52,4,22),(53,4,23),(54,4,24),(55,4,25),(56,4,26),(57,2,20),(58,2,21),(60,2,22),(61,2,23),(62,2,24),(63,2,25),(64,2,26),(65,2,27),(66,4,27),(67,2,28),(68,4,28);

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL DEFAULT '0',
  `controller` varchar(100) DEFAULT '0',
  `createdate` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

/*Data for the table `roles` */

insert  into `roles`(`role_id`,`role_name`,`controller`,`createdate`) values (1,'System - Administrator','adminz','2020-08-28 17:56:08'),(2,'Freebies','','2020-08-28 17:56:08'),(4,'Premium','','2020-09-05 20:13:21'),(6,'Marketing','marketing','2020-08-28 17:56:08');

/*Table structure for table `roles_menu` */

DROP TABLE IF EXISTS `roles_menu`;

CREATE TABLE `roles_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleid` int(11) DEFAULT NULL,
  `menu` text DEFAULT NULL,
  `created` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `roles_menu` */

insert  into `roles_menu`(`id`,`roleid`,`menu`,`created`) values (1,4,'{\"Clients\":{\"parent\":{\"name\":\"Clients\",\"link\":\"\\/clients\"}},\"Dashboard\":{\"parent\":{\"name\":\"Dashboard\",\"link\":\"\\/dashboard\"}},\"Companies\":{\"parent\":{\"name\":\"Companies\",\"link\":\"\\/companies\"},\"child\":[{\"name\":\"View Companies\",\"link\":\"\\/companies\"},{\"name\":\"Add New \",\"link\":\"\\/companies\\/add\"}]}}','2020-09-09 16:39:04');

/*Table structure for table `usergen` */

DROP TABLE IF EXISTS `usergen`;

CREATE TABLE `usergen` (
  `userid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT '0',
  `password` varchar(64) DEFAULT '0',
  `email` varchar(255) DEFAULT '0',
  `fname` varchar(255) DEFAULT '0',
  `lname` varchar(255) DEFAULT '0',
  `phone` varchar(255) DEFAULT '0',
  `whatsapp` varchar(255) DEFAULT '0',
  `telegram` varchar(255) DEFAULT '0',
  `website` varchar(255) DEFAULT '0',
  `address1` varchar(255) DEFAULT '0',
  `address2` varchar(255) DEFAULT '0',
  `city` varchar(255) DEFAULT '0',
  `state` varchar(255) DEFAULT '0',
  `zip` varchar(255) DEFAULT '0',
  `country` varchar(255) DEFAULT '0',
  `province` varchar(255) DEFAULT '0',
  `vat` varchar(255) DEFAULT '0',
  `preferences` text DEFAULT '0',
  `avatar` varchar(255) DEFAULT '0',
  `cus_token` varchar(255) DEFAULT '0',
  `role_id` int(11) unsigned DEFAULT 6,
  `isadmin` int(1) DEFAULT 0,
  `lastlogin` datetime DEFAULT '0000-00-00 00:00:00',
  `created_date` datetime DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ipaddress` varchar(255) DEFAULT '000.000.000.000',
  `active` int(1) DEFAULT 1,
  `notes` varchar(255) DEFAULT '0',
  `pwtoken` varchar(20) DEFAULT '0',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `username` (`username`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `usergen_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `usergen` */

/*Table structure for table `users_api` */

DROP TABLE IF EXISTS `users_api`;

CREATE TABLE `users_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `apikey` varchar(255) DEFAULT NULL,
  `created_date` datetime DEFAULT current_timestamp(),
  `active` int(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `users_api` */

/*Table structure for table `users_notes` */

DROP TABLE IF EXISTS `users_notes`;

CREATE TABLE `users_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_date` datetime DEFAULT current_timestamp(),
  `active` int(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `users_notes` */

/*Table structure for table `users_plans` */

DROP TABLE IF EXISTS `users_plans`;

CREATE TABLE `users_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT 0,
  `planid` int(11) DEFAULT 0,
  `premium` int(11) DEFAULT 0,
  `stripe_subid` varchar(255) DEFAULT '0',
  `created_date` datetime DEFAULT current_timestamp(),
  `subcreatedate` datetime DEFAULT '0000-00-00 00:00:00',
  `subcanceldate` datetime DEFAULT '0000-00-00 00:00:00',
  `promotion` datetime DEFAULT '0000-00-00 00:00:00',
  `active` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `users_plans` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
