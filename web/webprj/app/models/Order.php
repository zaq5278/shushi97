<?php
namespace App\Models;

use Plugin\Core\QSTBaseModel;
class Order extends QSTBaseModel
{
    public $id;
    public $userid;
    public $ordercode;
    public $masterorder;
    public $type;
    public $depotid;
    public $btime;
    public $vstate;
    public $collid;
    public $mess;
    public $distribution;
    public $totalPrice;
    public $integral;
    public $disPrice;
    public $payment;
    public $logisticsnum;
    public $vname;
    public $province;
    public $city;
    public $tel;
    public $address;
    public $is_evaluation;
    public $errdsp;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("qst_ordercode");
        $this->hasOne('ordercode',__NAMESPACE__ . '\OrderGoods', 'ordercode',array(
            'alias' => 'ordergoods'));
        $this->hasOne('userid',__NAMESPACE__ . '\MemberInfo', 'userid',array(
            'alias' => 'memberinfo'));
        $this->hasOne('depotid',__NAMESPACE__ . '\Depot', 'id',array(
            'alias' => 'depot'));
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
