<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;

class MultipleCheck extends CustomElement
{
    private $optionsValues = null;
    public function __construct($name, $options = null, array $attributes = null) {
        $this->optionsValues = $options;
        //array(array("label"=>"首页", "id"=>"1"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"), array("label"=>"查看", "id"=>"2"));
        parent::__construct($name, $attributes);
    }

    public function render($attributes = null)
    {
        $html = null;
        if (isset($this->optionsValues) && is_array($this->optionsValues)) {
            foreach($this->optionsValues as $option) {
                if (!isset($option['label'])) {
                    continue;
                }
                $defaultAttributes = $this->_attributes;
                if (!empty($attributes)) {
                    if (gettype($defaultAttributes) == "array") {
                        $defaultAttributes = array_merge($defaultAttributes, $attributes);
                    } else {
                        $defaultAttributes = $attributes;
                    }
                }

                if (isset($defaultAttributes) && isset($defaultAttributes['class'])) {
                    $html .= "<label class=" . $defaultAttributes['class'] . " style='margin: auto 10px auto 0;'>";
                } else {
                    $html .= "<label style='margin: auto 10px auto 0;'>";
                }

                $name = "mce_default";
                if (isset($defaultAttributes) && isset($defaultAttributes['name'])) {
                    $name = $defaultAttributes['name'];
                }

                $html .= "<input type='checkbox'";

                if (isset($option['id'])) {
                    $html .= " name=" . $name . "[" . $option['id'] . "] id=" . $option['id'];
                }

                if (isset($option['check'])) {
                    $html .= " checked=checked";
                }

                $html .= ">";
                $html .= $option['label'];
                $html .= "</label>";
            }
        }
        return $html;
    }
}