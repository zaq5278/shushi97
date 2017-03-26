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

class MessageForm extends CustomForm {
    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null)
    {
        parent::initialize($entity, $options);
        $itemSelect = new Select("roleid",$entity->roleid[0],$entity->roleid[1]
        );
        $itemSelect->setLabel('发送给');
        $itemSelect->setDefault($entity->depot_id[2]);
        $this->add($itemSelect);

        $itemArea = new TextArea("message", ["required"=>"required", "placeholder"=>"请输入发送内容",'maxlength' => 100,'minlength' => 10]);
        $itemArea->setLabel("发送内容");
        $this->add($itemArea);
    }
}
