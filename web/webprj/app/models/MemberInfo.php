<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;
use Plugin\Core\QSTBaseModel;

class MemberInfo extends QSTBaseModel
{

    public $userid;
    public $sex;
    public $integral;
    public $headurl;
    public $nick;
    public $city;
    public $province;
    public $country;
    public $recId;
    public $follow_num;
    public $follow_time;
    public $addTime;
    public $lastTime;

    public function initialize() {
        $this->setSource("qst_member_info");
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