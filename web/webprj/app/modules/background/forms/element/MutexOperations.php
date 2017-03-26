<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;

class MutexOperations extends CustomElement
{
    private $_keyExtra, $_operations;
    public function __construct($name, $keyExtra, $operations, array $attributes = null) {
        $this->_keyExtra = $keyExtra;
        $this->_operations = $operations;
        parent::__construct($name, $attributes);
    }

    public function render($attributes = null)
    {
        return \Plugin\Tags\ExTags::MutexOperations($this->_name, $this->_keyExtra, $this->_operations);
    }
}