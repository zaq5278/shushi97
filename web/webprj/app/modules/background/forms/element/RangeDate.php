<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/10/11
 * Time: 20:42
 */

namespace App\Background\Forms\Element;
use Phalcon\Di;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Form;
use Plugin\Core\QSTBaseLogger;

class RangeDate extends CustomElement
{
    private $_start;
    private $_end;
    public function __construct($name_start, $name_end, array $attributes = null) {
        parent::__construct(null, $attributes);
        $this->_start = new Date($name_start, $attributes);
        $this->_end = new Date($name_end, $attributes);
    }

    public function setForm(Form $form) {
        $this->_start->setForm($form);
        $this->_end->setForm($form);
    }

    /** 如果实在2.1以下版本运行，请注释掉 Array*/
    /* public function prepareAttributes(Array$attributes = NULL, $useChecked = NULL) {
         $this->_start->prepareAttributes($attributes, $useChecked);
         $this->_end->prepareAttributes($attributes, $useChecked);
     }*/

    public function render($attributes = null) {
        $classDefault = " form-control err";
        if (!isset($attributes["class"])) {
            $attributes["class"] = $classDefault;
        } else {
            $attributes["class"]  .= $classDefault;
        }

        $html = "";
        $html .= '<div class="input-group">';
        $html .= $this->_start->render($attributes);
        $html .= '<span class="input-group-addon">to</span>';
        $html .= $this->_end->render($attributes);
        $html .= '</div>';
        return $html;
    }
}