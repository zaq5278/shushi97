<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/10/12
 * Time: 21:27
 */

namespace App\Background\Forms\Element;


class StaticText extends CustomElement
{
    public function __construct($name, array $attributes = null) {
        parent::__construct($name, $attributes);
    }

    public function render($attributes = null)
    {
        $attrArray = $this->prepareAttributes($attributes);
        $attrArray["value"] = null;
        $attrString = $this->attributes2String($attrArray);
        $html = "<div " . $attrString . ">";
        $html .= $this->getValue();
        $html .= "</div>";
        return $html;
    }
}