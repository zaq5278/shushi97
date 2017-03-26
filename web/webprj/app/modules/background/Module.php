<?php

namespace App\Background;

use Phalcon\Mvc\View;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Plugin\Core\QstBaseVolt as VoltEngine;
use Phalcon\DiInterface;
use Phalcon\Loader;
use Plugin\Login\Login;
use Plugin\Rbac\Rbac;
use Plugin\Tags\ExTags;
use Plugin\Tags\GroupLayout;
use Plugin\Tags\StaticResource;
use Plugin\Weixin\Weixin;
use Plugin\rqcode\rqcode;
use Plugin\SMS\SMS;
use Plugin\excel\Excel;

class Module implements ModuleDefinitionInterface
{
    public function registerAutoloaders(DiInterface $di=null) {
        $loader = new Loader();
        $loader->registerNamespaces(array(
            'App\Background\Controllers' => '../app/modules/background/controllers',
            'App\Background\Forms' => '../app/modules/background/forms',
            'App\Background\Forms\Element' => '../app/modules/background/forms/element'
        ))->register();
    }

    /**
    * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
    */
    public function registerServices(DiInterface $di) {
        // Registering a dispatcher
        $di->set('dispatcher', function () {
            $dispatcher = new Dispatcher();
            $dispatcher->setDefaultNamespace("App\Background\Controllers");
            return $dispatcher;
        });
        // Registering the view component
        $di->setShared('view', function() {
            $view = new View();
            $view->setViewsDir(__DIR__ . '/views/');

            $voltPath = APP_CACHE_DIR . '/volt/background/';
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
		    
                    $compiler = $volt->getCompiler();
                    $compiler->addFunction(
                        'is_a',
                        function($resolvedArgs, $exprArgs) use ($compiler) {
                            // Resolve the first argument
                            $firstArgument = $compiler->expression($exprArgs[0]['expr']);
                            $secondArgument = $compiler->expression($exprArgs[1]['expr']);
                            return "is_a(" . $firstArgument . "," . $secondArgument . ")";
                        }
                    );
                    
                    $compiler->addFunction(
                        'max',
                        function($resolvedArgs, $exprArgs) use ($compiler) {
                            // Resolve the first argument
                            $firstArgument = $compiler->expression($exprArgs[0]['expr']);
                            $secondArgument = $compiler->expression($exprArgs[1]['expr']);
                            return "max(" . $firstArgument . "," . $secondArgument . ")";
                        }
                    );

                    $compiler->addFunction(
                        'min',
                        function($resolvedArgs, $exprArgs) use ($compiler) {
                            // Resolve the first argument
                            $firstArgument = $compiler->expression($exprArgs[0]['expr']);
                            $secondArgument = $compiler->expression($exprArgs[1]['expr']);
                            return "min(" . $firstArgument . "," . $secondArgument . ")";
                        }
                    );

                    $compiler->addFunction(
                        'ceil',
                        function($resolvedArgs, $exprArgs) use ($compiler) {
                            // Resolve the first argument
                            $firstArgument = $compiler->expression($exprArgs[0]['expr']);
                            return "ceil(" . $firstArgument . ")";
                        }
                    );
                    StaticResource::registerLibsVoltTags($compiler);
                    ExTags::registerVoltTags($compiler);
                    GroupLayout::registerVoltTags($compiler);
                    return $volt;
                }
            ));
            return $view;
        });

        // 注册权限管理服务
        $di->setShared("rbac", function() {
            return new Rbac('examples');
        });


        $di->setShared("login", function() {
            return new Login();
        });

        $di->set("rqcode", function(){
            return new rqcode();
        });

        $di->set("menu", function (){
            $roleid = $_SESSION['auth-identity']['profile'];
            $menu = [];
            if($roleid == 1){
                $menu = include __DIR__."/config/manage.php";
            }elseif($roleid == 3){
                $menu = include __DIR__."/config/franchisemenu.php";
            }elseif($roleid == 2){
                $menu = include __DIR__."/config/depotmenu.php";
            }
            if(empty($roleid)){
                $menu = include __DIR__."/config/menu.php";
            }
            return $menu;
        });

        // register the wxsdk
        $config = $di->get('config');
        $di->set("weixin", function() use($config) {
            return new Weixin($config->wx->appid, $config->wx->appsecret);
        });
        $di->set("sms", function() {
            return new SMS();
        });
        $di->set("Excel", function() {
            return new Excel();
        });
    }
}
