<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;

class Picture extends CustomElement
{
    private $_srcDefault;
    public function __construct($name, $srcDefault = null, array $attributes = null) {
        parent::__construct($name, $attributes);
        $this->_srcDefault = $srcDefault;
    }

    public function render($attributes = null)
    {
        $html = "";
        try {
            $attr = $this->prepareAttributes($attributes);
            $attr["value"] = null;
            $imageString = $this->getValue();
            $imageArray = json_decode($imageString);
            if (is_null($imageArray)) {
                $imageArray[] = $imageString;
            }
            foreach ($imageArray as $image) {
                $attr["src"] = $image;
                $html .= $this->htmlImage($attr);
            }
        } catch (\Exception $e) {
            $this->logger->log("Picture::render()-" . $e->getMessage());
        }
        return $html;
    }

    private function htmlImage($attrArray) {
        $src = isset($attrArray["src"]) ? $attrArray["src"] : $this->_srcDefault;
        $attrString = $this->attributes2String($attrArray);
        $htmlImage = "<div class='col-xs-6 col-md-3'>";
//        $htmlImage .= "<a href='" . $src . "'class='thumbnail' target='_blank'>";
        $htmlImage .= "<image width='80px'" . $attrString  . "/>";
//        $htmlImage .= "</a>";
        $htmlImage .= "</div>";
        return $htmlImage;
    }
}
