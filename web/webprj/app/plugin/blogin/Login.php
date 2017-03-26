<?php
namespace Plugin\Login;

use Phalcon\Mvc\User\Component;
use Plugin\Core\QSTBaseLogger;
use Plugin\Login\Models\Users;
use Plugin\Login\Models\RememberTokens;
use Plugin\Login\Models\SuccessLogins;
use Plugin\Login\Models\FailedLogins;
use Plugin\Misc\FileManager;

/**
 * Manages Authentication/Identity Management
 */
class Login extends Component
{
    const refresh_time = 86400;
    const expired_time = 691200; // 86400 * 8
    /**
     * @param $user
     * @throws LoginException
     * 登录成功记录
     */
    private function saveSuccessLogin($user)
    {
        $successLogin = new SuccessLogins();
        $successLogin->usersId = $user->id;
        $successLogin->ipAddress = $this->request->getClientAddress();
        $successLogin->userAgent = $this->request->getUserAgent();
        if (!$successLogin->save()) {
            $messages = $successLogin->getMessages();
            throw new LoginException($messages[0]);
        }
    }

    /**
     * @param int $userId
     * 登录失败记录，并通过延迟防止用户连续登录
     */
    private function registerUserThrottling($userId)
    {
        $failedLogin = new FailedLogins();
        $failedLogin->usersId = $userId;
        $failedLogin->ipAddress = $this->request->getClientAddress();
        $failedLogin->attempted = time();
        $failedLogin->save();

        $attempts = FailedLogins::count(array(
            'ipAddress = ?0 AND attempted >= ?1',
            'bind' => array(
                $this->request->getClientAddress(),
                time() - 3600 * 6
            )
        ));

        switch ($attempts) {
            case 1:
            case 2:
                // no delay
                break;
            case 3:
            case 4:
                sleep(2);
                break;
            default:
                sleep(4);
                break;
        }
    }

    /**
     * @param $userId string
     * 创建用户记住登录的内容
     */
    private function createRememberEnvironment($userId)
    {
        $token = FileManager::createGuid($userId);

        $remember = new RememberTokens();
        $remember->usersId = $userId;
        $remember->token = $token;
        $remember->userAgent = $this->request->getUserAgent();
        $remember->sessionId = $this->session->getId();

        if ($remember->save() != false) {
            $expire = time() + self::expired_time;
            $this->cookies->set('RMU', $userId, $expire);
            $this->cookies->set('RMT', $token, $expire);
        }
    }

    /**
     * @param  $user
     * @throws LoginException
     * 检测用户状态，是否可用
     */
    private function checkUserFlags(Users $user)
    {
        if (!$user->activity) {
            throw new LoginException('该用户已停用');
        }
    }

    /**
     * @param $credentials
     * @return bool
     * @return integer
     * 用户创建或注册
     */
    public function signup($credentials) {
        $user = Users::findFirstByAccount($credentials['account']);
        if (!$user) {
            $user = new Users();
            /*$user->account = $credentials['account'];
            $user->name = $credentials['name'];
            $user->tel = $credentials['tel'];
            $user->password = $this->security->hash($credentials['password']);
            $user->role_id = $credentials['role_id'];
            $user->role_name = $credentials['role_name'];check
            $user->Relation_id = $credentials['Relation_id'];
            $user->create_time = time();*/
            $user->assign(array(
                'account' => $credentials['account'],
                'name' => $credentials['name'],
                'tel' => $credentials['tel'],
                'password' => $this->security->hash($credentials['password']),
                'role_id' => $credentials['role_id'],
                'role_name' => $credentials['role_name'],
                'Relation_id' => $credentials['Relation_id'],
                'create_time' => time()
            ));
            if ($user->save()) {
                return 0;
            }
            /*print_r($user->getMessages());exit;
            foreach ($user->getMessages() as $message) {
                QSTBaseLogger::getDefault()->log($message);
                $this->flash->error($message);
            }*/
            return -2;
        }else{
            QSTBaseLogger::getDefault()->log("use exist, " . $credentials['account']);
            return -1;
        }
    }

