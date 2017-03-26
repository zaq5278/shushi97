<?php

error_reporting(E_ERROR);
use \Phalcon\Mvc\Application;
use \Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use \Plugin\Core\QSTBaseException;
use Phalcon\Di;

try {
    /**
     * Define some useful constants
     */
    define('ROOT_DIR', dirname(__DIR__));
    define('APP_DIR', ROOT_DIR . '/app');
    define('APP_CACHE_DIR', APP_DIR .'/cache');
    define('API_DIR', ROOT_DIR .'/app/plugin');
    define('LOG_DIR', ROOT_DIR . '/log');
    define('UPLOAD_DIR_RELATIVE', 'files');
    define('UPLOAD_DIR', ROOT_DIR . "/" . UPLOAD_DIR_RELATIVE);
    include_once APP_DIR . '/config/def.php';
    include_once APP_DIR . '/config/errcode.php';

    /**
     * Read the configuration
     */
    $config = include APP_DIR . '/config/config.php';
    
    /**
     * Read plugin
     */
    include_once APP_DIR . '/plugin/plugin.php';

    /**
     * Read auto-loader
     */
    include_once APP_DIR . '/config/loader.php';

    /**
     * Read vendor
     */
    include_once APP_DIR . '/vendor/autoload.php';

    /**
     * Read services
     */
    include_once APP_DIR . '/config/services.php';

    /**
     * Handle the request
     */
    $application = new Application($di);

    /**
     * init modules
     */
    include_once APP_DIR . '/config/modules.php';

    /**
     * Registering a router
     */
    include_once APP_DIR . "/config/routers.php";

    $application->getDI()->get('logger')->log("IN==>[".$_SERVER['REQUEST_METHOD']."][". $_SERVER['QUERY_STRING']."]");

    echo $application->handle()->getContent();

} catch (Exception $exception) {
    if (Di::getDefault()->get('config')->runtime == "release") {
        $cssOuts = [];
        $cssOuts[] =_LIBS_."libs/js3party/bootstrap/3.3.6/bootstrap.min.css";
        $cssOuts[] = _LIBS_."libs/js3party/AdminEx/css/style.css";
        $cssOuts[] = _LIBS_."libs/js3party/AdminEx/css/style-responsive.css";
        $jsOuts = [];
        $jsOuts[] = _LIBS_."libs/js3party/jquery/jquery-2.2.4.min.js";
        $jsOuts[] = _LIBS_."libs/js3party/bootstrap/3.3.6/bootstrap.min.js";
        $error = [];
        // $error['home'] = Di::getDefault()->get("url")->get($module . "/index/index");
        if ($exception instanceof DispatchException) {
            $error['title'] = "404页面";
            $error['image'] = Di::getDefault()->get("url")->get("public/404-error.png");
            $error['tip_head'] = "页面不存在";
            $error['tip_body'] = "您访问了不存在的页面";
            echo include "./error-default.html";
        } else if ($exception instanceof QSTBaseException) {
            if ($exception->getCode() == 403) {
                $error['title'] = "拒绝访问";
                $error['image'] = Di::getDefault()->get("url")->get("public/403-error.png");
                $error['tip_head'] = "拒绝访问";
                $error['tip_body'] = '很抱歉,您没有该访问权限, 请联系管理员';
                echo include "/error-default.html";
            }
        } else {
            $error['title'] = "出错了";
            $error['image'] = Di::getDefault()->get("url")->get("public/500-error.png");
            $error['tip_head'] = "系统出错了";
            $error['tip_body'] = '很抱歉,系统出问题了,请稍后重试,或者<a href="#">联系我们</a>';
            echo include "/error-default.html";
        }
    } else {
        echo $exception->getMessage();
        echo $exception->getTraceAsString();
    }
    \Plugin\Core\QSTBaseLogger::getDefault(LOG_DIR)->log($exception->getMessage(), \Phalcon\Logger::ERROR);
    \Plugin\Core\QSTBaseLogger::getDefault(LOG_DIR)->log($exception->getTraceAsString(), \Phalcon\Logger::ERROR);
}

