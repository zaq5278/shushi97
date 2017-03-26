<?php
namespace App\Models;

use Plugin\Core\QSTBaseModel;

class Assess extends QSTBaseModel
{

    public $id;
    public $ordercode;
    public $userid;
    public $goods_id;
    public $num;
    public $mess;
    public $addTime;
    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("qst_assess");
        $this->hasOne('userid',__NAMESPACE__ . '\MemberInfo', 'userid',array(
            'alias' => 'memberinfo'));
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Depot[]|Depot
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Depot
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
