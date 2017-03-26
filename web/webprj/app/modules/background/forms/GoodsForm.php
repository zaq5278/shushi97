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

class GoodsForm extends CustomForm {
    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null)
    {
        RichEditor::init($this->assets);
        FileUploader::init($this->assets);

        parent::initialize($entity, $options);
        $itemSelect = new Select("depot_id",$entity->depot_id[0],$entity->depot_id[1]
        );
        $itemSelect->setLabel('仓库地址');
        $itemSelect->setDefault($entity->depot_id[2]);
        $this->add($itemSelect);

        $itemSelect = new Select("cat_id",$entity->cat_id[0],$entity->cat_id[1]
        );
        $itemSelect->setLabel("一级栏目");
        $itemSelect->setDefault($entity->cat_id[2]);
        $this->add($itemSelect);

        $itemSelects = new Select("cat_ide",[],['useEmpty' => true, 'emptyText' => '请选择栏目', "required"=>"required"]);
        $itemSelects->setLabel("二级栏目");
        //$itemSelects->setDefault($entity->cat_id[2]);
        $this->add($itemSelects);


        $itemText = new Text("goods_name",["required"=>"required", 'style' => 'width:400px;' , "placeholder"=>"请输入商品名称",'maxlength' => 40,'minlength' => 5]);
        $itemText->setLabel("商品名称");
        $this->add($itemText);


        /*$itemText = new Text("click_count", ["placeholder"=>"请输入点击数量", 'value' => rand(100,1000)]);
        $itemText->setLabel("商品点击量");
        $this->add($itemText);*/


        $itemArea = new TextArea("goods_brief", ["required"=>"required", "placeholder"=>"请输入商品介绍",'maxlength' => 200,'minlength' => 10]);
        $itemArea->setLabel("商品介绍");
        $this->add($itemArea);

        $itemText = new Text("market_price", ["required"=>"required", 'style' => 'width:400px;' , "placeholder"=>"请输入商品原价", 'onkeyup' =>'value=value.replace(/[^\d]/g,"")' ]);
        $itemText->setLabel("原价");
        $this->add($itemText);

        $itemText = new Text("shop_price", ["required"=>"required", 'style' => 'width:400px;' , "placeholder"=>"请输入本店售价", 'onkeyup' =>'value=value.replace(/[^\d]/g,"")' ]);
        $itemText->setLabel("现价");
        $this->add($itemText);

        $itemText = new Text("goods_number", ["required"=>"required", 'style' => 'width:400px;' , "placeholder"=>"请输入库存", 'onkeyup' =>'value=value.replace(/[^\d]/g,"")' ]);
        $itemText->setLabel("库存");
        $this->add($itemText);

        $itemCheck = new TextCheck("is_integral", ["value"=>"1"]);
        $itemCheck->setText("是");
        $itemCheck->setLabel("积分兑换商品");
        $this->add($itemCheck);

        $itemText = new Text("integral", ['style' => 'width:400px;' , 'onkeyup' =>'value=value.replace(/[^\d]/g,"")' ]);
        $itemText->setLabel("积分");
        $this->add($itemText);

        $itemText = new Text("con_integral", ["required"=>"required", 'style' => 'width:400px;','maxlength' => 3 , "placeholder"=>"请输入送积分比例%", 'value' => 8,'onkeyup' =>'value=value.replace(/[^\d]/g,"")' ]);
        $itemText->setLabel("送积分比例%");
        $this->add($itemText);

        $itemText = new Text("fran_cash", ["required"=>"required", 'style' => 'width:400px;','maxlength' => 3 , "placeholder"=>"请输入加盟店返现比例%", 'value' => 8,'onkeyup' =>'value=value.replace(/[^\d]/g,"")' ]);
        $itemText->setLabel("加盟店返现比例%");
        $this->add($itemText);

        $itemText = new Text("ref_integral", ["required"=>"required", 'style' => 'width:400px;','maxlength' => 3 , "placeholder"=>"请输入推荐者赠送积分%", 'value' => 8,'onkeyup' =>'value=value.replace(/[^\d]/g,"")' ]);
        $itemText->setLabel("推荐者赠送积分%");
        $this->add($itemText);

        $itemFileUploader = new FileUploader("good_introduction", ["required"=>"required","multiple"=> 'multiple','fileCount' => 3 , 'id' => 'good_introduction','desc'=>'上传图片大小(高*宽)：230*375']);
        $itemFileUploader->setLabel("商品介绍图");
        $this->add($itemFileUploader);

        $itemFileUploader = new FileUploader("good_details", ["required"=>"required","multiple"=>"multiple",'fileCount' => 3 ]);
        $itemFileUploader->setLabel("商品详情图");
        $this->add($itemFileUploader);

        $itemFileUploader = new FileUploader("good_spec", ["required"=>"required" ]);
        $itemFileUploader->setLabel("商品规格图");
        $this->add($itemFileUploader);

        $itemCheck = new Hidden("is_recom", ['value' => 0]);
        $this->add($itemCheck);

        $itemText = new Hidden('sort_order', ['value' => '0']);
        $this->add($itemText);

        $itemText = new Hidden('page', ['value' => '0']);
        $itemText->setLabel("列表第几页");
        $this->add($itemText);

        $itemCheck = new Hidden("is_show", ['checked' => 'checked', 'value' => 1]);
        $this->add($itemCheck);
    }
}
