<?php
namespace App\Models;

use Plugin\Core\QSTBaseModel;
class OrderGoods extends QSTBaseModel
{
    public $ordercode;
    public $goods_id;
    public $goods_name;
    public $goods_introduction;
    public $market_price;
    public $goods_price;
    public $con_integral;
    public $fran_cash;
    public $ref_integral;
    public $num;
    public $totalPrice;
    public $btime;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("qst_order_goods");

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
