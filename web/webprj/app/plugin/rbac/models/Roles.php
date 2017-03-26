<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace Plugin\Rbac\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Di;

class Roles extends Model
{
    private $id;
    private $title;
    private $activity;
    private $remarks;
    private $parent_id;
    public $create_time;

    /**
     * @param $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param $activity
     */
    public function setActivity($activity) {
        $this->activity = $activity;
    }

    /**
     * @param $remarks
     */
    public function setRemarks($remarks) {
        $this->remarks = $remarks;
    }

    /**
     * @return mixed 获取id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param $parent_id
     */
    public function setParentId($parent_id) {
        $this->parent_id = $parent_id;
    }

    public function initialize() {
        $this->setSource("rbac_roles");
        $this->hasMany('id', __NAMESPACE__ . '\RolePermissions', 'role_id', array(
            'alias' => 'RolePermissions'
        ));
    }

    static function findFromAll($value) {
        $where = array(
            "id = :id: OR title like :value: OR remarks like :value:",
            'bind' => array(
                "id" => $value,
                "value" => '%' . $value . "%"
            )
        );
        return self::find($where);
    }

    static function findFrom($key, $value) {
        $post = array($key => $value);
        $phql = Criteria::fromInput(Di::getDefault(), __NAMESPACE__ . '\Roles', $post);
        return $phql->execute();
    }
}