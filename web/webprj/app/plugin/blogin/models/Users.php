<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/17
 * Time: 11:41
 */

namespace Plugin\Login\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Di;
use Phalcon\Validation;


class Users extends Model
{
    /**
     * @var integer 自增变量
     */
    public $id;
    /**
     * @var string 姓名或昵称
     */
    public $name;
    /**
     * @var string 手机号
     */
    public $tel;
    /**
     * @var string 用户登录账户
     */
    public $account;
    /**
     * @var string 用户登录密码
     */
    public $password;
    /**
     * @var integer 用户角色
     */
    public $role_id;

    /**
     * @var string 角色创建时间
     */
    public $create_time;
    /**
     * @var integer 账户状态，账户是否已激活
     */
    public $activity;
    /**
     * @var string 角色名
     */
    public $role_name;
    /**
     * @var string 关联仓库或者加盟店ID
     */
    public $Relation_id;


    /**
     * Before create the user assign a password
     */
    public function beforeValidationOnCreate()
    {
        if (empty($this->password)) {

            // Generate a plain temporary password
            $tempPassword = "123456";//preg_replace('/[^a-zA-Z0-9]/', '', base64_encode(openssl_random_pseudo_bytes(12)));

            // Use this password as default
            $this->password = $this->getDI()
                ->getSecurity()
                ->hash($tempPassword);
        }

        $this->create_time = time();
        // The account must be confirmed via e-mail
        $this->activity = true;

        // The account is not suspended by default
//        $this->suspended = 'N';

        // The account is not banned by default
//        $this->banned = 'N';
    }

    /**
     * Validate that account across users
     */
    public function validation()
    {
//        $validUn = new UniquenessValidator(array(
//            "field" => "account",
//            "message" => "The account is already exist"
//        ));
//        $this->validate($validUn);
//        return $this->validationHasFailed() != true;
        return true;
    }

    public function initialize() {
        $this->setSource("hl_manager_users");
//        $this->hasMany('id', __NAMESPACE__ . '\SuccessLogins', 'usersId', array(
//            'alias' => 'successLogins',
//            'foreignKey' => array(
//                'message' => '请先删除SuccessLogins表中相关数据'
//            )
//        ));
//
//        $this->hasMany('id', __NAMESPACE__ . '\PasswordChanges', 'usersId', array(
//            'alias' => 'passwordChanges',
//            'foreignKey' => array(
//                'message' => '请先删除passwordChanges表中相关数据'
//            )
//        ));
//
//        $this->hasMany('id', __NAMESPACE__ . '\ResetPasswords', 'usersId', array(
//            'alias' => 'resetPasswords',
//            'foreignKey' => array(
//                'message' => '请先删除resetPasswords表中相关数据'
//            )
//        ));
    }

    static function findFromAll($value) {
        $where = array(
            "id = :id: OR account like :value: OR name like :value:",
            'bind' => array(
                "id" => $value,
                "value" => '%' . $value . "%"
            )
        );
        return self::find($where);
    }

    static function findFrom($key, $value) {
        $post = array($key => $value);
        $phql = Criteria::fromInput(Di::getDefault(), __NAMESPACE__ . '\Users', $post);
        return $phql->execute();
    }
}
