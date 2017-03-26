<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/8/17
 * Time: 18:04
 */

namespace Plugin\Rbac;

class RbacException extends \Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class RbacRoleNotFoundException extends RbacException
{
}

class RbacPermissionNotFoundException extends RbacException
{
}