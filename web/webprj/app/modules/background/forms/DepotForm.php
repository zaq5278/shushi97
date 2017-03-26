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

class DepotForm extends CustomForm {
    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null)
    {
        RichEditor::init($this->assets);
        FileUploader::init($this->assets);

        parent::initialize($entity, $options);
        $itemText = new Text('title', ['required' => 'required', 'placeholder' => '请输入店名','style' => 'width:400px;','maxlength' => 20]);
        $itemText->setLabel("仓库名称");
        $this->add($itemText);

        $itemStatic = new StaticText("static");
        $itemStatic->setLabel("所在城市");
        $itemStatic->setDefault('<div id="distpicker5"><select name="province" class="form-control err" required="required"></select>&nbsp;&nbsp;<select name="city"  class="form-control err" required="required"></select>&nbsp;&nbsp;</div>');
        $this->add($itemStatic);

        $itemText = new Text('address', ['style' => 'width:400px;','maxlength' => 30,'required' => 'required']);
        $itemText->setLabel("仓库地址");
        $this->add($itemText);

        $itemText = new Text('name', ['style' => 'width:400px;','maxlength' => 6,'required' => 'required']);
        $itemText->setLabel("仓库负责人");
        $this->add($itemText);

        $itemText = new Text('mobile', ['style' => 'width:400px;','required' => 'required','maxlength' => 11 , 'placeholder' => '请输入负责人联系方式', 'pattern' => '(\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$']);
        $itemText->setLabel("负责人联系方式");
        $this->add($itemText);

        $itemText = new Text('freight', ['style' => 'width:400px;','maxlength' => 10,'required' => 'required']);
        $itemText->setLabel("基础运费");
        $this->add($itemText);

        $itemText = new Hidden('brand', ['style' => 'width:400px;','maxlength' => 10]);
        $itemText->setLabel("运营品牌");
        $this->add($itemText);

        $itemText = new Hidden('sort_order', ['value' => '0']);
        $itemText->setLabel("排序");
        $this->add($itemText);

        /*$itemCheck = new TextCheck("is_show", ['checked' => 'checked', 'value' => 1]);
        $itemCheck->setText("显示");
        $itemCheck->setLabel("是否显示");
        $this->add($itemCheck);*/
    }
}
