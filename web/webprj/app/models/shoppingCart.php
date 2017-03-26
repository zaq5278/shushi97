<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;
use Plugin\Core\QSTBaseModel;

class shoppingCart extends QSTBaseModel
{

    public $id;
    public $userid;
    public $g_id;
    public $num;
    public $addTime;

    public function initialize() {
        $this->setSource("qst_shoppingCart");
        $this->hasOne('g_id',__NAMESPACE__ . '\Goods', 'goods_id',array(
            'alias' => 'goods'));
    }

    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}