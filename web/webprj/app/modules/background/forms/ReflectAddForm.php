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

class ReflectAddForm extends CustomForm {
    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null)
    {
        RichEditor::init($this->assets);
        FileUploader::init($this->assets);

        parent::initialize($entity, $options);
        $itemText = new Text("refPrice", ["required"=>"required", 'style' => 'width:400px;' , "placeholder"=>"请输入提现金额",'maxlength' => 10,'minlength' => 3]);
        $itemText->setLabel("提现金额");
        $this->add($itemText);

        $itemText = new Text("name", ["required"=>"required","placeholder"=>"请输入姓名"]);
        $itemText->setLabel("姓名");
        $this->add($itemText);

        $itemText = new Text("code", ["required"=>"required","placeholder"=>"请输入身份证号"]);
        $itemText->setLabel("身份证号");
        $this->add($itemText);

        $itemText = new Text("brank", ["required"=>"required","placeholder"=>"请输入银行名称"]);
        $itemText->setLabel("银行名称");
        $this->add($itemText);

        $itemText = new Text("brankCode", ["required"=>"required","placeholder"=>"请输入银行账号"]);
        $itemText->setLabel("银行账号");
        $this->add($itemText);
    }
}
