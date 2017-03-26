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

class ManagerMBForm extends CustomForm
{
    function __construct($entity, $userOptions)
    {
        $userOptions['default_button'] = false;
        parent::__construct($entity, $userOptions);
    }

    public function initialize($entity = null, $options = null) {
        parent::initialize($entity, $options);

        $moblie = new Text("moblie", ["required"=>"required"]);
        $moblie->setLabel("新手机号");
        $this->add($moblie);

        $buttons = array(
            array("name" => "确认", "status" => "1", "type" => "submit"),
            array("name" => "取消", "status" => "0", "type" => "back")
        );

        $managerOperations = new MutexOperations("operations", null, $buttons);
        $managerOperations->setLabel(" ");
        $this->add($managerOperations);
    }
}