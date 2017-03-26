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

class CategoryForm extends CustomForm {
    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null)
    {
        RichEditor::init($this->assets);
        FileUploader::init($this->assets);

        parent::initialize($entity, $options);
        //print_r($entity);exit;
        $itemSelect = new Select('pid',$entity->pidData,['useEmpty' => true, 'emptyText' => '请选择栏目']);
        $itemSelect->setLabel("一级分类");
        $this->add($itemSelect);

        $itemText = new Text('name', ['required' => 'required','useEmpty' => true, 'emptyText' => '请选择栏目']);
        $itemText->setLabel("分类名称");
        $this->add($itemText);

        $itemText = new Hidden('keywords', ['style' => 'width:400px;']);
        $itemText->setLabel("关键词");
        $this->add($itemText);

        $itemArea = new Hidden("desc", ["placeholder"=>"栏目描述",'rows' => 10, 'cols' => 30]);
        $itemArea->setLabel("描&nbsp;述");
        $this->add($itemArea);

        $itemText = new Text('sort_order', ['value' => '0']);
        $itemText->setLabel("排序");
        $this->add($itemText);

        $itemCheck = new Hidden("is_show", ['value' => 1]);
        //$itemCheck->setText("显示");
        $itemCheck->setLabel("是否显示");
        $this->add($itemCheck);
    }
}
