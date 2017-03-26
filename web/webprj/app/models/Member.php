<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;
use Plugin\Core\QSTBaseModel;

class Member extends QSTBaseModel
{

    public $id;
    public $account;
    public $time;
    public $lastlogintime;
    public $token;

    public function initialize() {
        $this->setSource("qst_member");
        $this->hasOne('id',__NAMESPACE__ . '\MemberInfo', 'userid',array('alias' => 'memberinfo'));
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