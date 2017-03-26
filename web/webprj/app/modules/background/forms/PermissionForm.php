<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/24
 * Time: 10:23
 */

namespace App\Background\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Forms\Element\Check;

class PermissionForm extends CustomForm
{
    public function initialize($entity = null, $options = null) {
        parent::initialize($entity, $options);
        
        $permissionTitle = new Text("title", ["required"=>"required"]);
        $permissionTitle->setLabel("功能名称");
        $this->add($permissionTitle);

        $permissionController = new Text("controller", ["required"=>"required"]);
        $permissionController->setLabel("控制器");
        $this->add($permissionController);

        $permissionAction = new Text("method", ["required"=>"required"]);
        $permissionAction->setLabel("方法");
        $this->add($permissionAction);

        $permissionRemark = new TextArea("remarks");
        $permissionRemark->setLabel("备注");
        $this->add($permissionRemark);

        $permissionActive = new Check("activity");
        $permissionActive->setLabel("是否启用");
        $this->add($permissionActive);
    }
}