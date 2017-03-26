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

class RefundAddForm extends CustomForm {
    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null)
    {
        RichEditor::init($this->assets);
        FileUploader::init($this->assets);

        parent::initialize($entity, $options);

        $itemArea = new TextArea("mess", ["placeholder"=>"请输入说明",'maxlength' => 250]);
        $itemArea->setLabel("说明");
        $this->add($itemArea);
    }
}
