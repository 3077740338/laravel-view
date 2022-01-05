<?php
/*
|----------------------------------------------------------------------------
| TopWindow [ Internet Ecological traffic aggregation and sharing platform ]
|----------------------------------------------------------------------------
| Copyright (c) 2006-2019 http://yangrong1.cn All rights reserved.
|----------------------------------------------------------------------------
| Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
|----------------------------------------------------------------------------
| Author: yangrong <yangrong2@gmail.com>
|----------------------------------------------------------------------------
| 模板设置
|----------------------------------------------------------------------------
*/
return [
    // 模板路径
    'paths' => [
        //
        resource_path('views'),
    ],
    // 默认模板文件后缀
    'suffix' => 'htm',
    //默认模板缓存路径
    'compiled' => env('VIEW_COMPILED_PATH', realpath(storage_path('framework/views'))),
    // 是否去除模板文件里面的html空格与换行
    'strip_space' => false,
    // 是否开启模板编译缓存,设为false则每次都会重新编译
    'tpl_cache' => true,
];