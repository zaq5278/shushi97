<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/30
 * Time: 14:43
 */

namespace App\Background\Forms;
use App\Background\Forms\Element\MutexOperations;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Select;

class ManagerForm extends CustomForm
{
    function __construct($entity, $userOptions)
    {
        $userOptions['default_button'] = false;
        parent::__construct($entity, $userOptions);
    }

    public function initialize($entity = null, $options = null) {
        parent::initialize($entity, $options);

        if(null == $entity){//新建用户
            $managerAccount = new Text("account", ["required"=>"required"]);
            $managerAccount->setLabel("账户名");
            $this->add($managerAccount);

            $managerPwd = new Password("password", ["required"=>"required"]);
            $managerPwd->setLabel("密码");
            $this->add($managerPwd);

            $managerPwd = new Password("pw_confirm", ["required"=>"required"]);
            $managerPwd->setLabel("确认密码");
            $this->add($managerPwd);

            $buttons = array(
                array("name" => "创建", "status" => "1", "type" => "submit"),
                array("name" => "返回", "status" => "0", "type" => "back")
            );
        }else{//编辑用户
            $managerAccount = new Text("account", ["disabled"=>"disabled"]);
            $managerAccount->setLabel("账户名");
            $this->add($managerAccount);

            $buttons = array(
                array("name" => "修改", "status" => "2", "type" => "submit"),
                array("name" => "重置密码", "status" => "3", "type" => "pop", "modal"=>"#reset_password"),
                array("name" => "返回", "status" => "0", "type" => "back")
            );
        }

        $managerName = new Text("name", ["required"=>"required"]);
        $managerName->setLabel("姓名");
        $this->add($managerName);

        $managerName = new Text("moblie", ["required"=>"required"]);
        $managerName->setLabel("手机号");
        $this->add($managerName);

        $managerRole = new Select("role_id",
            $options['roles'],
            [
                /*'useEmpty' => true,
                'emptyText' => '...',
                'emptyValue' => '',*/
                "required"=>"required"
            ]
        );
        $managerRole->setLabel("用户角色");
        $this->add($managerRole);
        $managerOperations = new MutexOperations("operations", null, $buttons);
        $managerOperations->setLabel(" ");
        $this->add($managerOperations);
    }
}