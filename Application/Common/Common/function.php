<?php


@require_once "Function/base.php";
@require_once "Function/order.php";
@require_once "Function/user.php";
@require_once "Function/verify.php";
@require_once "Function/goods.php";
@require_once "Function/email.php";
@require_once "Function/service.php";


function dd($x) {
    echo "<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\">";
    echo "<pre>";
    print_r($x);
    echo "</pre>";
    die;
}