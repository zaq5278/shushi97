<?php
/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace Plugin\Rbac;

use Phalcon\Di;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\User\Component;
use Phalcon\Acl\Adapter\Memory as AclMemory;
use Phalcon\Acl\Role as AclRole;
use Phalcon\Acl\Resource as AclResource;
use Plugin\Core\QSTBaseLogger;
use Plugin\Rbac\Models\Roles;
use Plugin\Rbac\Models\Permissions;
use Plugin\Rbac\Models\RolePermissions;

class Rbac extends Component
{
    /**
     * The ACL Object
     */
    private $acl;
    private $fileRbac;
    private $_rolesDict;

    private $moduleName = "glob";

    public function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
        $path = $this->config->cachePath . DIRECTORY_SEPARATOR . __NAMESPACE__;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $this->fileRbac = $this->config->cachePath . DIRECTORY_SEPARATOR . __NAMESPACE__ . DIRECTORY_SEPARATOR . $this->moduleName . ".txt";
        $this->_rolesDict = $this->config->cachePath . DIRECTORY_SEPARATOR . __NAMESPACE__ . DIRECTORY_SEPARATOR . $this->moduleName . "_roles.json";
    }

    /**
     * @return string 获取超级管理员名称
     */
    public function getSupperRoleName()
    {
        return "超级管理员";
    }

    /**
     * @return int 获取超级管理员id
     */
    public function getSupperRoleId()
    {
        return 0;
    }

    /**
     * 重置acl状态, 后续请求将从数据库中重新获取数据来构建acl。
     */
    public function reset()
    {
        if(file_exists($this->fileRbac)){
            unlink($this->fileRbac);
        }
        if(file_exists($this->_rolesDict)){
            unlink($this->_rolesDict);
        }
    }
    /**
     * @param $pid integer，父角色id
     * @param $title string, 角色名称
     * @param $active , 是否启用
     * @param $remarks mixed, 备注
     * @return int
     */
    public function createRole($pid, $title, $active = 1, $remarks = "")
    {
        if (is_numeric($title)) {
            QSTBaseLogger::getDefault()->log("role name format error");
            return -1;
        }
        if (0 != Roles::count(array('conditions' => "title=:title:", "bind" => array('title' => $title)))) {
            QSTBaseLogger::getDefault()->log("role with same name exist, do not allow to create, name: $title");
            return -2;
        }
        $role = new Roles();
        $role->setTitle($title);
        $role->setRemarks($remarks);
        $role->setActivity($active);
        $role->setParentId($pid);
        $role->create_time = date("Y-m-d H:i:s", time());
        $ret = $role->save();
        if (false == $ret) {
            if (false == $ret) {
                foreach ($role->getMessages() as $msg) {
                    QSTBaseLogger::getDefault()->log("db error trace ===> " . $msg, \Phalcon\Logger::ALERT);
                }
                return -3;
            }
        }
        return $role->getId();
    }

    /**
     * @param $id integer, 角色id
     * @param $pid integer，父角色id
     * @param $title string, 角色名称
     * @param $active , 是否启用
     * @param $remarks mixed, 备注
     * @return int
     */
    public function updateRole($id, $pid, $title, $active, $remarks)
    {
        if (is_numeric($title)) {
            QSTBaseLogger::getDefault()->log("role name format error");
            return -1;
        }
        $role = Roles::findFirst(array('conditions' => "id=:id:", 'bind' => array('id' => $id), 'for_update' => true));
        if (!isset($role)) {
            QSTBaseLogger::getDefault()->log("no matched role record found, id: $id");
            return -2;
        }
        $role->setTitle($title);
        $role->setRemarks($remarks);
        $role->setActivity($active);
        $role->setParentId($pid);
        $ret = $role->save();
        if (false == $ret) {
            if (false == $ret) {
                foreach ($role->getMessages() as $msg) {
                    QSTBaseLogger::getDefault()->log("db error trace ===> " . $msg, \Phalcon\Logger::ALERT);
                }
                return -3;
            }
        }
        return $role->getId();
    }

    public function removeRole($roleId)
    {
        $role = Roles::findFirst(array('conditions' => "id=:id:", 'bind' => array('id' => $roleId), 'for_update' => true));
        if (!isset($role)) {
            QSTBaseLogger::getDefault()->log("no matched role record found, id: $roleId");
            return 0;
        }
        if (0 != Roles::count(array('conditions' => "parent_id=:pid:", 'bind' => array('pid' => $roleId)))) {
            QSTBaseLogger::getDefault()->log("can not delete a role with children roles, id: $roleId");
            return -1;
        }
        $sql = 'delete from \Plugin\Rbac\Models\RolePermissions where Plugin\Rbac\Models\RolePermissions.role_id=' . $roleId;
        $query = new Query($sql, Di::getDefault());
        $query->execute();
        $role->delete();
        $this->reset();
        return 0;
    }

    /**
     * @deprecated 未测试，逻辑比较隐晦，不建议使用
     * @param $element
     * @throws RbacException
     * 删除
     */
    public function remove($element)
    {
        if ($element === null) {
            throw new RbacException ("\$element is a required argument.");
        }
        // 删除关系
        $rpRelations = null;
        if ($element instanceof Permissions) {
            $rpRelations = RolePermissions::findByPermission_id($element->getId());
        } else if ($element instanceof Roles) {
            $rpRelations = RolePermissions::findByRole_id($element->getId());
        } else {
            throw new RbacException ("\$element must be Permissions or Roles.");
        }

        foreach ($rpRelations as $rpItem) {
            $rpItem->delete();
        }

        $element->delete();
    }

    /**
     * @deprecated 未测试，逻辑比较隐晦，不建议使用
     * @param $element
     * @throws RbacException
     * 修改
     */
    public function update($element)
    {
        if ($element === null) {
            throw new RbacException ("\$element is a required argument.");
        }
        if (!($element instanceof Permissions) && !($element instanceof Roles)) {
            throw new RbacException ("\$element must be Permissions or Roles.");
        }

        $element->update();
    }

    /**
     * 递归的删除所有子角色的权限
     * @param $roleId
     * @param $permissionId
     */
    private function rDeleteRolePermission($roleId, $permissionId)
    {
        $childRoles = Roles::find(array('conditions' => "parent_id = :pid:", 'bind' => array('pid' => $roleId)));
        foreach ($childRoles as $childRole) {
            $rolePermission = RolePermissions::findFirst(array(
                'conditions' => "role_id = :rid: and permission_id = :perId:",
                "bind" => array('rid' => $childRole->getId(), 'perId' => $permissionId)
            ));
            if (isset($rolePermission->id)) {
                $rolePermission->delete();
            }
            self::rDeleteRolePermission($childRole->getId(), $permissionId);
        }
    }

    /**
     * 更新角色权限关系
     * @param $roleId int
     * @param $permissions array 角色最新权限列表['1':"on"]
     * @return boolean
     */
    public function updateRolePermissions($roleId, &$permissions)
    {
        $rolePermissions = RolePermissions::find(array('conditions' => "role_id = :rid:", "bind" => array('rid' => $roleId)));
        foreach ($rolePermissions as $rolePermission) {
            if (isset($permissions["$rolePermission->permission_id"]) && "on" == $permissions["$rolePermission->permission_id"]) {//权限在数据库中已有记录
                unset($permissions["$rolePermission->permission_id"]);
            } else {//数据库中记录已被移除
                $rolePermission->delete();
                self::rDeleteRolePermission($rolePermission->role_id, $rolePermission->permission_id);
            }
        }
        foreach ($permissions as $key => $value) {
            if ('on' != $value) {
                continue;
            }
            $relation = new RolePermissions();
            $relation->role_id = $roleId;
            $relation->permission_id = $key;
            $ret = $relation->save();
            if (false == $ret) {
                foreach ($relation->getMessages() as $msg) {
                    QSTBaseLogger::getDefault()->log("db error trace ===> " . $msg, \Phalcon\Logger::ALERT);
                }
            }
        }
        $this->reset();
        return true;
    }

    /**
     * @deprecated not used and without test.
     * @param $role
     * @param $permission
     * @throws RbacException
     * 增加角色和权限之间的关系
     */
    public function addRolePermission($role, $permission)
    {
        if (empty($role) || !($role instanceof Roles)) {
            throw new RbacException("\$role must be Roles.");
        }

        if (empty($permission) || !($permission instanceof Permissions)) {
            throw new RbacException("\$permission must be Permissions.");
        }

        // 增加角色和权限之间的关系
        if ($permission instanceof Permissions) {
            $rpRelation = new RolePermissions();
            $rpRelation->setRolePermissions($role->getId(), $permission->getId());
            $rpRelation->save();
            return;
        }
    }

    public function clearPermission($role)
    {
        if (empty($role)) {
            throw new RbacException("\$role must be require");
        }

        $rpRelations = RolePermissions::findByRole_id($role->getId());
        foreach ($rpRelations as $rpItem) {
            $rpItem->delete();
        }
    }

    /**
     * @param $role
     * @param $roleParent
     * @throws RbacException
     * 增加角色之间的关系
     */
    public function addRoleParent($role, $roleParent)
    {
        if (null === $role || null === $roleParent) {
            throw new RbacException("\$role and \$roleParent are required arguments.");
        }

        // 增加角色和角色之间的拥有关系
        if ($role instanceof Roles && $roleParent instanceof Roles) {
            $role->setParentId($roleParent->getId());
            $role->save();
        } else {
            throw new RbacException("\$role and \$roleParent must be Roles.");
        }
    }

    /**
     * @param mixed $parameters
     * @return array
     * 获取所有角色
     */
    public function getRoles($parameters = null)
    {
        $roles = Roles::find($parameters);
        return $roles->toArray();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getRoleById($id)
    {
        return Roles::findFirstById($id);
    }

    public function enableRole($id, $activity)
    {
        $role = Roles::findFirstById($id);
        if ($role) {
            $role->setActivity($activity);
            return $role->save();
        }
        return false;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function getRolesWith($key, $value)
    {
        $roles = null;
        if (empty($key)) {
            $roles = Roles::findFromAll($value);
        } else {
            $roles = Roles::findFrom($key, $value);
        }
        return $roles->toArray();
    }

    /**
     * @param mixed $parameters
     * @return array
     * @throws RbacException
     */
    public function getPermissions($parameters = null)
    {
        QSTBaseLogger::getDefault()->log($parameters);
        $permissions = Permissions::find($parameters);
        return $permissions->toArray();
    }

    /**
     * @param int $roleId
     * @return array
     */
    public function getRolesPermissions($roleId)
    {
        if ($this->getSupperRoleId() == $roleId) {
            $permissions = $this->getPermissions(array("columns" => array("id as permission_id")));
        } else {
            $permissions = RolePermissions::find(array('conditions' => "role_id = :rid:",
                'bind' => array('rid' => $roleId), "columns" => "permission_id"))->toArray();
        }
        return $permissions;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getPermissionById($id)
    {
        return Permissions::findFirstById($id);
    }

    /**
     * @param $id
     * @param $activity
     * @return bool
     */
    public function enablePermission($id, $activity)
    {
        $permission = Permissions::findFirstById($id);
        if ($permission) {
            $permission->setActivity($activity);
            return $permission->save();
        }
        $this->reset();
        return false;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     * @deprecated equivocal
     */
    public function getPermissionsWith($key, $value)
    {
        $permissions = null;
        if (empty($key)) {
            $permissions = Permissions::findFromAll($value);
        } else {
            $permissions = Permissions::findFrom($key, $value);
        }
        return $permissions->toArray();
    }

    /**
     * Rebuilds the access list into a file
     *
     * @return \Phalcon\Acl\Adapter\Memory
     */
    public function rebuild()
    {
        $acl = new AclMemory();
        $acl->setDefaultAction(\Phalcon\Acl::DENY);

        // Register permission
        $permissions = Permissions::find();
        $permissionArray = [];
        foreach ($permissions as $permission) {
            if (!isset($permissionArray[$permission->controller])) {
                $permissionArray[$permission->controller] = [];
            }
            if ("*" == $permission->action) {
                $permissionArray[$permission->controller][] = "*";
            } else {
                if ("ALL" == $permission->method) {
                    $permissionArray[$permission->controller][] = $permission->action . "!GET";
                    $permissionArray[$permission->controller][] = $permission->action . "!POST";
                    $permissionArray[$permission->controller][] = $permission->action . "!UPDATE";
                    $permissionArray[$permission->controller][] = $permission->action . "!DELETE";
                } else {
                    $permissionArray[$permission->controller][] = $permission->action . "!" . $permission->method;
                }
            }
        }
        foreach ($permissionArray as $resource => $access) {
            $acl->addResource(new AclResource($resource), array_unique($access));
        }

        /**
         * 添加超级管理员权限
         */
        $rolesDict = [];
        $acl->addRole(new AclRole($this->getSupperRoleName()));
        $acl->allow($this->getSupperRoleName(), "*", "*");
        $rolesDict[$this->getSupperRoleId()] = $this->getSupperRoleName();
        // Register roles
        $roles = Roles::find('activity = true');
        foreach ($roles as $role) {
            $acl->addRole(new AclRole($role->getTitle()));
            $rolesDict[$role->getId()] = $role->getTitle();
        }
        // Grant access to private area to role Users
        foreach ($roles as $role) {
            // Grant permissions in "permissions" model
            foreach ($role->RolePermissions as $rpItem) {
                $permission = $rpItem->Permission;
                if ($permission->getActivity()) {
                    if ("*" == $permission->action) {
                        $acl->allow($role->getTitle(), $permission->controller, "*");
                    } else {
                        if ("ALL" == $permission->method) {
                            $acl->allow($role->getTitle(), $permission->controller, $permission->action . "!GET");
                            $acl->allow($role->getTitle(), $permission->controller, $permission->action . "!POST");
                            $acl->allow($role->getTitle(), $permission->controller, $permission->action . "!UPDATE");
                            $acl->allow($role->getTitle(), $permission->controller, $permission->action . "!DELETE");
                        } else {
                            $acl->allow($role->getTitle(), $permission->controller, $permission->action . "!" . $permission->method);
                        }
                    }
                }
            }
        }

        if (touch($this->fileRbac) && is_writable($this->fileRbac)) {
            file_put_contents($this->fileRbac, serialize($acl));
            file_put_contents($this->_rolesDict, json_encode($rolesDict));
            // Store the ACL in APC
            if (function_exists('apc_store')) {
                apc_store($this->moduleName . '-acl', $acl);
            }
        } else {
            QSTBaseLogger::getDefault()->log('The user does not have write permissions to create the ACL list at ' . $this->fileRbac);
            $this->flash->error(
                'The user does not have write permissions to create the ACL list at ' . $this->fileRbac
            );
        }
        return $acl;
    }

    /**
     * Returns the ACL list
     *
     * @return \Phalcon\Acl\Adapter\Memory
     */
    public function getAcl()
    {
        // Check if the ACL is already created
        if (is_object($this->acl)) {
            return $this->acl;
        }
        // Check if the ACL is in APC
        if (function_exists('apc_fetch')) {
            $acl = apc_fetch($this->moduleName . '-acl');
            if (is_object($acl)) {
                $this->acl = $acl;
                return $acl;
            }
        }
        // Check if the ACL is already generated
        if (!file_exists($this->fileRbac)) {
            $this->acl = $this->rebuild();
            return $this->acl;
        }

        // Get the ACL from the data file
        $data = file_get_contents($this->fileRbac);
        $this->acl = unserialize($data);

        // Store the ACL in APC
        if (function_exists('apc_store')) {
            apc_store('vokuro-acl', $this->acl);
        }

        return $this->acl;
    }

    /**
     * @param $controllerName string controller name
     * @return bool
     */
    public function isPrivate($controllerName)
    {
        $permission = Permissions::count(array(
            'controller = :controller: and activity = 1',
            'bind' => array('controller' => $controllerName)
        ));

        if ($permission) {
            return true;
        }
        return false;
    }

    /**
     * Checks if the current profile is allowed to access a resource
     *
     * @param string|integer $role name or role id.
     * @param string $controller
     * @param string $access
     * @return boolean
     */
    public function isAllowed($role, $controller, $access)
    {
        $acl = $this->getAcl();
        $rolesDict = json_decode(file_get_contents($this->_rolesDict), true);
        $_role = is_numeric($role) ? $rolesDict[$role] : $role;
        if (!isset($_role)) {
            return false;
        }
        return $acl->isAllowed($_role, $controller, $access);
    }

    /**
     * @param string $name , permission display name
     * @param string $controller , permission access controller.
     * @param string $action , permission access action.
     * @param string $method , permission access http method.
     * @param integer $pid ,parent permission id
     * @param int $active , is permission used.
     * @return boolean | Permissions
     */
    public function createPermission($name, $controller, $action = '*', $method = "ALL", $pid = 0, $active = 1)
    {
        QSTBaseLogger::getDefault()->log("Name: $name, Controller: $controller, action: $action, method: $method");
        if (0 != $pid) {
            if (0 == Permissions::count(array('conditions' => "id = :id:", 'bind' => array('id' => $pid)))) {
                QSTBaseLogger::getDefault()->log("parent permission not exit!!, pid: $pid");
                return false;
            }
        }
        $permission = Permissions::findFirst(array(
            'conditions' => "controller = :controller: and action = :action: and method = :method:",
            'bind' => array('controller' => $controller, 'action' => $action, 'method' => $method),
            'for_update' => true
        ));
        if (isset($permission->id)) {
            QSTBaseLogger::getDefault()->log("permission exit!!");
            return true;
        }
        $permission = new Permissions();
        $permission->title = $name;
        $permission->controller = $controller;
        $permission->action = $action;
        $permission->method = $method;
        $permission->pid = $pid;
        $permission->activity = $active;
        $ret = $permission->save();
        if (false == $ret) {
            foreach ($permission->getMessages() as $msg) {
                QSTBaseLogger::getDefault()->log("db error trace ===> " . $msg, \Phalcon\Logger::ALERT);
            }
            return false;
        }
        $this->reset();
        return $permission;
    }

    /**
     * @param $id int delete permission
     * @return bool
     */
    public function deletePermission($id)
    {
        $permission = Permissions::findFirst(array(
            'conditions' => 'id = :id:', 'bind' => array('id' => $id), 'for_update' => true
        ));
        if (!isset($permission->id)) {
            QSTBaseLogger::getDefault()->log("no matched permission found, id: $id");
            return true;
        }
        $sql = 'delete from \Plugin\Rbac\Models\RolePermissions where Plugin\Rbac\Models\RolePermissions.permission_id=';
        QSTBaseLogger::getDefault()->log("no matched permission found, id: $permission->id");
        if (0 == $permission->pid) {//controller permission, all children permission will deleted.
            $childrenPermissions = Permissions::find(array(
                'conditions' => 'pid = :id:', 'bind' => array('id' => $id), 'for_update' => true
            ));
            foreach ($childrenPermissions as $child) {
                $query = new Query($sql . $child->id, Di::getDefault());
                $query->execute();
                $child->delete();
            }
        }
        $query = new Query($sql . $permission->id, Di::getDefault());
        $query->execute();
        $permission->delete();
        $this->reset();
    }

    /**
     * @param string $name , permission display name
     * @param string $controller , permission access controller.
     * @param string $action , permission access action.
     * @param string $method , permission access http method.
     * @param int $active , is permission used.
     * @return boolean | Permissions
     */
    public function updatePermission($id, $name, $controller, $action, $method, $active)
    {
        $permission = Permissions::findFirst(array(
            'conditions' => 'id = :id:', 'bind' => array('id' => $id), 'for_update' => true
        ));
        if (!isset($permission->id)) {
            QSTBaseLogger::getDefault()->log("no matched permission found, id: $id");
            return true;
        }
        $permission->title = $name;
        $permission->controller = $controller;
        $permission->action = $action;
        $permission->method = $method;
        $permission->activity = $active;
        $ret = $permission->save();
        if (false == $ret) {
            foreach ($permission->getMessages() as $msg) {
                QSTBaseLogger::getDefault()->log("db error trace ===> " . $msg, \Phalcon\Logger::ALERT);
            }
            return false;
        }
        $this->reset();
        return true;
    }

    /**
     * @param string $moduleName module name
     * @param array $menu menu.
     * @return bool
     */
    public function buildPermissionsFromMenu($moduleName, $menu)
    {
        foreach ($menu as $item) {
            $controller = "";
            $controllerPerName = $item['name'];
            $pid = 0;
            if (isset($item['sub_menu'])) { // with action permission.
                foreach ($item['sub_menu'] as $subItem) {
                    $path = substr(explode("?", $subItem['link_action'])[0], strlen($moduleName) + 1);
                    $srcs = explode("/", $path);
                    $subController = isset($srcs[0]) ? $srcs[0] : "index";
                    if ("" == $controller | $subController != $controller) {
                        if ("" != $controller) {//  $subController != $controller condition.
                            $controllerPerName = $subItem['name'];
                        }
                        $controller = $subController;
                        $ret = $this->createPermission($controllerPerName, $controller);
                        if (false == $ret) {//创建控制器级别权限失败，将不再自动创建该目录子权限。
                            QSTBaseLogger::getDefault()->log("auto create permission $controllerPerName failed", \Phalcon\Logger::ALERT);
                            break;
                        }
                        $pid = $ret->id;
                    }
                    if (isset($srcs[1])) {
                        $actionPerName = $subItem['name'];
                        $action = $srcs[1];
                        $ret = $this->createPermission($actionPerName, $controller, $action, "ALL", $pid);
                        if (false == $ret) {
                            QSTBaseLogger::getDefault()->log("auto create permission $actionPerName failed", \Phalcon\Logger::ALERT);
                        }
                    }
                }
            } else {//only controller permission
                if (!isset($item['link_action'])) {
                    QSTBaseLogger::getDefault()->log("link_action is required for build permission for module: $moduleName, controller: $controllerPerName", \Phalcon\Logger::ALERT);
                    QSTBaseLogger::getDefault()->log("You may need to add this permission manually", \Phalcon\Logger::ALERT);
                    continue;
                }
                $path = substr(explode("?", $item['link_action'])[0], strlen($moduleName) + 1);
                $srcs = explode("/", $path);
                $controller = isset($srcs[0]) ? $srcs[0] : "index";
                $this->createPermission($controllerPerName, $controller);
            }
        }
        return true;
    }
}
