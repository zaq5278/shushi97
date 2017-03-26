<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/30
 * Time: 20:02
 */

namespace Plugin\Login;

use Phalcon\Mvc\Model\Validator;
use Phalcon\Mvc\EntityInterface;
use Phalcon\ValidationInterface;

class UniquenessValidator extends Validator implements EntityInterface, ValidationInterface
{
//    public function validate($record) {
////        $fieldName = $this->getOption('field');
////        if (!preg_match('/[a-z]+/', $fieldName) {
////            $this->appendMessage("The hash is not valid", $fieldName, "Hash");
////            return false;
////        }
//
//        return true;
//    }

    public function readAttribute($attribute) {

    }

    public function writeAttribute($attribute, $value) {

    }

    public function validate($data = NULL, $entity = NULL) {

    }
}

//use Phalcon\Mvc\Model\Validator,
//    Phalcon\Mvc\Model\ValidatorInterface,
//    Phalcon\Mvc\Model\Message;

//class UniquenessValidator extends Validator implements ValidatorInterface
//{
//    /**
//     * Validates that the record is unique
//     *
//     * @return boolean
//     */
//    public function validate(\Phalcon\Mvc\EntityInterface $record)
//    {
//        $field = $this->getOption('field');
//
//        $count = $record::count(array(
//            array($field => $record->readAttribute($field))
//        ));
//
//        if ($count > 0) {
//            $message = $this->getOption('message');
//            $this->appendMessage(new Message($message, $field));
//            return false;
//        }
//
//        return true;
//    }
//
//    //public function getMessages() {}
//}