<?php
namespace Plugin\Login\Models;

use Phalcon\Mvc\Model;

class RememberTokens extends Model
{
    public $id;
    public $usersId;
    public $token;
    public $userAgent;
    public $createdAt;
    /**
     * @var string sessionid
     */
    public $sessionId;

    public function beforeValidationOnCreate()
    {
        // Timestamp the confirmaton
        $this->createdAt = time();
    }

    public function initialize()
    {
        $this->setSource("hl_remember_tokens");
        $this->belongsTo('usersId', __NAMESPACE__ . '\Users', 'id', array(
            'alias' => 'user'
        ));
    }

    /**
     * 删除用户记住登陆状态相关的session和token，管理员密码重置
     * @param $userId int 用户id
     */
    public static function forceDelete($userId)
    {
        $records = self::find(array('conditions' => 'userId = :uid:', 'for_update' => true, 'bind'=>array('uid'=>$userId)));
        foreach ($records as $record){
            if($record instanceof RememberTokens){
                $sessionFile = _SESSION_PATH_ . "sess_" . $record->sessionId;
                if (file_exists($sessionFile)) {
                    unlink($sessionFile);
                }
                $record->delete();
            }
        }
    }
}
