<?php
namespace App\Background\Forms;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Validation\Validator\Identical;

class CustomForm extends Form
{
    public function __construct($entity = null, $userOptions = null) {
        parent::__construct(CustomForm::array2object($entity), $userOptions);
    }

    /**
     * This method returns the default value for field 'csrf'
     */
    public function getCsrf()
    {
        return $this->security->getToken();
    }

    public function initialize($entity = null, $options = null)
    {
        $formToken = new Hidden("csrf");
        $formToken->setLabel(" ");
        $formToken->addValidator(new Identical(array(
            'value' => $this->security->getSessionToken(),
            'message' => 'CSRF无效'
        )));
        $formToken->clear();
        $this->add($formToken);

        $formId = new Hidden("id");
        $formId->setLabel(" ");
        $this->add($formId);
    }

    private static function array2object($array) {
        if (!empty($array) && is_array($array)) {
            $obj = new \stdClass();
            foreach ($array as $key => $val){
                $obj->$key = $val;
            }
        }
        else { $obj = $array; }
        return $obj;
    }
}

