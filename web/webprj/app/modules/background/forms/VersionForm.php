<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/11/4
 * Time: 17:29
 */

namespace App\Background\Forms;

use App\Background\Forms\Element\Download;
use App\Background\Forms\Element\FileUploader;
use App\Background\Forms\Element\StaticText;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;

class VersionForm extends CustomForm
{
    public function initialize($entity = null, $options = null) {
        parent::initialize($entity, $options);

        if ($options["read_only"]) {
            $itemName = new StaticText("name");
            $itemName->setLabel("名称");
            $this->add($itemName);

            $itemVersion = new StaticText("number");
            $itemVersion->setLabel("版本号");
            $this->add($itemVersion);

            $itemFile = new Download("url");
            $itemFile->setLabel("版本文件");
            $this->add($itemFile);

            $itemDesc = new StaticText("desc");
            $itemDesc->setLabel("版本描述");
            $this->add($itemDesc);

        } else {
            $itemName = new Text("name", ["required"=>"required", "value"=>"ANDROID-李四帮会"]);
            $itemName->setLabel("名称");
            $this->add($itemName);

            $itemVersion = new Text("number", ["required"=>"required", "placeholder"=>"格式要求: 1.0.0"]);
            $itemVersion->setLabel("版本号");
            $this->add($itemVersion);

            FileUploader::init($this->assets);
            $itemFile = new FileUploader("url", ["required"=>"required"]);
            $itemFile->setLabel("版本文件");
            $this->add($itemFile);

            $itemDesc = new TextArea("desc", ["required"=>"required"]);
            $itemDesc->setLabel("版本描述");
            $this->add($itemDesc);
        }
    }
}