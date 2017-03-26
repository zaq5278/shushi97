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
use App\Background\Forms\Element\Audio;

class UserForm extends CustomForm {
    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null) {
        RichEditor::init($this->assets);
        FileUploader::init($this->assets);

        parent::initialize($entity, $options);
        $itemText = new Text("id_text", ["required"=>"required", "placeholder"=>"我是必选的输入框"]);
        $itemText->setLabel("单文本测试项");
        $this->add($itemText);



        $itemArea = new TextArea("id_area", ["placeholder"=>"我是多文本输入框", "style"=>"display:none"]);
        $itemArea->setLabel("多文本测试项");
        $this->add($itemArea);

        $itemCheck = new TextCheck("id_check", ["value"=>"表单提交值"]);
        $itemCheck->setText("复选框");
        $itemCheck->setLabel("是否启用");
        $this->add($itemCheck);

        $itemEditor = new RichEditor("id_editor", ["value"=>"test", "class"=>"test"]);
        $itemEditor->setLabel("富文本");
        $this->add($itemEditor);

        $itemFileUploader = new FileUploader("id_uploader", ["multiple"=>""]);
        $itemFileUploader->setLabel("上传文件");
        $this->add($itemFileUploader);

        $itemFileUploader1 = new FileUploader("id_uploader1", ["required"=>"required"]);
        $itemFileUploader1->setLabel("上传文件1");
        $this->add($itemFileUploader1);

        $itemMultiButton = new MultipleButton("", [["link"=>"#", "value"=>"按钮1", "class"=>"btn-success"],["link"=>"#", "value"=>"按钮2", "class"=>"btn-warning"]]);
        $itemMultiButton->setLabel("");
        $this->add($itemMultiButton);

        $itemImage = new Picture("head");
        $itemImage->setLabel("头像");
        $this->add($itemImage);

        $itemSelect = new Select("id_select",
            [
                "1"=>"北京",
                "2"=>"南京",
                "3"=>"上海"
            ],
            array(
                'useEmpty' => true,
                'emptyText' => '请选择城市',
                'emptyValue' => ''
            )
        );
        $itemSelect->setLabel("所在城市");
        $this->add($itemSelect);

        $itemDate = new Date("id_date");
        $itemDate->setLabel("选择日期");
        $this->add($itemDate);

        $itemDate = new Numeric("id_number");
        $itemDate->setLabel("数量");
        $this->add($itemDate);

        $itemDate = new RangeDate("date_start", "date_end");
        $itemDate->setLabel("起止时间");
        $this->add($itemDate);

        $itemDate = new StaticText("static");
        $itemDate->setLabel("静态文本");
        $this->add($itemDate);

        $item = new Audio("audio");
        $item->setLabel("语音文件");
        $this->add($item);
    }
}
