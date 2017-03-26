<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;

use Phalcon\Forms\Element;

abstract class CustomElement extends Element
{
    public function prepareAttributesEx($attributes = null, $class = false) {
        // _name 1. label 2. id, 3. name
        $attr = null;
        if (!empty($attributes) && !empty($this->_attributes)) {
            $attr = array_merge($this->_attributes ? $this->_attributes : [], $attributes ? $attributes : []);
            if (!empty($attributes["class"]) && !empty($this->_attributes['class'])) {
                $attr['class'] = $attributes["class"] . " " . $this->_attributes['class'];
            }
        } else {
            $attr = empty($attributes) ? $this->_attributes : $attributes;
        }

        if (!empty($class)) {
            $attr['class'] = $class . " " . (isset($attr['class']) ? $attr['class'] : "");
        }

        if (!isset($attr['id'])) {
            $attr['id'] = $this->_name;
        }

        if (!isset($attr["name"])) {
            $attr["name"] = $attr['id'];
        }

        $attrString = "";
        foreach ($attr as $key=>$value) {
            $attrString .= $key . '="' . $value . '"';
            $attrString .= " ";
        }

        return $attrString;
    }

    public function attributes2String(array $attr) {
        $attrString = "";
        foreach ($attr as $key=>$value) {
            if (!empty($key) && !empty($value)) {
                $attrString .= $key . '="' . $value . '"';
                $attrString .= " ";
            }
        }
        return $attrString;
    }
}
