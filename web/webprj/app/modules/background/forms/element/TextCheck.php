<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;
use Phalcon\Forms\Element\Check;

class TextCheck extends Check
{
    private $_text = "";

    public function setText($text) {
        $this->_text = '<span> ' . $text . '</span>';
    }

    public function render($attributes = null)
    {
        $html = parent::render($attributes);
        $html .= $this->_text;
        return $html;
    }
}