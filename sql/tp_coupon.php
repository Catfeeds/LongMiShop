<?php

$sql = "
//新增字段


ALTER TABLE  `tp_coupon` ADD  `is_discount` TINYINT( 1 ) NOT NULL COMMENT  '券类型' AFTER  `type` ;

//配置表插入
INSERT INTO `tpshop`.`tp_config` (`id`, `name`, `value`, `inc_type`, `desc`) VALUES (NULL, 'details', '1', 'shop_info', NULL);

INSERT INTO `tpshop`.`tp_config` (`id`, `name`, `value`, `inc_type`, `desc`) VALUES (NULL, 'classify', '1', 'shop_info', NULL);

INSERT INTO `tpshop`.`tp_config` (`id`, `name`, `value`, `inc_type`, `desc`) VALUES (NULL, 'article', '1', 'shop_info', NULL);

INSERT INTO `tpshop`.`tp_config` (`id`, `name`, `value`, `inc_type`, `desc`) VALUES (NULL, 'default', '1', 'shop_info', NULL);


//新增QA数据表
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tp_problem
-- ----------------------------
DROP TABLE IF EXISTS `tp_problem`;
CREATE TABLE `tp_problem` (
  `pro_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pro_title` varchar(250) DEFAULT NULL COMMENT '描述',
  `pro_rank` int(11) NOT NULL COMMENT '排序 从大到小',
  `pro_show` int(2) NOT NULL COMMENT '1显示 2隐藏',
  `pro_details` text COMMENT '详情',
  `pro_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`pro_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

//新增物流表
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tp_logistics
-- ----------------------------
DROP TABLE IF EXISTS `tp_logistics`;
CREATE TABLE `tp_logistics` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_rank` int(11) NOT NULL COMMENT '排序',
  `log_template` varchar(255) DEFAULT NULL COMMENT '模版名称',
  `log_delivery` varchar(255) DEFAULT NULL COMMENT '配送方式',
  `log_province` varchar(50) DEFAULT NULL COMMENT '商品所在省份',
  `log_city` varchar(50) DEFAULT NULL COMMENT '城市',
  `log_aging` varchar(20) NOT NULL COMMENT '发货时效 ',
  `log_is_take_their` tinyint(2) unsigned NOT NULL COMMENT '是否自提  1允许  2不允许',
  `log_take_their_site` varchar(255) DEFAULT NULL COMMENT '自提地址',
  `log_is_free` tinyint(2) NOT NULL COMMENT '包邮  1是  2否',
  `log_is_collect` tinyint(2) NOT NULL COMMENT '到付 1是 2否',
  `log_mode` tinyint(2) NOT NULL COMMENT '记重方式 1重量 2件数 ',
  `log_cost` decimal(10,0) NOT NULL COMMENT '基础费用',
  `log_cost_add` decimal(10,0) NOT NULL COMMENT '增加费用',
  `log_amount` int(11) NOT NULL COMMENT '基础件数/重量',
  `log_amount_add` int(11) NOT NULL COMMENT '增加件数/重量',
  `log_ispinkage` tinyint(2) NOT NULL COMMENT '包邮条件 1有  2空',
  `log_condition` text COMMENT '包邮地区',
  `log_time` int(11) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

//商品表 添加配送字段
ALTER TABLE  `tp_goods` ADD  `delivery_way` INT( 11 ) NOT NULL COMMENT  '配送方式' AFTER  `is_free_shipping` ;

//配置表新增官网id
INSERT INTO `tpshop`.`tp_config` (`id`, `name`, `value`, `inc_type`, `desc`) VALUES (NULL, 'index_id', '104', 'shop_info', NULL);



//新增邮箱验证表
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tp_email_log
-- ----------------------------
DROP TABLE IF EXISTS `tp_email_log`;
CREATE TABLE `tp_email_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `secret_key` varchar(255) DEFAULT '验证密钥',
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;


//退款
ALTER TABLE `tp_return_goods`
ADD COLUMN `refund_name`  varchar(60) NULL AFTER `spec_key`,
ADD COLUMN `refund_code`  varchar(100) NULL AFTER `refund_name`,
ADD COLUMN `refund_way`  int(1) NOT NULL DEFAULT 0 COMMENT '还款方式 0， 原路   1，余额 ' AFTER `refund_code`;
ADD COLUMN `refund_money`  decimal(10,2) NULL DEFAULT 0 COMMENT '金额' AFTER `refund_way`;


//文章表  新增字段
ALTER TABLE  `tp_article` ADD  `device_type` TINYINT( 3 ) NOT NULL COMMENT  '设备类型 1PC 2手机 3pc+手机' AFTER  `cat_id`

//新增表 消息记录表
CREATE TABLE `tp_push_message` (
  `push_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  PRIMARY KEY (`push_id`)
) AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


//openid绑定表
CREATE TABLE `tp_binding` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`user_id`  int(11) NOT NULL ,
`openid`  varchar(50) NOT NULL ,
`create_time`  int(10) NULL ,
`update_time`  int(10) NULL ,
PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8;

//openid绑定表
ALTER TABLE `tp_binding`
ADD COLUMN `third_user_id`  int(10) NOT NULL COMMENT '第三方id' AFTER `update_time`,
ADD COLUMN `current_user_id`  int(10) NOT NULL COMMENT '当前id' AFTER `third_user_id`;


CREATE TABLE `tp_coupon_code` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`user_id`  int(11) NOT NULL DEFAULT 0 COMMENT '用户id' ,
`gift_coupon_id`  int(11) NOT NULL DEFAULT 0 COMMENT '礼品券id' ,
`coupon_id`  int(11) NOT NULL DEFAULT 0 COMMENT '优惠券id' ,
`code`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '优惠码' ,
`state`  int(1) NOT NULL DEFAULT 0 COMMENT '0 未领取   1 已领取  2  已使用' ,
`use_time`  int(10) NULL COMMENT '使用时间' ,
`receive_time`  int(10) NULL COMMENT '获取卡券时间' ,
`create_time`  int(10) NULL COMMENT '创建时间' ,
`update_time`  int(10) NULL COMMENT '修改时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
;


ALTER TABLE `tp_coupon`
ADD COLUMN `use_code`  tinyint(1) NOT NULL DEFAULT 0 AFTER `type`;


CREATE TABLE `tp_gift_coupon` (
`id`  int(11) NOT NULL AUTO_INCREMENT COMMENT 'id' ,
`is_create_code`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已经生成兑换' ,
`gift_coupon_name`  varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '礼品券名称' ,
`send_start_time`  int(11) NULL COMMENT '发放开始时间' ,
`send_end_time`  int(11) NULL COMMENT '发放结束时间' ,
`use_start_time`  int(11) NULL COMMENT '使用开始时间' ,
`use_end_time`  int(11) NULL COMMENT '使用结束时间' ,
`create_time`  int(11) NULL COMMENT '添加时间' ,
`update_time`  int(11) NULL COMMENT '修改时间' ,
`create_num`  int(11) NULL COMMENT '发放数量' ,
`send_num`  int(11) NULL COMMENT '已领取数量' ,
`use_num`  int(11) NULL COMMENT '已使用数量' ,
`condition`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '详情' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
;



CREATE TABLE `tp_gift_coupon_goods_list` (
`id`  int(11) NOT NULL AUTO_INCREMENT COMMENT 'id' ,
`gift_coupon_id`  int(11) NOT NULL DEFAULT 0 COMMENT '礼品券id' ,
`admin_id`  int(11) NOT NULL DEFAULT 0 COMMENT '供应商id' ,
`goods_id`  int(11) NOT NULL COMMENT '商品id' ,
`goods_sn`  varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品货号' ,
`goods_name`  varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '商品名称' ,
`spec_key`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '商品规格key 对应tp_spec_goods_price 表' ,
`spec_key_name`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '商品规格组合名称' ,
`goods_num`  int(11) NOT NULL COMMENT '商品数量' ,
`create_time`  int(11) NULL COMMENT '添加时间' ,
`update_time`  int(11) NULL COMMENT '修改时间' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
;


CREATE TABLE `tp_invite_list` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`user_id`  int(11) NOT NULL ,
`parent_user_id`  int(11) NULL ,
`create_time`  int(11) NULL ,
`update_time`  int(11) NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
;
//关注部分
ALTER TABLE `lm_users`
ADD COLUMN `is_follow`  int(1) NOT NULL DEFAULT 0 COMMENT '是否关注' AFTER `third_leader`,
ADD COLUMN `follow_time`  int(11) NULL COMMENT '关注时间' AFTER `is_follow`,
ADD COLUMN `unfollow_time`  int(11) NULL COMMENT '取消关注时间' AFTER `follow_time`;

//用户表新增同步时间字段
ALTER TABLE  `lm_users` ADD  `sync_time` INT( 11 ) DEFAULT NULL COMMENT  '上次同步时间' AFTER  `unfollow_time`;


ALTER TABLE `lm_goods_images`
ADD COLUMN `sort`  int(11) DEFAULT '0' COMMENT '排序';

//商品表新增字段
ALTER TABLE  `lm_goods` ADD  `virtual_sales` INT( 11 ) NOT NULL COMMENT  '商品虚拟销量' AFTER  `goods_sn`;
ALTER TABLE  `lm_goods` ADD  `virtual_address` VARCHAR( 255 ) NOT NULL COMMENT  '商品虚拟发货地址' AFTER  `virtual_sales`;

//新增反馈表
CREATE TABLE `lm_user_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `nickname` varchar(255) DEFAULT NULL COMMENT '用户昵称',
  `phone_model` varchar(255) DEFAULT NULL COMMENT '手机型号',
  `phone_network` varchar(255) DEFAULT NULL COMMENT '手机网络',
  `user_proposal` text COMMENT '反馈内容',
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
CREATE TABLE `lm_goods_comment` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`user_id`  int(11) NOT NULL ,
`goods_id`  int(11) NOT NULL ,
`is_buyer`  int(1) NOT NULL DEFAULT 0 COMMENT '是否为卖家' ,
`create_time`  int(11) NULL ,
`update_time`  int(11) NULL ,
`level`  int(1) NOT NULL DEFAULT 0 COMMENT '评星等级' ,
`is_show`  int(1) NOT NULL DEFAULT 1 COMMENT '是否显示' ,
`user_name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '用户名称' ,
`user_img`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '用户头像' ,
`is_delete`  int(1) NOT NULL DEFAULT 0 COMMENT '是否删除' ,
`content`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容' ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
;


//对账单
CREATE TABLE `lm_account_statement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL COMMENT '供应商id',
  `create_time` int(11) DEFAULT NULL COMMENT '生成时间',
  `balance` float(11,2) DEFAULT '0.00' COMMENT '余额',
  `type` int(1) NOT NULL DEFAULT '1' COMMENT '对账单类型 1 日对账单 2 月对账单',
  `income` float(11,2) DEFAULT '0.00' COMMENT '收入',
  `expend` float(11,2) DEFAULT '0.00' COMMENT '支出',
  `income_count` int(11) DEFAULT '0' COMMENT '收入笔数',
  `expend_count` int(11) DEFAULT '0' COMMENT '支出笔数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

//商品表增加 规格参数字段
ALTER TABLE  `lm_goods` ADD  `my_parameter` TEXT NOT NULL COMMENT  '规格参数' AFTER  `sku`;

CREATE TABLE `lm_poster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '海报名称',
  `setting` text COMMENT '设置内容',
  `is_use` int(1) NOT NULL DEFAULT '1' COMMENT '是否可使用',
  `create_time` int(10) DEFAULT NULL,
  `update_time` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

//用户提现表
DROP TABLE IF EXISTS `lm_withdraw_deposit`;
CREATE TABLE `lm_withdraw_deposit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `openid` varchar(255) DEFAULT NULL,
  `nickname` varchar(255) DEFAULT NULL COMMENT '用户昵称',
  `money` float(11,2) DEFAULT NULL COMMENT '提现金额',
  `status` int(11) DEFAULT NULL COMMENT '审核状态  1未审核  2审核不通过  3审核通过',
  `application_time` int(11) DEFAULT NULL COMMENT '申请时间',
  `check_time` int(11) DEFAULT NULL COMMENT '审核时间',
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

//商户号配置表
DROP TABLE IF EXISTS `lm_merchant_conf`;
CREATE TABLE `lm_merchant_conf` (
  `conf_id` int(11) NOT NULL AUTO_INCREMENT,
  `wx_uid` int(11) DEFAULT NULL COMMENT '对应微信表id',
  `merchant` varchar(255) DEFAULT NULL COMMENT '商户号',
  `apiclient_cert` varchar(255) DEFAULT NULL COMMENT '证书cert',
  `apiclient_key` varchar(255) DEFAULT NULL COMMENT '证书key',
  `create_time` int(11) DEFAULT NULL COMMENT '添加时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`conf_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

//增加字段
ALTER TABLE  `lm_order_goods` ADD  `user_message` VARCHAR( 255 )  NULL COMMENT  '留言备注' AFTER  `sku`


ALTER TABLE `lm_spec`
ADD COLUMN `goods_id`  int(11) NOT NULL DEFAULT 0 COMMENT '商品id' AFTER `search_index`;
ALTER TABLE `lm_spec_goods_price`
ADD COLUMN `weight`  int(11) NULL DEFAULT 0 COMMENT '重量' AFTER `price`;


ALTER TABLE `lm_coupon_code`
ADD COLUMN `g_code_order_id`  int(11) NULL DEFAULT 0 COMMENT 'G码支付订单id' AFTER `update_time`;
//退货
ALTER TABLE  `lm_return_goods` ADD  `refund_money` float(11,2) NOT NULL COMMENT  '退款金额' AFTER  `remark`


CREATE TABLE `lm_goods_check` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT NULL COMMENT '商品id',
  `admin_id` int(11) DEFAULT NULL COMMENT '供应商id',
  `check` int(2) DEFAULT NULL COMMENT '审核状态 0未审核 1已审核 2已下架',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

ALTER TABLE  `lm_admin` ADD  `phone` VARCHAR( 20 ) NOT NULL COMMENT  '手机号码' AFTER  `email`
ALTER TABLE  `lm_admin` ADD  `headerpic` VARCHAR( 255 )  NULL COMMENT  '用户头像' AFTER  `phone`


CREATE TABLE `lm_admin_withdrawals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `money` float(11,2) DEFAULT NULL COMMENT '提现金额',
  `admin_id` int(11) DEFAULT NULL,
  `mode` int(2) DEFAULT NULL COMMENT '提现类型 1对私账户 2对公账户',
  `bank` varchar(100) DEFAULT NULL COMMENT '开户行',
  `card_number` varchar(50) DEFAULT NULL COMMENT '银行帐号',
  `bank_name` varchar(50) DEFAULT NULL COMMENT '开卡人',
  `state` int(2) DEFAULT NULL COMMENT '状态 0未处理 1已处理 2驳回',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;



ALTER TABLE `lm_admin`
ADD COLUMN `suppliers_level`  int(1) NOT NULL DEFAULT 0 COMMENT '供应商等级' AFTER `role_id`,
ADD COLUMN `amount`  float(11,2) NULL DEFAULT 0 COMMENT '余额' AFTER `suppliers_level`,
ADD COLUMN `transaction_amount`  float(11,2) NULL DEFAULT 0 COMMENT '累计销量' AFTER `amount`,
ADD COLUMN `withdrawals_amount`  float(11,2) NULL DEFAULT 0 COMMENT '提现金额' AFTER `transaction_amount`,
ADD COLUMN `amount_refresh_time`  int(11) NULL COMMENT '余额刷新时间' AFTER `withdrawals_amount`;



ALTER TABLE `lm_order_goods`
ADD COLUMN `goods_postage`  float(11,2) NOT NULL COMMENT '订单邮费' AFTER `goods_num`;

ALTER TABLE `lm_order`
ADD COLUMN `admin_list`  varchar(255) NOT NULL DEFAULT '[0]' COMMENT '供应商列表' AFTER `is_distribut`;

ALTER TABLE `lm_return_goods`
ADD COLUMN `admin_id`  int(11) NOT NULL DEFAULT 0 COMMENT '供应商id' AFTER `order_id`;

ALTER TABLE `lm_logistics`
ADD COLUMN `admin_id`  int(11) NOT NULL DEFAULT 0 COMMENT '供应商id' AFTER `log_time`;


ALTER TABLE `lm_admin`
ADD COLUMN `nickname`  varchar(255) NULL COMMENT '帐号昵称' AFTER `role_id`;

ALTER TABLE `lm_goods`
ADD COLUMN `goods_pv`  int(11) NOT NULL DEFAULT 0 AFTER `my_parameter`,
ADD COLUMN `goods_uv`  int(11) NOT NULL DEFAULT 0 AFTER `goods_pv`;

DROP TABLE IF EXISTS `lm_goods_uv`;
CREATE TABLE `lm_goods_uv` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `goods_id` int(11) DEFAULT NULL COMMENT '商品id',
  `create_time` int(11) DEFAULT NULL COMMENT '插入时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `lm_goods_pv`;
CREATE TABLE `lm_goods_pv` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sum` int(11) DEFAULT NULL COMMENT '总数',
  `goods_id` int(11) DEFAULT NULL COMMENT '商品id',
  `create_time` int(11) DEFAULT NULL COMMENT '插入时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

ALTER TABLE `lm_admin`
ADD COLUMN `goods_limit`  int(11) NOT NULL DEFAULT 0 COMMENT '商品发布限制' AFTER `amount_refresh_time`,
ADD COLUMN `point_number`  float(11,2) NOT NULL DEFAULT 1.00 COMMENT '积分比例' AFTER `goods_limit`;
ALTER TABLE `lm_admin`
ADD COLUMN `point`  float(11,2) NULL DEFAULT 0 COMMENT '当前积分' AFTER `point_number`,
ADD COLUMN `transaction_point`  float(11,2) NULL DEFAULT 0 COMMENT '累计积分' AFTER `point`;

CREATE TABLE `lm_admin_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '后台用户id',
  `point` float(11,2) DEFAULT '0.00' COMMENT '积分数值',
  `create_time` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `flow` varchar(30) DEFAULT '' COMMENT '流水',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `lm_return_goods`
ADD COLUMN `result`  int(1) NOT NULL DEFAULT 0 COMMENT '退货退款结果  1 成功 2 拒绝' AFTER `refund_money`;


ALTER TABLE `lm_order_goods`
MODIFY COLUMN `spec_key`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '商品规格key' AFTER `give_integral`,
MODIFY COLUMN `spec_key_name`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '规格对应的中文名字' AFTER `spec_key`;

ALTER TABLE `lm_order`
MODIFY COLUMN `user_note`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '用户备注' AFTER `discount`;



ALTER TABLE `lm_coupon`
ADD COLUMN `use_type`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '使用类型 0 固定日期  1 动态时间' AFTER `is_discount`,
ADD COLUMN `limit_day`  int(11) NOT NULL DEFAULT 7 COMMENT '限制天数' AFTER `use_type`;

ALTER TABLE `lm_coupon_list`
ADD COLUMN `receive_time`  int(11) NOT NULL DEFAULT 0 COMMENT '领取时间' AFTER `send_time`;


-- ----------------------------
-- Table structure for lm_cron_list
-- ----------------------------
DROP TABLE IF EXISTS `lm_cron_list`;
CREATE TABLE `lm_cron_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) DEFAULT NULL,
  `value` int(11) NOT NULL DEFAULT '0' COMMENT '值 时间戳',
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of lm_cron_list
-- ----------------------------
INSERT INTO `lm_cron_list` VALUES ('1', 'min', '0', 'Order');
INSERT INTO `lm_cron_list` VALUES ('2', 'min', '0', 'Supplier');
INSERT INTO `lm_cron_list` VALUES ('3', 'hour', '0', 'Push');

ALTER TABLE `lm_coupon_list`
ADD COLUMN `plugin_name`  varchar(255) NULL COMMENT '插件名字' AFTER `receive_time`,
ADD COLUMN `plugin_order_id`  int(11) NULL COMMENT '插件order表id' AFTER `plugin_name`;




ALTER TABLE `lm_coupon`
MODIFY COLUMN `is_discount`  tinyint(1) NOT NULL COMMENT '券类型 0 优惠券 1 折扣券 2 买一送一券 3 第三方展示券' AFTER `use_code`;



ALTER TABLE `lm_coupon`
ADD COLUMN `goods_id`  int(11) NOT NULL DEFAULT 0 COMMENT '商品id' AFTER `add_time`,
ADD COLUMN `is_appoint`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否制定商品' AFTER `goods_id`;




ALTER TABLE `lm_goods`
ADD COLUMN `hide_goods_content`  text NULL COMMENT '特殊内容' AFTER `goods_remark`;

ALTER TABLE `lm_goods`
ADD COLUMN `goods_subtitle`  varchar(120) NULL COMMENT '商品副标题' AFTER `goods_name`,
ADD COLUMN `goods_label`  varchar(120) NULL COMMENT '商品标签' AFTER `goods_subtitle`;



ALTER TABLE `lm_coupon`
ADD COLUMN `remarks`  varchar(255) NULL COMMENT '备注' AFTER `name`;



ALTER TABLE `lm_users`
ADD COLUMN `experience`  int(11) NOT NULL DEFAULT 0 COMMENT '经验值' AFTER `frozen_money`;

ALTER TABLE `lm_users`
ADD COLUMN `upgrade_time`  int(11) NULL COMMENT '升级时间' AFTER `experience`;
  
  
  
CREATE TABLE `lm_points_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `create_time` int(10) DEFAULT NULL,
  `value` int(11) NOT NULL DEFAULT '0' COMMENT '更改数值',
  `text` varchar(255) DEFAULT NULL COMMENT '备注',
  `before_points` int(11) NOT NULL DEFAULT '0' COMMENT '改变前的数值',
  `after_points` int(11) NOT NULL DEFAULT '0' COMMENT '改变后的数值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `lm_users`
ADD COLUMN `user_points`  int(11) NOT NULL DEFAULT 0 COMMENT '积分' AFTER `user_money`;

ALTER TABLE `lm_users`
ADD COLUMN `last_buy_time`  int(10) NULL COMMENT '最后购买时间' AFTER `reg_time`;

ALTER TABLE `lm_users`
ADD COLUMN `points_clear_time`  int(11) NULL DEFAULT NULL COMMENT '积分清除时间' AFTER `frozen_money`;


ALTER TABLE `lm_users`
MODIFY COLUMN `pay_points`  decimal(10,2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '消费积分' AFTER `distribut_money`;

ALTER TABLE `lm_points_log`
MODIFY COLUMN `value`  decimal(11,2) NOT NULL DEFAULT 0 COMMENT '更改数值' AFTER `create_time`,
MODIFY COLUMN `before_points`  decimal(11,2) NOT NULL DEFAULT 0 COMMENT '改变前的数值' AFTER `text`,
MODIFY COLUMN `after_points`  decimal(11,2) NOT NULL DEFAULT 0 COMMENT '改变后的数值' AFTER `before_points`;


  ";