<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/4
 * Time: 13:53
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
    'Plugin\Rbac' => __DIR__,
    'Plugin\Rbac\Models' => __DIR__ . '/models'
))->register();
