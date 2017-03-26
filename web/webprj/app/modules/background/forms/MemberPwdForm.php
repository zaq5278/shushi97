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
use Phalcon\Forms\Element\Password;
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

class MemberPwdForm extends CustomForm {
    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null) {
        RichEditor::init($this->assets);
        FileUploader::init($this->assets);

        parent::initialize($entity, $options);
        $itemText = new Password("oldPwd", ["required"=>"required", "placeholder"=>"请输入原始密码！"]);
        $itemText->setLabel("原密码");
        $this->add($itemText);
        $itemText = new Password("newPwd", ["required"=>"required", "placeholder"=>"请输入新密码！"]);
        $itemText->setLabel("新密码");
        $this->add($itemText);
        $itemText = new Password("comPwd", ["required"=>"required", "placeholder"=>"请再次输入新密码！"]);
        $itemText->setLabel("确认新密码");
        $this->add($itemText);
    }
}
