
CREATE TABLE `lm_addons_christmas_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(125) DEFAULT NULL COMMENT '活动名称',
  `desc` text COMMENT '描述（注意事项）',
  `money`  float(11,2) NOT NULL DEFAULT 0.00 COMMENT '订单价格' ,
  `create_time` int(10) DEFAULT NULL,
  `is_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除',
  `number`  int(11) NOT NULL DEFAULT 0 COMMENT '发行数量' ,
  `start_time` int(10) DEFAULT NULL COMMENT '是否删除',
  `end_time` int(10) DEFAULT NULL COMMENT '是否删除',
  `wx_title` varchar(255) DEFAULT NULL COMMENT '微信气泡标题',
  `wx_desc` varchar(255) DEFAULT NULL COMMENT '微信气泡简介',
  `wx_shareimg` varchar(255) DEFAULT NULL COMMENT '微信气泡头像',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `lm_addons_christmas_activity_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `activity_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动id',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '供应商id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `goods_sn` varchar(60) NOT NULL DEFAULT '' COMMENT '商品货号',
  `goods_name` varchar(120) NOT NULL DEFAULT '' COMMENT '商品名称',
  `spec_key` varchar(64) DEFAULT NULL COMMENT '商品规格key 对应tp_spec_goods_price 表',
  `spec_key_name` varchar(64) DEFAULT NULL COMMENT '商品规格组合名称',
  `goods_num` int(11) NOT NULL COMMENT '商品数量',
  `goods_money`  float(11,2) NOT NULL DEFAULT 0.00 COMMENT '商品价格',
  `create_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lm_addons_christmas_order` (
  `id`  int(11) NOT NULL AUTO_INCREMENT ,
  `user_id`  int(11) NULL COMMENT '用户id' ,
  `activity_id`  int(11) NULL COMMENT '活动id' ,
  `order_sn`  varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '订单编号',
  `status`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0 未支付 1 已支付 2 已送出' ,
  `money`  float(11,2) NOT NULL DEFAULT 0.00 COMMENT '订单价格' ,
  `message` varchar(255) NOT NULL DEFAULT '' COMMENT '留言',
  `create_time`  int(10) NULL ,
  `pay_time`  int(10) NULL COMMENT '支付时间' ,
  `pay_tag`  text COMMENT '微信tag',
  `get_time`  int(10) NULL COMMENT '获取时间' ,
  `get_user_id`  int(11) NULL COMMENT '获取者id' ,
  `order_id`  int(11) NOT NULL DEFAULT 0 COMMENT '订单id' ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `lm_addons_christmas_order_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `activity_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动id',
  `order_id` int(11) NOT NULL COMMENT '订单id',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '供应商id',
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `goods_sn` varchar(60) NOT NULL DEFAULT '' COMMENT '商品货号',
  `goods_name` varchar(120) NOT NULL DEFAULT '' COMMENT '商品名称',
  `spec_key` varchar(64) DEFAULT NULL COMMENT '商品规格key 对应tp_spec_goods_price 表',
  `spec_key_name` varchar(64) DEFAULT NULL COMMENT '商品规格组合名称',
  `goods_num` int(11) NOT NULL COMMENT '商品数量',
  `goods_money`  float(11,2) NOT NULL DEFAULT 0.00 COMMENT '商品价格',
  `create_time` int(11) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;