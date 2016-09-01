<?php
return array(

    'TMPL_PARSE_STRING'=>array(
        '__PUBLIC__'        => '/Public/index',
        '__MAIN__'        => '/Public',
    ),
    //默认错误跳转对应的模板文件
    'TMPL_ACTION_ERROR' => 'Public:dispatch_jump',
    //默认成功跳转对应的模板文件
    'TMPL_ACTION_SUCCESS' => 'Public:dispatch_jump',

//    'LOAD_EXT_CONFIG' => 'template',
);