<?php
namespace App\Models;

use Plugin\Core\QSTBaseModel;
class OrderLog extends QSTBaseModel
{
    public $id;
    public $ordercode;
    public $state;
    public $mess;
    public $btime;


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSource("qst_orderlog");
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
