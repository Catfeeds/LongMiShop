<?php


@include_once "Function/base.php";
@include_once "Function/order.php";
@include_once "Function/user.php";
@include_once "Function/verify.php";
@include_once "Function/goods.php";
@include_once "Function/email.php";
@include_once "Function/service.php";
@include_once "Function/address.php";
@include_once "Function/log.php";
@include_once "Function/weChat.php";
@include_once "Function/callback.php";
@include_once "Function/coupon.php";
@include_once "Function/exchange.php";
@include_once "Function/gift.php";
@include_once "Function/kuaidi.php";
@include_once "Function/addons.php";

function dd($x) {
    echo "<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\">";
    echo "<pre>";
    print_r($x);
    echo "</pre>";
    die;
}