<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;

class TagSelector extends CustomElement
{
    private $_candidates, $_maxChosen, $_selfSubmit, $_buttonDisplay;
    public function __construct($name, $candidates, $maxChosen = -1, $selfSubmit = false, $buttonDisplay = "提交", array $attributes = null) {
        $this->_candidates = $candidates;
        $this->_maxChosen = $maxChosen;
        $this->_selfSubmit = $selfSubmit;
        $this->_buttonDisplay = $buttonDisplay;
        parent::__construct($name, $attributes);
    }

    public function render($attributes = null)
    {
        return \Plugin\Tags\ExTags::TagSelector("", $this->_name, $this->_candidates, $this->_maxChosen, $this->_selfSubmit, $this->_buttonDisplay);
    }
}