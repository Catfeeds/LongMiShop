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

