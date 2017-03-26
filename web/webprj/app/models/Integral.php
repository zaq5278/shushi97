<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;
use Plugin\Core\QSTBaseModel;

class Integral extends QSTBaseModel
{

    public $id;
    public $userid;
    public $title;
    public $type;
    public $integral;
    public $totalIntegral;
    public $addTime;

    public function initialize() {
        $this->setSource("qst_integral");
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