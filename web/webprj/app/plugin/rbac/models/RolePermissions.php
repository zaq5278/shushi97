<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/17
 * Time: 11:44
 */

namespace Plugin\Rbac\Models;
use Phalcon\Mvc\Model;

class RolePermissions extends Model
{
    public $id;
    public $role_id;
    public $permission_id;

    /**
     * @param $idRole
     * @param $idPermission
     */
    public function setRolePermissions($idRole, $idPermission) {
        $this->role_id = $idRole;
        $this->permission_id = $idPermission;
    }

    public function initialize() {
        $this->setSource("rbac_rolepermissions");
        $this->hasOne('role_id', __NAMESPACE__ . '\Roles', 'id', array(
            'alias' => 'Role'
        ));

        $this->hasOne('permission_id', __NAMESPACE__ . '\Permissions', 'id', array(
            'alias' => 'Permission'
        ));
    }
}