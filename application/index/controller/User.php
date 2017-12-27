<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
use think\Request;

use app\index\model\User as UserModel;
class User extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    public function setting(){
        return $this->fetch();
    }

    public function email(){
        return $this->fetch();
    }
    public function password(){
        return $this->fetch();
    }
    public function portrait(){
        return $this->fetch();
    }
    public function security(){
        return $this->fetch();
    }
    public function submit(){
        return $this->fetch();
    }

    public function space(){
        return $this->fetch();
    }

    public function userlayout(){
        return $this->fetch();
    }


}
