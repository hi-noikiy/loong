<?php

return [
    'HTML_CACHE_ON' => true, // 开启静态缓存
    'HTML_CACHE_TIME' => 60, // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX' => '.shtml', // 设置静态缓存文件后缀
    'HTML_CACHE_RULES' => [
        '*' => ['{$_SERVER.REQUEST_URI|md5}'],
    ]
];
