<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;

use Plugin\Core\QSTBaseModel;

class Franchise extends QSTBaseModel
{

    public $id;
    public $htuserid;
    public $title;
    public $litpic;
    public $province;
    public $city;
    public $district;
    public $address;
    public $mobile;
    public $JoinTime;
    public $addTime;
    public $sort_order;
    public $balance;
    public $is_show;

    public function initialize() {
        $this->setSource("franchise");
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