    /**
     * 用户登录
     * @param array $credentials
     * @return boolean
     * @throws LoginException
     */
    public function check($credentials)
    {
        // Check if the user exist
        $user = Users::findFirstByAccount($credentials['account']);
        if ($user == false) {
            $this->registerUserThrottling(0);
            throw new LoginException('账户不存在');
        }

        // Check the password
        if (!$this->security->checkHash($credentials['password'], $user->password)) {
            $this->registerUserThrottling($user->id);
            throw new LoginException('密码错误');
        }

        // Check if the user was flagged
        $this->checkUserFlags($user);

        // Register the successful login
        $this->saveSuccessLogin($user);

        // Check if the remember me was selected
        if (isset($credentials['remember'])) {
            $this->createRememberEnvironment($user->id);
        }

        $this->session->set('auth-identity', array(
            'id' => $user->id,
            'account' => $user->account,
            'name' => $user->name,
            'profile' => $user->role_id,
            'ispd' => $user->Relation_id,
        ));
    }

    /**
     * @return boolean
     * 检测是否用户记住登录
     */
    public function hasRememberMe()
    {
        return $this->cookies->has('RMU');
    }

    /**
     * Logs on using the information in the cookies
     * @return mixed
     * @throws LoginException
     * 记住用户登录方式登录
     */
    private function loginWithRememberMe()
    {
        $userId = $this->cookies->get('RMU')->getValue();
        $cookieToken = $this->cookies->get('RMT')->getValue();

        $remember = RememberTokens::findFirst(array(
            'conditions' => 'token = :token: and usersId = :uid:',
            'bind' => array('token' => $cookieToken, 'uid' => $userId),
            'for_update' => true
        ));
        if($remember && isset($remember->id)){//有匹配记录
            $time = time();
            if($time > $remember->createdAt + self::expired_time){//登陆已过期
                return false;
            }
            if($time > $remember->createdAt + self::refresh_time){//超过一天，生成新token
                $remember->token = FileManager::createGuid($remember->usersId);
                $remember->userAgent = $this->request->getUserAgent();
                $remember->createdAt = $time;
                $remember->sessionId = $this->session->getId();
                if(false == $remember->save()){
                    QSTBaseLogger::getDefault()->log("save token failed");
                    return false;
                }
                $expire = $time + self::expired_time;
                $this->cookies->set('RMU', $remember->usersId, $expire);
                $this->cookies->set('RMT', $remember->token, $expire);
            }else{
                //使用就得token，do nothing
            }
            $user = Users::findFirstById($userId);
            if(!isset($user->id)){
                QSTBaseLogger::getDefault()->log("no matched user found, but there is record in remember toekn", \Phalcon\Logger::ALERT);
                return false;
            }
            if(0 == $user->activity){
                QSTBaseLogger::getDefault()->log("user $user->userId was disabled");
                return false;
            }
            $this->saveSuccessLogin($user);
            $this->session->set('auth-identity', array(
                'id' => $user->id,
                'account' => $user->account,
                'name' => $user->name,
                'profile' => $user->role_id
            ));
            return true;
        }else{//无匹配记录，清除cookie
            $this->cookies->get('RMU')->delete();
            $this->cookies->get('RMT')->delete();
            return false;
        }
    }

    /**
     * 检测是否已登录或满足自动登陆状态，如满足则自动登陆并返回auth-identityx信息，否则返回false
     * @return bool|mixed
     */
    public function getIdentity()
    {
        if(!$this->session->has('auth-identity')){//session 过期
            if($this->cookies->has('RMU')){
                if(true != $this->loginWithRememberMe()) {
                    return false;
                }
            }else{//用户未记住登陆状态
                return false;
            }
        }
        return $this->session->get('auth-identity');
    }

    /**
     * @return string
     * 返回用户姓名
     */
    public function getId()
    {
        $identity = $this->getIdentity();
        return $identity['id'];
    }

    /**
     * @deprecated，提供该接口会使与该参数需该相关的页面变得与session状态强关联，涉及信息修改的接口逻辑会比较复杂且容易出错
     * @return string
     * 返回用户姓名
     */
    public function getName()
    {
        $identity = $this->getIdentity();
        return $identity['name'];
    }

    /**
     * 删除用户记住登录信息，用于退出登录时调用
     */
    public function remove()
    {
        if ($this->cookies->has('RMU')) {
            $rmu = $this->cookies->get('RMU');
            $uid = $rmu->getValue();
            $rmu->delete();
        }
        if ($this->cookies->has('RMT')) {
            $rmt = $this->cookies->get('RMT');
            $token = $rmt->getValue();
            $rmt->delete();
        }
        if(isset($uid) && isset($token)){
            $remberToken = RememberTokens::findFirst(array(
                'conditions' => 'token = :token: and usersId = :uid:',
                'bind' => array('token' => $token, 'uid'=>$uid),
                'for_update' => true
            ));
            if(isset($remberToken->id)){
                $remberToken->delete();
            }
        }
        $this->session->remove('auth-identity');
    }

