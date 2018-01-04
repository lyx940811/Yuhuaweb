<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
use think\Request;

use app\index\model\User as UserModel;
class Student extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    public function mystudy(){
        return $this->fetch();
    }
    public function discussions(){
        return $this->fetch();
    }
    public function homeworkfirst(){
        return $this->fetch();
    }
    public function questions(){
        return $this->fetch();
    }





}
