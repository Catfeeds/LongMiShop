CREATE TABLE `lm_addons_partner_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `p_name` varchar(100) DEFAULT NULL COMMENT '合伙人姓名',
  `p_city` varchar(255) DEFAULT NULL COMMENT '合伙人城市',
  `p_sex` varchar(30) DEFAULT NULL COMMENT '合伙人性别',
  `p_phone` varchar(13) DEFAULT NULL COMMENT '合伙人手机号',
  `p_wechat` varchar(50) DEFAULT NULL COMMENT '合伙人微信号',
  `p_email` varchar(50) DEFAULT NULL COMMENT '合伙人邮箱',
  `p_desc` varchar(255) DEFAULT NULL COMMENT '合伙人个人优势',
  `create_time` int(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0 未查看 1 已查看',
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

