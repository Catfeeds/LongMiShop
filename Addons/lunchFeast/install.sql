SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `lm_addons_lunchfeast_invite_list`;
CREATE TABLE `lm_addons_lunchfeast_invite_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `parent_user_id` int(11) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `lm_addons_lunchfeast_shop`;
CREATE TABLE `lm_addons_lunchfeast_shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(100) DEFAULT NULL COMMENT '名称',
  `mobile` int(11) DEFAULT NULL COMMENT '联系电话',
  `province` int(11) NOT NULL DEFAULT '0' COMMENT '省份',
  `city` int(11) NOT NULL DEFAULT '0' COMMENT '城市',
  `district` int(11) NOT NULL DEFAULT '0' COMMENT '县区',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `create_time` int(11) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `desc` varchar(255) DEFAULT NULL COMMENT '简介',
  `content` text COMMENT '详细描述',
  `goods` text COMMENT '菜品',
  `seats` int(11) NOT NULL DEFAULT '0' COMMENT '座位数',
  `is_online`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否上线',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `lm_addons_lunchfeast_meal_list`;
CREATE TABLE `lm_addons_lunchfeast_meal_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `lm_addons_lunchfeast_meal_list` VALUES ('1', '中午', '1', '0');
INSERT INTO `lm_addons_lunchfeast_meal_list` VALUES ('2', '晚上', '0', '0');


DROP TABLE IF EXISTS `lm_addons_lunchfeast_shop_goods`;
CREATE TABLE `lm_addons_lunchfeast_shop_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `date` int(10) DEFAULT NULL COMMENT '日期',
  `meal_id` int(11) NOT NULL COMMENT '饭点id',
  `content` text COMMENT '菜品',
  `money` float(10,2) NOT NULL DEFAULT '0.00' COMMENT '总价',
  `create_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lm_addons_lunchfeast_diningper`;
CREATE TABLE `lm_addons_lunchfeast_diningper` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户uid',
  `names` varchar(255) DEFAULT NULL COMMENT '用餐人 名字',
  `mobile` char(15) DEFAULT NULL COMMENT '用餐人电话',
  `show` int(1) DEFAULT '1' COMMENT '是否显示',
  `pitchon` int(1) NOT NULL COMMENT '选中',
  `add_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lm_addons_lunchfeast_config`;
CREATE TABLE `lm_addons_lunchfeast_config` (
  `main` text,
  `title` varchar(255) DEFAULT NULL,
  `desc` varchar(255) DEFAULT NULL,
  `shareimg` varchar(255) DEFAULT NULL,
  `invite` int(2) DEFAULT NULL,
  `invited_value` varchar(255) DEFAULT NULL,
  `invited_to` int(2) DEFAULT NULL,
  `invited_to_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lm_addons_lunchfeast_order`;
CREATE TABLE `lm_addons_lunchfeast_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `order_sn`  varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '订单编号',
  `order_amount` float(11,2) DEFAULT NULL COMMENT '总价',
  `pay_amount` float(11,2) DEFAULT NULL COMMENT '实际支付金额',
  `coupon_price` float(11,2) DEFAULT NULL COMMENT '折扣金额',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0 未支付 1 已支付 2 已使用 3 已过期',
  `create_time` int(10) DEFAULT NULL,
  `pay_time` int(10) DEFAULT NULL,
  `date` int(10) DEFAULT NULL COMMENT '就餐时间',
  `meal_id` int(11) DEFAULT NULL COMMENT '饭点id',
  `shop_id` int(11) DEFAULT NULL COMMENT '店铺id',
  `meal_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '菜品',
  `number`  int(11) NULL COMMENT '人数',
  `transferring` varchar(255) DEFAULT NULL COMMENT '转让历史',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lm_addons_lunchfeast_order_user`;
CREATE TABLE `lm_addons_lunchfeast_order_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `code`  varchar(30) NULL COMMENT '编号',
  `order_id` int(11) DEFAULT NULL COMMENT '订单id',
  `diningper_id` int(11) DEFAULT NULL,
  `is_use` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否核销',
  `use_time` int(11) DEFAULT '0' COMMENT '核销时间',
  `admin_id` int(11) DEFAULT '0' COMMENT '核销员id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lm_addons_lunchfeast_order_pay_log`;
CREATE TABLE `lm_addons_lunchfeast_order_pay_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL COMMENT '订单id',
  `user_id` int(11) DEFAULT NULL,
  `openid` varchar(100) DEFAULT NULL,
  `create_time` int(11) DEFAULT NULL,
  `tag` text COMMENT '微信tag',
  `money` float(11,2) DEFAULT NULL COMMENT '支付金额',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0 未支付 1 已支付 2 异常',
  `pay_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lm_addons_lunchfeast_admin`;
CREATE TABLE `lm_addons_lunchfeast_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) DEFAULT NULL COMMENT '店铺id',
  `level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '等级 0 普通 1超级',
  `username` varchar(100) DEFAULT NULL COMMENT '用户名',
  `password` varchar(100) DEFAULT NULL COMMENT '密码',
  `token` varchar(125) DEFAULT NULL,
  `last_time` int(11) DEFAULT NULL COMMENT '最后一次登录时间',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `desc` varchar(255) DEFAULT NULL COMMENT '备注',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `is_lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



