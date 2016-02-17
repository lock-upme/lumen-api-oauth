/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50626
Source Host           : localhost:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50626
File Encoding         : 65001

Date: 2016-01-30 16:08:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ut_member_users
-- ----------------------------
DROP TABLE IF EXISTS `ut_member_users`;
CREATE TABLE `ut_member_users` (
  `users_id` int(10) DEFAULT NULL,
  `member_uid` int(10) DEFAULT NULL,
  KEY `INDEX_IUID` (`users_id`,`member_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
