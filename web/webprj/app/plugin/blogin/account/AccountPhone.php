<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/22
 * Time: 17:54
 */

namespace Plugin\Login\Account;

class AccountPhone extends AccountType implements IAccountType
{
    public function __construct()
    {
        parent::__construct("phone");
    }

    public function check()
    {
        // TODO: Implement check() method.
        return true;
    }
}