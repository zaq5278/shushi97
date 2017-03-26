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

class ManagerPWForm extends CustomForm
{
    function __construct($entity, $userOptions)
    {
        $userOptions['default_button'] = false;
        parent::__construct($entity, $userOptions);
    }

    public function initialize($entity = null, $options = null) {
        parent::initialize($entity, $options);

        $managerPwd = new Password("opassword", ["required"=>"required"]);
        $managerPwd->setLabel("原密码");
        $this->add($managerPwd);

        $managerPwd = new Password("password", ["required"=>"required"]);
        $managerPwd->setLabel("新密码");
        $this->add($managerPwd);

        $managerPwd = new Password("pw_confirm", ["required"=>"required"]);
        $managerPwd->setLabel("确认密码");
        $this->add($managerPwd);

        $buttons = array(
            array("name" => "修改", "status" => "1", "type" => "submit"),
            array("name" => "返回", "status" => "0", "type" => "back")
        );

        $managerOperations = new MutexOperations("operations", null, $buttons);
        $managerOperations->setLabel(" ");
        $this->add($managerOperations);
    }
}