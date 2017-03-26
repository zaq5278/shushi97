<?php
namespace App\Background\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Radio;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Identical;

class ModifyPwdForm extends CustomForm
{
    public function initialize($entity = null, $options = null)
    {
        parent::initialize($entity, $options);

        $this->add(new Submit('go', array(
            'class' => 'btn btn-success',
            'value' => "保 存"
        )));
    }
}
