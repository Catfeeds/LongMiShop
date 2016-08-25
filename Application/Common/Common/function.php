<?php


require_once "Function/base.php";


function dd($x) {
    echo "<pre>";
    print_r($x);
    echo "</pre>";
    die;
}