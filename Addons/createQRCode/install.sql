CREATE TABLE `lm_addons_createqrcode_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qr_id` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `openid` varchar(100) DEFAULT NULL,
  `event` varchar(100) DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `lm_addons_createqrcode_qr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(100) DEFAULT NULL,
  `type` int(1) NOT NULL DEFAULT '1' COMMENT '类型 1永久 2临时',
  `limit_time` int(11) DEFAULT '0' COMMENT '存在时间',
  `ticket` varchar(150) DEFAULT NULL,
  `url` varchar(150) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `key_word` varchar(50) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

