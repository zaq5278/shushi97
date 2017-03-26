<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace Plugin\Login\Models;
use Phalcon\Mvc\Model;

class FailedLogins extends Model
{
    public $id;
    public $usersId;
    public $ipAddress;
    public $attempted;

    public function initialize()
    {
        $this->setSource("hl_failed_logins");
        $this->belongsTo('usersId', __NAMESPACE__ . '\Users', 'id', array(
            'alias' => 'user'
        ));
    }
}
