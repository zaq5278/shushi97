<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace App\Models;

use Plugin\Core\QSTBaseModel;

class Category extends QSTBaseModel
{

    public $id;
    public $name;
    public $title;
    public $keywords;
    public $desc;
    public $pid;
    public $sort_order;
    public $is_show;
    public $addTime;

    public function initialize() {
        $this->setSource("category");
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