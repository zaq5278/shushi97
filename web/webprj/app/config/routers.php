<?php

$router = $di->get("router");
$router->setDefaultModule('home');
foreach ($application->getModules() as $key => $module) {
    $namespace = str_replace('Module', 'Controllers', $module["className"]);
    //$di->get("logger")->log($namespace . "----" . $key . "-----------" . $module["className"]);
    $router->add('/' . $key . '/:params', array(
        'module' => $key,
        'controller' => 'index',
        'action' => 'index',
        'params' => 1
    ))->setName($key);

    $router->add('/'. $key. '/([a-zA-Z][a-zA-Z0-9\_\-]*)/:params', array(
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 'index',
        'params' => 2
    ));

    $router->add('/'. $key . '/([a-zA-Z][a-zA-Z0-9\_\-]*)/([a-zA-Z][a-zA-Z0-9\_]*)/:params', array(
        'namespace' => $namespace,
        'module' => $key,
        'controller' => 1,
        'action' => 2,
        'params' => 3
    ));
}

$di->set("router", $router);

