DROP TABLE IF EXISTS `lm_addons_forevercoupon_user`;
CREATE TABLE `lm_addons_forevercoupon_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `edition` int(11) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lm_addons_forevercoupon_config`;
CREATE TABLE `lm_addons_forevercoupon_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` int(11) DEFAULT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `is_delete` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;