    /**
     * @deprecated 没看明白应用场景
     * @param int $id
     * @throws LoginException
     * 通过用户id进行登录鉴权
     */
    public function authUserById($id)
    {
        $user = Users::findFirstById($id);
        if ($user == false) {
            throw new LoginException('The user does not exist');
        }

        $this->checkUserFlags($user);

        $this->session->set('auth-identity', array(
            'id' => $user->id,
            'name' => $user->name,
            'profile' => $user->role_id
        ));
    }

    /**
     * @return bool
     * @throws LoginException
     * 获取用户信息
     */
    public function getUser()
    {
        $identity = $this->session->get('auth-identity');
        if (isset($identity['id'])) {

            $user = Users::findFirstById($identity['id']);
            if ($user == false) {
                throw new LoginException('用户不存在');
            }

            return $user;
        }

        return false;
    }

    public function getUsers() {
        $usersArray = [];
        $users = Users::find();
        if ($users) {
            $usersArray = $users->toArray();
        }

        for ($i = 0; $i < count($usersArray); $i++) {
            $usersArray[$i]["login_time"] = SuccessLogins::getDataTimeOfLastLogin($usersArray[$i]["id"]);
        }

        return $usersArray;
    }

    public function getUsersWith($key, $value) {
        $users = null;
        if (empty($key)) {
            $users = Users::findFromAll($value);
        } else {
            $users = Users::findFrom($key, $value);
        }

        $usersArray = $users->toArray();

        for ($i = 0; $i < count($usersArray); $i++) {
            $usersArray[$i]["login_time"] = SuccessLogins::getDataTimeOfLastLogin($usersArray[$i]["id"]);
        }

        return $usersArray;
    }

    public function getUserById($value) {
        return Users::findFirstById($value);
    }

    public function enableManager($id, $activity) {
        $manager = Users::findFirstById($id);
        if ($manager) {
            $manager->activity = $activity;
            if (!$manager->save()) {
                foreach ($manager->getMessages() as $message) {
                    $this->flash->error($message);
                }
                return false;
            }

            return true;
        }
        return false;
    }

    public function deleteUserById($id) {
        $manager = Users::findById($id);
        if ($manager) {
            $manager->delete();
        }
    }

    /**
     * 设置新的密码
     * @param $id integer, 用户id
     * @param $password string, 新密码
     * @return bool, true on success and false on failed.
     */
    public function setPassword($id, $password)
    {
        $user = Users::findFirst(array('conditions' => 'id = :id:', 'bind'=>array('id' => $id), "for_update"=>true));
        if(!isset($user->id)){
            QSTBaseLogger::getDefault()->log("no matched user found, id: $id", \Phalcon\Logger::ALERT);
            return false;
        }
        $user->password = $this->security->hash($password);
        $user->save();
        //密码变更，清除旧的session和token，强制旧的记住登陆状态失效。
        $this->flushSession($id, true);
        return true;
    }

    /**
     * 跟新用户账户信息
     * @param $id integer, 用户id
     * @param $name string, 用户名
     * @param $roleId string, 用户角色id
     * @param $roleName string，用户角色
     * @return bool, true on success, false on failed.
     */
    public function updateAccount($id, $name, $roleId, $roleName)
    {
        $user = Users::findFirst(array('conditions' => 'id = :id:', 'bind'=>array('id' => $id), "for_update"=>true));
        if(!isset($user->id)){
            QSTBaseLogger::getDefault()->log("no matched user found, id: $id", \Phalcon\Logger::ALERT);
            return false;
        }
        $user->name = $name;
        $roleChange = !($user->role_id == $roleId);
        $user->role_id = $roleId;
        $user->role_name = $roleName;
        if($roleChange){//由于角色信息缓存在session中，角色变化时需要清除session来强制重新获取新的角色信息
            $this->flushSession($id);
        }
        return $user->save();
    }

    private function flushSession($id, $deleteToken = false)
    {
        $records = RememberTokens::find(array('conditions' => 'usersId = :uid:', "bind" => array('uid'=>$id)));
        foreach ($records as $record){
            $sessionFile = _SESSION_PATH_ . "/sess_" .$record->sessionId;
            if(file_exists($sessionFile)){ //@TODO 对未记录登录状态的session无效，暂未想到有效解决方案
                $ret = unlink($sessionFile);
            }
            if(true == $deleteToken){
                $record->delete();
            }
        }
    }
}
