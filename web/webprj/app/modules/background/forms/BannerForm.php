<?php
namespace App\Background\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Check;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Identical;
use Phalcon\Forms\Element\Select;
use App\Background\Forms\Element\TextCheck;
class BannerForm extends CustomForm
{
    public function initialize($entity = null, $options = null)
    {
        parent::initialize($entity, $options);


        // file
        $file = new Text('image_flag', ["class" => "hidden"]);
        $file->setLabel("轮播图图片");
        $this->add($file);
        $ptvalue=$options['ptvalue'];
        $itemSelect = new Select('pid',['积分商城','有机商城']);
        $itemSelect->setDefault($entity->pid);
        $itemSelect->setLabel("轮播图位置");
        $this->add($itemSelect);

        $itemSelects = new Select("param1",$entity->name['intrDatas'][0],$entity->name['intrDatas'][2]);
        $itemSelects->setLabel("商品编号");
        $itemSelects->setDefault($entity->name['intrDatas'][2]);
        $this->add($itemSelects);

        $itemSelectss = new Select('param2',$entity->name['goods_Datas'][0]);
        $itemSelectss->setLabel("商品编号");
        $itemSelectss->setDefault($entity->name['goods_Datas'][2]);
        $this->add($itemSelectss);


        $itemCheck = new Hidden("is_integral", ["value"=>"1",'class'=>'']);
        $itemCheck->setLabel("是否是积分商品");
        $this->add($itemCheck);

        // sort
        $sort = new Text('sort', array(
            "value" => "10"
        ));
        $sort->setLabel("排序");
        $this->add($sort);

        // submit
        $this->add(new Submit('go', array(
            'class' => 'btn btn-success',
            'value' => "提交"
        )));
    }
}
