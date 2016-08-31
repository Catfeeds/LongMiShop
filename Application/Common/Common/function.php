<?php


require_once "Function/base.php";


function dd($x) {
    echo "<meta http-equiv=\"content-type\" content=\"text/html;charset=utf-8\">";
    echo "<pre>";
    print_r($x);
    echo "</pre>";
    die;
}