SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for lm_addons_incubation_help
-- ----------------------------
DROP TABLE IF EXISTS `lm_addons_incubation_activity`;
CREATE TABLE `lm_addons_incubation_activity` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `help_uid` int(11) DEFAULT NULL COMMENT '帮助者id',
  `user_id` int(11) DEFAULT NULL COMMENT '被帮助者id',
  `temperature` int(11) DEFAULT NULL COMMENT '加热温度',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

