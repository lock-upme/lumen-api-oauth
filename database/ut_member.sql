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
-- Table structure for ut_member
-- ----------------------------
DROP TABLE IF EXISTS `ut_member`;
CREATE TABLE `ut_member` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `phone` varchar(11) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `avatar` varchar(225) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0无效用户 1正常',
  `eactive` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未激活 1激活',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:普通用户，1:企业',
  `salt` varchar(10) NOT NULL,
  `releuid` int(10) DEFAULT '0' COMMENT '关联帐号id',
  `inviteuid` int(10) DEFAULT '0' COMMENT '邀请者uid',
  `invitecode` varchar(16) DEFAULT NULL COMMENT '用户邀请注册唯一标示',
  `from` varchar(30) DEFAULT NULL COMMENT '从哪里来的',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `invitecode` (`invitecode`),
  KEY `inviteuid` (`inviteuid`),
  KEY `username` (`username`,`email`,`status`,`eactive`,`type`,`phone`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='会员表';
