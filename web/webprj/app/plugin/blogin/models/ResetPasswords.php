<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/17
 * Time: 11:44
 */

namespace Plugin\Login\Models;
use Phalcon\Mvc\Model;

class ResetPasswords extends Model
{
    public $id;
    public $usersId;
    public $code;
    public $createdAt;
    public $modifiedAt;
    public $reset;

    /**
     * Before create the user assign a password
     */
    public function beforeValidationOnCreate()
    {
        // Timestamp the confirmaton
        $this->createdAt = time();

        // Generate a random confirmation code
        $this->code = preg_replace('/[^a-zA-Z0-9]/', '', base64_encode(openssl_random_pseudo_bytes(24)));

        // Set status to non-confirmed
        $this->reset = 'N';
    }

    /**
     * Sets the timestamp before update the confirmation
     */
    public function beforeValidationOnUpdate()
    {
        // Timestamp the confirmaton
        $this->modifiedAt = time();
    }

    /**
     * Send an e-mail to users allowing him/her to reset his/her password
     */
    public function afterCreate()
    {
//        $this->getDI()
//            ->getMail()
//            ->send(array(
//                $this->user->email => $this->user->name
//            ), "Reset your password", 'reset', array(
//                'resetUrl' => '/reset-password/' . $this->code . '/' . $this->user->email
//            ));
    }

    public function initialize()
    {
        $this->setSource("hl_reset_passwords");
        $this->belongsTo('usersId', __NAMESPACE__ . '\Users', 'id', array(
            'alias' => 'user'
        ));
    }
}
