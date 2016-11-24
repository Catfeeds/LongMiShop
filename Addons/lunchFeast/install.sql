SET FOREIGN_KEY_CHECKS=0;


-- ----------------------------
-- Table structure for lm_addons_lunchfeast_shop
-- ----------------------------
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `lm_addons_lunchfeast_shop`
ADD COLUMN `is_online`  tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否上线' AFTER `seats`;



-- ----------------------------
-- Table structure for lm_addons_lunchfeast_meal_list
-- ----------------------------
DROP TABLE IF EXISTS `lm_addons_lunchfeast_meal_list`;
CREATE TABLE `lm_addons_lunchfeast_meal_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示',
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lm_addons_lunchfeast_meal_list
-- ----------------------------
INSERT INTO `lm_addons_lunchfeast_meal_list` VALUES ('1', '中午', '1', '0');
INSERT INTO `lm_addons_lunchfeast_meal_list` VALUES ('2', '晚上', '0', '0');


-- ----------------------------
-- Table structure for lm_addons_lunchfeast_shop_goods
-- ----------------------------
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
  `main` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




DROP TABLE IF EXISTS `lm_addons_lunchfeast_order`;
CREATE TABLE `lm_addons_lunchfeast_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `order_amount` float(11,2) DEFAULT NULL COMMENT '总价',
  `pay_amount` float(11,2) DEFAULT NULL COMMENT '实际支付金额',
  `coupon_price` float(11,2) DEFAULT NULL COMMENT '折扣金额',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0 未支付 1 已支付 2 已使用 3 已过期',
  `create_time` int(10) DEFAULT NULL,
  `pay_time` int(10) DEFAULT NULL,
  `date` int(10) DEFAULT NULL COMMENT '就餐时间',
  `meal_id` int(11) DEFAULT NULL COMMENT '饭点id',
  `shop_id` int(11) DEFAULT NULL COMMENT '店铺id',
  `mealContent` text COMMENT '菜品',
  `transferring` varchar(255) DEFAULT NULL COMMENT '转让历史',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lm_addons_lunchfeast_order_user`;
CREATE TABLE `lm_addons_lunchfeast_order_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL COMMENT '订单id',
  `diningper_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

;
ALTER TABLE `lm_addons_lunchfeast_order`
ADD COLUMN `number`  int(11) NULL COMMENT '人数' AFTER `mealContent`;

ALTER TABLE `lm_addons_lunchfeast_order`
CHANGE COLUMN `mealContent` `meal_content`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '菜品' AFTER `shop_id`;

ALTER TABLE `lm_addons_lunchfeast_order`
ADD COLUMN `order_sn`  varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '订单编号' AFTER `user_id`;

ALTER TABLE `lm_addons_lunchfeast_order_user`
ADD COLUMN `code`  varchar(30) NULL COMMENT '编号' AFTER `id`;


