<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;

use Plugin\Core\QSTBaseModel;


class UAddress extends QSTBaseModel
{

    public $id;
    public $userid;
    public $vname;
    public $province;
    public $city;
    public $tel;
    public $address;
    public $code;
    public $setdefault;
    public $btime;

    public function initialize() {
        $this->setSource("qst_address");
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