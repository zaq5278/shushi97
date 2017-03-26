<?php

namespace App\Wap;

use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\DiInterface;
use Phalcon\Loader;
use Plugin\Weixin\Weixin;
use Plugin\kefu\Rongyun;
use Plugin\Alibaba\Alibaba;
use Plugin\SMS\SMS;


class Module implements ModuleDefinitionInterface
{
    public function registerAutoloaders(DiInterface $di=null) {
        $loader = new Loader();
        $loader->registerNamespaces(
            array(
                'App\Wap\Controllers' => ROOT_DIR . '/app/modules/wap/controllers'
            )
        );
        $loader->register();
    }

    /**
     * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
     */
    public function registerServices(DiInterface $di) {
        // Registering a dispatcher
        $di->set('dispatcher', function () {
            // 请求分发服务
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace(ROOT_DIR."App\Wap\Controllers");
            return $dispatcher;
        });
        // Registering the view component
        $di->set('view', function() {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/views/');
            $voltPath = APP_CACHE_DIR . '/volt/foreground/';
            if (!is_dir($voltPath)) {
                mkdir($voltPath, 0755, true);
            }

            $view->registerEngines(array(
                '.phtml' => function ($view, $di) use($voltPath) {
                    $volt = new VoltEngine($view, $di);
                    $volt->setOptions(array(
                        'compiledPath' => $voltPath,
                        'compiledSeparator' => '_'
                    ));
                    return $volt;
                }
            ));

            return $view;
        });

        // register the wxsdk
        $config = $di->get('config');
        $di->set("weixin", function() use($config) {
			return new Weixin($config->wx->appid, $config->wx->appsecret);
        });

        $di->set("rongyun", function() use($config) {
            return new Rongyun($config->kf->appKey, $config->kf->appsecret);
        });

        $di->set("alibaba", function() use($config) {
            return new Alibaba($config->ali->appid, $config->ali->appsecret);
        });

        $di->set("sms", function() {
            return new SMS();
        });
    }
        
}
