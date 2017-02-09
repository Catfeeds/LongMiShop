CREATE TABLE `addons_agriculturalbank_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `p_name` varchar(100) DEFAULT NULL COMMENT '姓名',
  `p_phone` varchar(13) DEFAULT NULL COMMENT '手机号',
  `p_branch` varchar(50) DEFAULT NULL COMMENT '支行',
  `create_time` int(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0 未查看 1 已查看',
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

