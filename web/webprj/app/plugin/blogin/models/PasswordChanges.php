<?php
namespace Plugin\Login\Models;
use Phalcon\Mvc\Model;

class PasswordChanges extends Model
{
    public $id;
    public $usersId;
    public $ipAddress;
    public $userAgent;
    public $createdAt;

    public function beforeValidationOnCreate()
    {
        // Timestamp the confirmaton
        $this->createdAt = time();
    }

    public function initialize()
    {
        $this->setSource("hl_password_changes");
        $this->belongsTo('usersId', __NAMESPACE__ . '\Users', 'id', array(
            'alias' => 'user'
        ));
    }
}
