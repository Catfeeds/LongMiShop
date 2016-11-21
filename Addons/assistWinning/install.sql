SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for lm_addons_assistwinning_help
-- ----------------------------
DROP TABLE IF EXISTS `lm_addons_assistwinning_help`;
CREATE TABLE `lm_addons_assistwinning_help` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `help_uid` int(11) DEFAULT NULL COMMENT '帮助者id',
  `user_id` int(11) DEFAULT NULL COMMENT '被帮助者id',
  `temperature` int(11) DEFAULT NULL COMMENT '加热温度',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lm_addons_assistwinning_prize`;
CREATE TABLE `lm_addons_assistwinning_prize` (
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `prize` varchar(255) DEFAULT NULL COMMENT '奖品',
  `phone` char(15) DEFAULT NULL COMMENT '手机号码',
  `site` varchar(255) DEFAULT NULL COMMENT '发放地址',
  `create_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

