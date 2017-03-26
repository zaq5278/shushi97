<?php
/**
 * Created by PhpStorm.
 * User: caohailiang
 * Date: 2016/8/30
 * Time: 19:43
 */

namespace Plugin\Misc;

class ErrorDescriptions{
    static public function getErrorDesc($code){
        return isset($GLOBALS["ERRCODE"][$code]) ?$GLOBALS["ERRCODE"][$code] : "undefined error code";
    }
}