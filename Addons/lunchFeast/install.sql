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
INSERT INTO `lm_addons_lunchfeast_meal_list` VALUES ('1', '午餐', '1', '0');
INSERT INTO `lm_addons_lunchfeast_meal_list` VALUES ('2', '晚餐', '0', '0');


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

