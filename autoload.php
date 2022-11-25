<?php
// Use in an environment without composer
// 在非composer 的环境下导入该文件即可
if(!defined('IRY_AUTOLOAD__IRY_CLI')){
    define('IRY_AUTOLOAD__IRY_CLI',__FILE__);

    if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 50400) {
        exit(__FILE__." PHP version error (version requirement >=5.4)");
    }


    spl_autoload_register(function ($class) {
        $_classFile = str_replace('\\','/',rtrim($class,'\\'));
        if(strpos($_classFile,'iry/cli')===0 && !class_exists($class,false)){
            include __DIR__.'/src/'.str_replace(':iry/cli/','', ':'.$_classFile).'.php';
        }
    });
}
