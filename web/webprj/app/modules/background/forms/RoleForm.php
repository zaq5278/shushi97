<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/26
 * Time: 10:08
 */

namespace App\Background\Forms;

use App\Background\Forms\Element\StaticText;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Check;
use App\Background\Forms\Element\MultipleCheck;

class RoleForm extends CustomForm {
    function __construct($entity, $userOptions)
    {
        $helpText = "<span class='text-success'>* 绿色权限为当前可选权限</span></br>
            <span class='text-danger'>* 红色为父角色未拥有权限，自角色不能勾选</span></br>
            <span class='text-warning'>* 橙色为已勾选父权限，拥有全部子权限不需要额外勾选</span></br>
            <span class='text-danger'><strong>** 移除父角色权限,所有子角色的该权限将被移除</strong></span></br>";
        $entity['help'] = $helpText;
        parent::__construct($entity, $userOptions);
    }

    /**
     * @param null $entity
     * @param null $options
     */
    public function initialize($entity = null, $options = null) {
        parent::initialize($entity, $options);
        $parentData = $options['parentRoles']['roles'];
        $parentAttr = $options['parentRoles']['attributes'];
        $parentAttr['class'] = "form-control";
        $roleParent = new Select("parent", $parentData, $parentAttr);
        $roleParent->setLabel("父角色");
        $this->add($roleParent);
        $roleTitle = new Text("title", ["required"=>"required"]);
        $roleTitle->setLabel("角色名称");
        $this->add($roleTitle);

        $roleRemark = new TextArea("remarks");
        $roleRemark->setLabel("备注");
        $this->add($roleRemark);

        $permissions = new MultipleCheck("permissions", $options['permissions'], ['class' => 'checkbox-inline text-success', "name"=>"permissions"]);
        $permissions->setLabel("权限");
        $this->add($permissions);

        $help = new StaticText("help");
        $help->setLabel("权限说明");
        $this->add($help);

        $roleActive = new Check("activity");
        $roleActive->setLabel("是否启用");
        $this->add($roleActive);
    }
}