<?php
return array(
    'VIEW_PATH'       =>'./Template/index/', // 改变某个模块的模板文件目录
    'DEFAULT_THEME'    =>'lepur', // 模板名称 lepur
//    'DEFAULT_THEME'    =>'default', // 模板名称 default

    'TMPL_PARSE_STRING'=>array(
//        '__INDEX__'        => '/Template/index/default/Static',
//        '__PUBLIC__'        => '/Public',
//        '__STATIC__'         => '/Template/index/default/Static',
        '__INDEX__'        => '/Template/index/lepur/Static',
        '__PUBLIC__'        => '/Public',
        '__STATIC__'         => '/Template/index/lepur/Static',
    ),
    'HTML_CACHE_ON'     =>   false, // 开启静态缓存
    'HTML_CACHE_TIME'   =>   60,   // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX'  =>   '.html', //设置静态缓存文件后缀
//    'DATA_CACHE_TIME'=>60, // 查询缓存时间
);