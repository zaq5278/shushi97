<?php
return new \Phalcon\Config(array(
    'database' => array(
            'host' => _HOST_,
            'port' => _PORT_,
            'username' => _USER_,
            'password' => _PSW_,
            'dbname' => _DB_,
            'charset' => 'utf8',
            'prefix' => ''
    ),
    'runtime' => 'debug', // debug or release
    'baseUri' =>_F_BASE_URL_,
    'cachePath' => APP_DIR . "/cache",
    'log' => array(
    ),
    "wx" => array(
        "appid" => _APPID_,
        "appsecret"=> _SECRET_,
        "ticket" => _TICKET_
    ),
    "ali" => array(
        "appid" => _ALIPAYPARTNER_,
        "appsecret"=> _ALIPAYKEY_
    ),
    "kf" => array(
        "appKey" => _RONGYUNKEY_,
        "appsecret"=> _RONGYUNSECRET_
)
));
