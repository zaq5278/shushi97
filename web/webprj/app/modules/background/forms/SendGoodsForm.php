<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 10:08
 */

namespace App\Background\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Numeric;
use App\Background\Forms\Element\TextCheck;
use App\Background\Forms\Element\RichEditor;
use App\Background\Forms\Element\FileUploader;
use App\Background\Forms\Element\MultipleButton;
use App\Background\Forms\Element\Picture;
use App\Background\Forms\Element\RangeDate;
use App\Background\Forms\Element\StaticText;

class SendGoodsForm extends CustomForm {
    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null)
    {
        RichEditor::init($this->assets);
        FileUploader::init($this->assets);

        parent::initialize($entity, $options);
        $itemText = new Text("wl_name", ["required"=>"required", 'style' => 'width:400px;' , "placeholder"=>"请输入物流名称",'maxlength' => 10,'minlength' => 2]);
        $itemText->setLabel("物流名称");
        $this->add($itemText);

        $itemText = new Text("wl_code", ["required"=>"required", 'style' => 'width:400px;' , "placeholder"=>"请输入物流单号",'maxlength' => 20,'minlength' => 5]);
        $itemText->setLabel("物流单号");
        $this->add($itemText);
    }
}
