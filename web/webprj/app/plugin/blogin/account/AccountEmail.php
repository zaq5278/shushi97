<?php

/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/22
 * Time: 17:53
 */

namespace Plugin\Login\Account;

class AccountEmail extends AccountType implements IAccountType
{
    public function __construct()
    {
        parent::__construct("email");
    }

    public function check()
    {
        // TODO: Implement check() method.
        return true;
    }
}