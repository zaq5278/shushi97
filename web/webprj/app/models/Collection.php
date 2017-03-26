<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;
use Plugin\Core\QSTBaseModel;

class Collection extends QSTBaseModel
{

    public $id;
    public $userid;
    public $g_id;
    public $addTime;

    public function initialize() {
        $this->setSource("qst_collection");
        //$this->hasOne('g_id',__NAMESPACE__ . '\Goods', 'goods_id',array('alias' => 'goods'));
        $this->hasMany('g_id',__NAMESPACE__ . '\Goods', 'goods_id',array('alias' => 'goods'));
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