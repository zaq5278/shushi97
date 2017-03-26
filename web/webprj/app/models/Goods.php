<?php
namespace App\Models;

use Plugin\Core\QSTBaseModel;
class Goods extends QSTBaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=8, nullable=false)
     */
    public $goods_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $depot_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $cat_id;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $goods_sn;

    /**
     *
     * @var string
     * @Column(type="string", length=120, nullable=false)
     */
    public $goods_name;

    /**
     *
     * @var string
     * @Column(type="string", length=60, nullable=true)
     */
    public $goods_name_style;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=true)
     */
    public $click_count;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $goods_brief;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $market_price;

    /**
     *
     * @var double
     * @Column(type="double", length=10, nullable=false)
     */
    public $shop_price;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $goods_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_integral;

    public $integral;
    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $con_integral;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $fran_cash;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=true)
     */
    public $ref_integral;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $good_introduction;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $good_details;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $good_spec;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_on_sale;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_delete;

    public $is_coll;
    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_recom;

    /**
     *
     * @var integer
     * @Column(type="integer", length=8, nullable=false)
     */
    public $sort_order;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $last_update;

    public $on_saleTime;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $addTime;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("goods");
        $this->hasOne('depot_id',__NAMESPACE__ . '\Depot', 'id',array(
            'alias' => 'depot'));
        $this->hasOne('cat_id',__NAMESPACE__ . '\Category', 'id',array(
            'alias' => 'category'));
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
