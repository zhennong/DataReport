/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50546
Source Host           : 127.0.0.1:3306
Source Database       : data_report

Target Server Type    : MYSQL
Target Server Version : 50546
File Encoding         : 65001

Date: 2016-01-22 13:48:35
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for dr_admin
-- ----------------------------
DROP TABLE IF EXISTS `dr_admin`;
CREATE TABLE `dr_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `account` varchar(32) DEFAULT NULL COMMENT '管理员账号',
  `password` varchar(36) DEFAULT NULL COMMENT '管理员密码',
  `mobile` varchar(11) DEFAULT NULL COMMENT '手机号',
  `login_time` int(11) DEFAULT NULL COMMENT '最后登录时间',
  `login_ip` varchar(15) DEFAULT NULL COMMENT '最后登录IP',
  `login_count` mediumint(8) NOT NULL COMMENT '登录次数',
  `email` varchar(40) DEFAULT NULL COMMENT '邮箱',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '账户状态，禁用为0   启用为1',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Records of dr_admin
-- ----------------------------
INSERT INTO `dr_admin` VALUES ('1', 'admin', 'e10adc3949ba59abbe56e057f20f883e', '15515783176', '1453440801', '192.168.0.15', '43', '', '1', null);
INSERT INTO `dr_admin` VALUES ('36', 'caiwu', 'e10adc3949ba59abbe56e057f20f883e', null, '1453170438', '127.0.0.1', '15', null, '1', '1452835639');

-- ----------------------------
-- Table structure for dr_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `dr_auth_group`;
CREATE TABLE `dr_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` char(80) NOT NULL DEFAULT '',
  `create_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Records of dr_auth_group
-- ----------------------------
INSERT INTO `dr_auth_group` VALUES ('30', '财务部', '1', '125,126,128,130,131,132,133,135', '1452835547');
INSERT INTO `dr_auth_group` VALUES ('31', '管理员', '1', '121,122,123,124,125,126', '1452835659');
INSERT INTO `dr_auth_group` VALUES ('32', '产品部', '1', '', '1452846072');

-- ----------------------------
-- Table structure for dr_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `dr_auth_group_access`;
CREATE TABLE `dr_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `group_id` (`group_id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

-- ----------------------------
-- Records of dr_auth_group_access
-- ----------------------------
INSERT INTO `dr_auth_group_access` VALUES ('1', '31');
INSERT INTO `dr_auth_group_access` VALUES ('35', '31');
INSERT INTO `dr_auth_group_access` VALUES ('36', '30');

-- ----------------------------
-- Table structure for dr_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `dr_auth_rule`;
CREATE TABLE `dr_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL DEFAULT '',
  `title` varchar(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '',
  `pid` smallint(5) NOT NULL COMMENT '父级ID',
  `sort` smallint(5) NOT NULL COMMENT '排序',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=151 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of dr_auth_rule
-- ----------------------------
INSERT INTO `dr_auth_rule` VALUES ('121', 'Admin/admin_index', '会员管理', '1', '1', '', '0', '0', '1452835299');
INSERT INTO `dr_auth_rule` VALUES ('122', 'Admin/admin_list', '用户管理', '1', '1', '', '121', '0', '1452835464');
INSERT INTO `dr_auth_rule` VALUES ('123', 'Admin/auth_group', '分组管理', '1', '1', '', '121', '0', '1452835496');
INSERT INTO `dr_auth_rule` VALUES ('124', 'Admin/auth_rule', '权限管理', '1', '1', '', '121', '0', '1452835528');
INSERT INTO `dr_auth_rule` VALUES ('125', 'Trade/trade_index', '订单管理', '1', '1', '', '0', '0', '1452841736');
INSERT INTO `dr_auth_rule` VALUES ('126', 'Trade/orderTrend', '订单走势', '1', '1', '', '125', '0', '1452841764');
INSERT INTO `dr_auth_rule` VALUES ('127', 'Finance/financeIndex', '财务管理', '1', '1', '', '0', '0', '1452908121');
INSERT INTO `dr_auth_rule` VALUES ('128', 'Trade/orderTime', '下单时段', '1', '1', '', '125', '0', '1452951841');
INSERT INTO `dr_auth_rule` VALUES ('129', 'Finance/paymentTotal', '付款总额', '1', '1', '', '127', '0', '1452908507');
INSERT INTO `dr_auth_rule` VALUES ('130', 'Trade/orderRate ', '客户退单', '1', '1', '', '125', '0', '1452933149');
INSERT INTO `dr_auth_rule` VALUES ('131', 'Trade/orderPay', '付款时段', '1', '1', '', '125', '0', '1452933206');
INSERT INTO `dr_auth_rule` VALUES ('132', 'Trade/orderLogistics', '发货时段', '1', '1', '', '125', '0', '1452933282');
INSERT INTO `dr_auth_rule` VALUES ('133', 'Trade/orderPaytype', '支付方式', '1', '1', '', '125', '0', '1452933320');
INSERT INTO `dr_auth_rule` VALUES ('134', 'Finance/mouthSoltPayment', '月付款', '1', '1', '', '127', '0', '1452993474');
INSERT INTO `dr_auth_rule` VALUES ('135', 'Trade/orderYearTrend', '订单年走势', '1', '1', '', '125', '0', '1452996406');
INSERT INTO `dr_auth_rule` VALUES ('136', 'Finance/annualSoltPayment', '年付款', '1', '1', '', '127', '0', '1453001837');
INSERT INTO `dr_auth_rule` VALUES ('137', 'Finance/annualGrowthRateOfPayment', '付款年增长率', '1', '1', '', '127', '0', '1453002080');
INSERT INTO `dr_auth_rule` VALUES ('139', 'Finance/sameMouthAnnually', '月付款同期对比', '1', '1', '', '127', '0', '1453014407');
INSERT INTO `dr_auth_rule` VALUES ('140', 'Member/memberIndex', '会员管理', '1', '1', '', '0', '0', '1453096894');
INSERT INTO `dr_auth_rule` VALUES ('141', 'Member/memberReg', '会员注册', '1', '1', '', '140', '0', '1453096928');
INSERT INTO `dr_auth_rule` VALUES ('142', 'Member/memberInfos', '会员信息', '1', '1', '', '140', '0', '1453108773');
INSERT INTO `dr_auth_rule` VALUES ('143', 'Member/memberPay', '会员付款', '1', '1', '', '140', '0', '1453108927');
INSERT INTO `dr_auth_rule` VALUES ('144', 'Member/memberRegApp', '会员注册（APP）', '1', '1', '', '140', '0', '1453109014');
INSERT INTO `dr_auth_rule` VALUES ('145', 'Member/memberCount', '会员统计', '1', '1', '', '140', '0', '1453109062');
INSERT INTO `dr_auth_rule` VALUES ('146', 'Business/businessIndex', '招商管理', '1', '1', '', '0', '0', '1453432320');
INSERT INTO `dr_auth_rule` VALUES ('147', 'Business/businessTrend', '加盟走势图', '1', '1', '', '146', '0', '1453432498');
INSERT INTO `dr_auth_rule` VALUES ('148', 'Business/businessTotal', '各县交易额', '1', '1', '', '146', '0', '1453432642');
INSERT INTO `dr_auth_rule` VALUES ('149', 'Business/businessCusTotal', '客户交易额', '1', '1', '', '146', '0', '1453432783');
INSERT INTO `dr_auth_rule` VALUES ('150', 'Business/businessHot', '合作商热力图', '1', '1', '', '146', '0', '1453432861');
