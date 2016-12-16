<?php
return array(
    'VIEW_PATH'       =>'./Template/index/', // 改变某个模块的模板文件目录
//    'DEFAULT_THEME'    =>'lepur', // 模板名称
    'DEFAULT_THEME'    =>'default', // 模板名称

    'TMPL_PARSE_STRING'=>array(
        '__INDEX__'        => '/Template/index/default/Static',
        '__PUBLIC__'        => '/Public',
        '__STATIC__'         => '/Template/index/default/Static',
    ),
    //'DATA_CACHE_TIME'=>60, // 查询缓存时间
);