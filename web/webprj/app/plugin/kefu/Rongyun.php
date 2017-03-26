<?php
namespace Plugin\kefu;
    
class Rongyun
{
    private $user;
    private $SendRequest;
    public function __construct($appKey, $appSecret, $format = 'json') {
        require_once(APP_DIR."/plugin/kefu/rongyun/SendRequest.php");
        $this->SendRequest = new \SendRequest($appKey, $appSecret, $format);
    }
    
    public function User() {
        require_once(APP_DIR."/plugin/kefu/rongyun/methods/User.php");
        if (empty($this->user)) {
            $this->user = new \User($this->SendRequest);
        }
        return $this->user;
    }
    
    public function Message() {
        $Message = new Message($this->SendRequest);
        return $Message;
    }
    
    public function Wordfilter() {
        $Wordfilter = new Wordfilter($this->SendRequest);
        return $Wordfilter;
    }
    
    public function Group() {
        $Group = new Group($this->SendRequest);
        return $Group;
    }
    
    public function Chatroom() {
        $Chatroom = new Chatroom($this->SendRequest);
        return $Chatroom;
    }
    
    public function Push() {
        $Push = new Push($this->SendRequest);
        return $Push;
    }
    
    public function SMS() {
        $SMS = new SMS($this->SendRequest);
        return $SMS;
    }
    
}