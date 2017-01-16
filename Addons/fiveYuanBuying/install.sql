CREATE TABLE `lm_addons_fiveyuanbuying_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `order_sn` varchar(30) DEFAULT NULL COMMENT '订单编号',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0 未支付 1 已支付',
  `money` float(11,2) NOT NULL DEFAULT '0.00' COMMENT '订单价格',
  `create_time` int(10) DEFAULT NULL,
  `pay_time` int(10) DEFAULT NULL COMMENT '支付时间',
  `pay_tag` text COMMENT '微信tag',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

