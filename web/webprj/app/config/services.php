<?php

use Phalcon\Di,
    Phalcon\DI\FactoryDefault,
    Phalcon\Mvc\Router,
    Phalcon\Mvc\Url as UrlResolver,
    Phalcon\Http\Response\Cookies,
    Phalcon\Db\Adapter\Pdo\Mysql as DBAdapter,
    Phalcon\Db\Profiler as DbProfiler,
    Phalcon\Events\Manager as EventManager,
    Phalcon\Config\Adapter\Php as PhpObject,
    Phalcon\Session\Adapter\Files as Session,
    Plugin\Upload\Upload,
    Plugin\Core\QSTBaseLogger;

$di = new FactoryDefault();

/**
 * Register the global configuration as config
 */
$di->setShared('config', $config);

/**
 * logger service
 */
$di->setShared('logger', function (){
    return new QSTBaseLogger(LOG_DIR, "app");
});
$di->setShared('refundLogger', function (){
    return new QSTBaseLogger(LOG_DIR, "refundOrder");
});
/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->baseUri);
    return $url;
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function() {
    $session = new Session();
    $session->start();
    return $session;
});

/**
 * Register cookies service
 */
$di->setShared('cookies', function() {
    $cookies = new Cookies();
    $cookies->useEncryption(false);
    return $cookies;
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () use($config) {
    $dbconfig = $config->database;
    $connection = new DBAdapter(
        array(
            "host" => $dbconfig['host'],
            "port" => $dbconfig['port'],
            "username" => $dbconfig['username'],
            "password" => $dbconfig['password'],
            "dbname" => $dbconfig['dbname'],
            "charset" => $dbconfig['charset']
        )
    );

    // 根据配置决定是否启动数据库执行分析
    if ($config->runtime != 'release') {
        $eventsManager = new EventManager();
        $connection->setEventsManager($eventsManager);

        // 分析底层sql性能，并记录日志
        $profiler = new DbProfiler();
        $eventsManager->attach('db', function ($event, $connection) use ($profiler) {
            if ($event->getType() == 'beforeQuery') {
                // 在sql发送到数据库前启动分析
                $profiler->startProfile($connection->getSQLStatement());
            }

            if ($event->getType() == 'afterQuery') {
                // 在sql执行完毕后停止分析
                $profiler->stopProfile();
                // 获取分析结果
                $profile = $profiler->getLastProfile();
                $sql = $profile->getSQLStatement();
                $executeTime = $profile->getTotalElapsedSeconds();
                // 写入日志
               // $logger = Di::getDefault()->get('logger');
               // $logger->log("{$sql} {$executeTime}");
            }
        });
    }

    return $connection;
});

/**
 * plugin - upload
 */
$di->setShared('upload', function() use($config) {
    $upload = new Upload(ROOT_DIR . "/", UPLOAD_DIR_RELATIVE . "/");
    $upload->setUriPath($config->baseUri);
    return $upload;
    //return new Upload(UPLOAD_DIR);
});

