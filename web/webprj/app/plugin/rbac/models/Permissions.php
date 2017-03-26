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

class Permissions extends Model
{
    /**
     * @var integer permission id.
     */
    public $id;
    /**
     * @var integer permission parent id.
     */
    public $pid;
    /**
     * @var string permission name.
     */
    public $title;
    /**
     * @var string permission controller.
     */
    public $controller;
    /**
     * @var string permission action
     */
    public $action;
    /**
     * @var string permission http method. GET/PUT/POST/DELETE/ALL
     */
    public $method;
    /**
     * @var integer is used.
     */
    public $activity;
    /**
     * @var string
     */
    public $remarks;

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
     * @param $controller
     */
    public function setController($controller) {
        $this->controller = $controller;
    }

    /**
     * @return mixed
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * @param $action
     */
    public function setAction($action) {
        $this->action = $action;
    }

    /**
     * @return mixed
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * @param $activity
     */
    public function setActivity($activity) {
        $this->activity = $activity;
    }

    /**
     * @return mixed
     */
    public function getActivity() {
        return $this->activity;
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

    public function initialize() {
        $this->setSource("rbac_permissions");
        $this->hasMany('id', __NAMESPACE__ . '\RolePermissions', 'permission_id', array(
            'alias' => 'RolePermissions'
        ));
    }

    static function findFromAll($value) {
        $where = array(
            "id = :id: OR title like :value: OR controller like :value: OR action like :value: OR remarks like :value:",
            'bind' => array(
                "id" => $value,
                "value" => '%' . $value . "%"
            )
        );
        return self::find($where);
    }

    static function findFrom($key, $value) {
        $post = array($key => $value);
        $phql = Criteria::fromInput(Di::getDefault(), __NAMESPACE__ . '\Permissions', $post);
        return $phql->execute();
    }
}