<?php

/**
 * Created by PhpStorm.
 * User: miaogang
 * Date: 2016/8/22
 * Time: 17:52
 */

namespace Plugin\Login\Account;

interface IAccountType {
    public function check();
}

class AccountType {
    protected $accountType;
    protected function __construct($type) {
        $this->accountType = $type;
    }

    public function getType() {
        return $this->accountType;
    }
}
