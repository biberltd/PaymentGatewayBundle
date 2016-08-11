/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 50712
 Source Host           : localhost
 Source Database       : database-structure

 Target Server Type    : MySQL
 Target Server Version : 50712
 File Encoding         : utf-8

 Date: 08/11/2016 17:17:46 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `payment_gateway`
-- ----------------------------
DROP TABLE IF EXISTS `payment_gateway`;
CREATE TABLE `payment_gateway` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `date_added` datetime NOT NULL,
  `settings` text NOT NULL,
  `site` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `site` (`site`),
  KEY `site_2` (`site`),
  CONSTRAINT `idx_f_payment_gateway_site` FOREIGN KEY (`site`) REFERENCES `site` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `payment_gateway_localization`
-- ----------------------------
DROP TABLE IF EXISTS `payment_gateway_localization`;
CREATE TABLE `payment_gateway_localization` (
  `gateway` int(10) NOT NULL,
  `language` int(5) unsigned NOT NULL,
  `name` text NOT NULL,
  `url_key` text NOT NULL,
  `description` text,
  PRIMARY KEY (`gateway`,`language`),
  KEY `language` (`language`),
  KEY `language_2` (`language`),
  CONSTRAINT `idx_f_payment_gateway_localization_gateway` FOREIGN KEY (`gateway`) REFERENCES `payment_gateway` (`id`) ON DELETE CASCADE,
  CONSTRAINT `idx_f_payment_gateway_localization_language` FOREIGN KEY (`language`) REFERENCES `language` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
