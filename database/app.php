<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2018/11/20
 * Time: 8:30
 */

require_once __DIR__ . '/core/Lib/Autoload.php';

use Core\Lib\Autoload;

$autoload = new Autoload();

$autoload->register([
    'class' => [
        'Core\\' => __DIR__ . '/core/'
    ] ,
    'file'  => [
        __DIR__ . '/core/Function/base.php' ,
        __DIR__ . '/core/Function/array.php' ,
        __DIR__ . '/core/Function/file.php' ,
        __DIR__ . '/core/Function/number.php' ,
        __DIR__ . '/core/Function/string.php' ,
        __DIR__ . '/core/Function/url.php' ,
        __DIR__ . '/core/Function/time.php'
    ]
]);