<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;
use Phalcon\Forms\Element\Check;

class RichEditor extends CustomElement
{
    public static function init($assert) {
        $assert->addJs(_LIBS_."libs/js3party/ueditor/1.4.3.3/ueditor.config.js", false);
        $assert->addJs(_LIBS_."libs/js3party/ueditor/1.4.3.3/ueditor.all.min.js", false);
        $assert->addJs(_LIBS_."libs/js3party/ueditor/1.4.3.3/lang/zh-cn/zh-cn.js", false);
    }

    public function __construct($name, array $attributes = null) {
        parent::__construct($name, $attributes);
    }

    public function render($attributes = null)
    {
        $attr = $this->prepareAttributes($attributes);
        if (!isset($attr['id'])) {
            $attr['id'] = $attr[0];
        }

        if (!isset($attr["name"])) {
            $attr["name"] = $attr['id'];
        }

        $attr['class'] = "mg-rich-editor" . " " . (isset($attr['class']) ? $attr['class'] : "");
        $value = isset($attr['value']) ? $attr['value'] : "";
        $attr['value'] = null;
        $attrString = $this->attributes2String($attr);

        $html = "";
        $html .= '<div>';
        $html .= '<script ' . $attrString . ' type="text/plain">' . (!is_null($value) ? $value : "") . '</script>';
        $html .= '</div>';
        return $html;
    }
}

