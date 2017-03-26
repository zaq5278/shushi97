<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;
use Plugin\Core\QSTBaseModel;

class MessageText extends QSTBaseModel
{

    public $id;
    public $userid;
    public $message;
    public $roleid;
    public $addTime;
    public $is_delete;

    public function initialize() {
        $this->setSource("qst_messagetext");
        $this->hasOne('roleid','Plugin\Rbac\Models\Roles', 'id',array('alias' => 'Roles'));
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