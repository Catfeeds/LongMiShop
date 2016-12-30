CREATE TABLE `lm_addons_ricegrains_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `user_id` int(11) DEFAULT NULL,
  `fraction` int(11) DEFAULT NULL,
  `create_time` int(10) DEFAULT NULL,
  `openid` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




CREATE TABLE `lm_addons_ricegrains_gift` (
  `coupon_id1` int(11) DEFAULT NULL,
  `coupon_id2` int(11) DEFAULT NULL,
  `coupon_id3` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
