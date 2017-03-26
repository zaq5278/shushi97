<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/10/13
 * Time: 10:05
 */

namespace App\Background\Forms\Element;


class Audio extends CustomElement
{
    public function __construct($name, array $attributes = null) {
        parent::__construct($name, $attributes);
    }

    public function render($attributes = null)
    {
//        $attrArray = $this->prepareAttributes($attributes);
//        $attrArray["value"] = null;
        $html = "";
        try {
            $srcString = $this->getValue();
            $srcArray = json_decode($srcString);

            foreach ($srcArray as $src) {
                $html .= "<div>";
                $html .= "<audio controls=\"controls\">";
                $html .= "浏览器不支持audio标签";
                $html .= "<source src='" . $src . "' type='audio/mp3' />";
                $html .= "</audio>";
                $html .= "</div>";
            }

        } catch (\Exception $e) {
            $this->logger->log("Audio::render()-" . $e->getMessage());
        }
        return $html;
    }
}