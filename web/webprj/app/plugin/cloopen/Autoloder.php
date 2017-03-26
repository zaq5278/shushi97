<?php
/**
 * Created by PhpStorm.
 * User: caohailiang
 * Date: 2016/8/30
 * Time: 19:43
 */

/**
 * 注册自动加载模块
 * ROOT_DIR 定义在index.php中
 */
$loader = new \Phalcon\Loader();

/**
 * 注册命名空间
 */
$loader->registerNamespaces(array(
    'Plugin\Cloopen' => __DIR__,
))->register();
