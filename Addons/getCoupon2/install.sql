CREATE TABLE `lm_addons_redrain_invite_list` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`user_id`  int(11) NULL ,
`parent_user_id`  int(11) NULL COMMENT '上级uid' ,
`create_time`  int(11) NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `lm_addons_redrain_winning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `money` float(11,2) DEFAULT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0 没发钱 1 发钱了',
  `create_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `lm_addons_redrain_stop` (
  `stop` int(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
