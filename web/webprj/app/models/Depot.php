<?php
namespace App\Models;

use Plugin\Core\QSTBaseModel;

class Depot extends QSTBaseModel
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=5, nullable=false)
     */
    public $id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=30, nullable=false)
     */
    public $title;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=true)
     */
    public $province;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=true)
     */
    public $city;

    /**
     *
     * @var string
     * @Column(type="string", length=15, nullable=true)
     */

    public $address;
    /**
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    public $name;

    /**
     *
     * @var string
     * @Column(type="string", length=11, nullable=true)
     */
    public $mobile;

    public $freight;

    public $brand;
    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $addTime;

    /**
     *
     * @var integer
     * @Column(type="integer", length=5, nullable=false)
     */
    public $sort_order;

    /**
     *
     * @var integer
     * @Column(type="integer", length=1, nullable=false)
     */
    public $is_show;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("depot");
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
