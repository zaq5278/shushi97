<?php
namespace App\Background\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Identical;

class LoginForm extends CustomForm
{
    public function initialize($entity = null, $options = null)
    {
        parent::initialize($entity, $options);
        
        // account
        $account = new Text('account', array(
            'placeholder' => '请输入用户名'
        ));

        $account->addValidators(array(
            new PresenceOf(array(
                'message' => '请先填写用户名'
            ))
        ));
        $account->setLabel(" ");
        $this->add($account);

        // Password
        $password = new Password('password', array(
            'placeholder' => '请输入密码'
        ));

        $password->addValidator(new PresenceOf(array(
            'message' => '请输入登录密码'
        )));
        $password->setLabel(" ");
        $password->clear();

        $this->add($password);

        // Remember
        $remember = new Check('remember', array(
            'value' => 'yes'
        ));
        $this->add($remember);

        $this->add(new Submit('go', array(
            'class' => 'btn btn-success',
            'value' => "登 录"
        )));
    }
}
