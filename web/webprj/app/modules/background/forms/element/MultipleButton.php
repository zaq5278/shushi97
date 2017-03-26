<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;

class MultipleButton extends CustomElement
{
    private $optionsValues = null;
    public function __construct($name, $options = null, array $attributes = null) {
        $this->optionsValues = $options;
        parent::__construct($name, $attributes);
    }

    public function render($attributes = null)
    {
        $html = "";
        if (isset($this->optionsValues) && is_array($this->optionsValues)) {
            foreach($this->optionsValues as $option) {
                $btn_type = isset($option["type"]) ? $option["type"] : "button";
                $btn_link = isset($option["link"]) ? $option["link"] : "#";
                $btn_class = isset($option["class"]) ? $option["class"] : "";
                $btn_value = isset($option["value"]) ? $option["value"] : "";
                $btn_onclick = isset($option["onclick"]) ? $option["onclick"] : "";
                if ($btn_type == "submit") {
                    $btnHtml = "<input type='submit' class='btn btn-default " . $btn_class . "' value=" . $btn_value . ">";
                } else {
                    $btnHtml = "<a href='" . $btn_link . "' class='btn btn-default " . $btn_class . "' " . $btn_onclick . ">" . $btn_value  . "</a>";
                }
                $html .= $btnHtml;
                $html .= "&nbsp";
            }
        }
        return $html;
    }
}