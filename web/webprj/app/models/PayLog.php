<?php
namespace App\Models;

use Plugin\Core\QSTBaseModel;
class PayLog extends QSTBaseModel
{
    public $id;
    public $userid;
    public $htuserid;
    public $fancheise_id;
    public $type;
    public $AddSub;
    public $ordercode;
    public $title;
    public $totalprice;
    public $name;
    public $code;
    public $brank;
    public $brankCode;
    public $vstate;
    public $state;
    public $mess;
    public $addTime;
    public $etime;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("qst_paylog");
        $this->hasOne('ordercode',__NAMESPACE__ . '\Order', 'ordercode',array(
            'alias' => 'order'));
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Goods[]|Goods
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Goods
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
