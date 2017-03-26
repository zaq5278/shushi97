<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 14:19
 */

namespace App\Background\Forms\Element;
use Phalcon\Forms\Element\Check;

class Download extends CustomElement
{
    public function __construct($name, array $attributes = null) {
        parent::__construct($name, $attributes);
    }

    public function render($attributes = null)
    {
        $html = "";
        try {
            $downloadString = $this->getValue();
            $imageArray = json_decode($downloadString);
            if (is_null($imageArray)) {
                $imageArray[] = $downloadString;
            }
            foreach ($imageArray as $image) {
                $html .= $this->htmlDownload($image);
            }
        } catch (\Exception $e) {
            $this->logger->log("Picture::render()-" . $e->getMessage());
        }

//        $html = $this->getValue();
//        $html .= "<a class='btn btn-success' href=" . $this->getValue() . ">";
//        $html .= "下载";
//        $html .= "</a>";
        return $html;
    }

    private function htmlDownload($fileUrl) {
        $html = "<div>";
        $html .= $fileUrl;
        $html .= " ";
        $html .= "<a target='_blank' class='btn btn-success btn-sm' href=" . $fileUrl . ">下载</a>";
        $html .= "</div>";
        return $html;
    }
}