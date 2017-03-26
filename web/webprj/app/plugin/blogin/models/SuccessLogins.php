<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/17
 * Time: 11:44
 */

namespace Plugin\Login\Models;
use Phalcon\Mvc\Model;

class SuccessLogins extends Model
{
    public $id;
    public $usersId;
    public $ipAddress;
    public $userAgent;

    public function initialize()
    {
        $this->setSource("hl_success_logins");
        $this->belongsTo('usersId', __NAMESPACE__ . '\Users', 'id', array(
            'alias' => 'user'
        ));
    }

    public function beforeValidationOnCreate() {
        $this->createTime = date("Y-m-d H:i:s", time());
    }

    /**
     * 根据用户id获取最后登录时间
     * @param $idUser
     * @return null
     */
    public static function getDataTimeOfLastLogin($idUser) {
        $loginRecord = self::findFirst([
            "usersId = " . $idUser,
            "order" => "id DESC"
        ]);

        if ($loginRecord) {
            return $loginRecord->createTime;
        }

        return null;
    }
}
