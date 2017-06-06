CREATE TABLE `lm_addons_cookrice_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态: 0 进行中 1 待领取 2 已领取',
  `edition_id` int(11) DEFAULT NULL COMMENT '版本id',
  `user_name` varchar(50) DEFAULT NULL COMMENT '领奖人名称',
  `user_phone` varchar(20) DEFAULT NULL COMMENT '领奖人电话',
  `user_site` varchar(255) DEFAULT NULL COMMENT '领奖人地址',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `lm_addons_cookrice_help_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `edition_id` int(11) DEFAULT NULL COMMENT '版本id',
  `value` int(11) DEFAULT NULL COMMENT '数值',
  `desc` varchar(150) DEFAULT NULL COMMENT '描述',
  `head_pic` varchar(255) DEFAULT NULL COMMENT '头像',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
