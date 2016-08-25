

ALTER TABLE `tp_goods`
ADD COLUMN `admin_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '供应商id' AFTER `goods_id`;

ALTER TABLE `tp_cart`
ADD COLUMN `admin_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '供应商id' AFTER `id`;

ALTER TABLE `tp_order_goods`
ADD COLUMN `admin_id`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '供应商id' AFTER `rec_id`;

