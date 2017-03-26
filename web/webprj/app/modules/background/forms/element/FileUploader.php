<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;
use Phalcon\Forms\Element\Check;

class FileUploader extends CustomElement
{
    public static function init($assert) {
        $assert->addCss(_LIBS_."libs/js3party/bootstrap-fileinput/4.3.5/css/fileinput.css", false);
        $assert->addJs(_LIBS_."libs/js3party/bootstrap-fileinput/4.3.5/js/fileinput.js", false);
        $assert->addJs(_LIBS_."libs/js3party/bootstrap-fileinput/4.3.5/js/locales/zh.js", false);
    }

    public function __construct($name, array $attributes = null) {
        parent::__construct($name, $attributes);
    }

    public function render($attributes = null)
    {
        $attr = $this->prepareAttributes($attributes);
        $attr['class'] = "mg-input-file file-loading" . " " . (isset($attr['class']) ? $attr['class'] : "");
        $id = isset($attr['id']) ? $attr['id'] : $attr[0];
        $name = isset($attr['name']) ? $attr['name'] : $id;
        $valueAttr = isset($attr['value']) ? " value='" . $attr['value'] . "' " : null;
        $requireAttr = isset($attr['required']) ? ' required="required" ' : "";
        $descrip = isset($attr['desc']) ? $attr['desc'] : '';
        unset($attr['required']);
        $attrString = $this->attributes2String($attr);

        $html = "";
        $html .= '<div class="mg-input-file-container">';
        $html .= '<input type="text" style="position:absolute;z-index: -1;bottom:0;" name="' . $name  .'"' . $valueAttr . $requireAttr . '>';
        $html .= '<input ' . $attrString . 'type="file">';
        $html .= $descrip."</div>";
        return $html;
    }
}

