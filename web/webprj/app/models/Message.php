<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;
use Plugin\Core\QSTBaseModel;

class Message extends QSTBaseModel
{

    public $id;
    public $sendid;
    public $recid;
    public $messageid;
    public $addTime;
    public $statue;

    public function initialize() {
        $this->setSource("qst_message");
        $this->hasOne('messageid',__NAMESPACE__ . '\MessageText', 'id',array('alias' => 'messagetext'));
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