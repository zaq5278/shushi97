<?php

/**
 * 注册自动加载模块
 * ROOT_DIR 定义在index.php中
 */
$loader = new \Phalcon\Loader();

/**
 * 注册命名空间
 */
$loader->registerNamespaces(array(
	'Plugin\Core' => ROOT_DIR . '/app/plugin/core',
	'App\Models' => ROOT_DIR . '/app/models'
))->register();

