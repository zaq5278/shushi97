<?php
/**
 * Created by PhpStorm.
 * User: caohailiang
 * Date: 2016/12/8
 * Time: 16:44
 */

namespace Plugin\Core;


use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\View\Exception;

class QstBaseVolt extends Volt
{
    public function callMacro($name, array $arguments = array())
    {
        if (!$this->_macros[$name]){
            throw new Exception("Macro '" . $name . "' does not exist");
        }
        return call_user_func($this->_macros[$name], $arguments);
    }
}