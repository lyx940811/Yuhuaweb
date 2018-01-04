<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
use think\Request;
use app\index\model\Course;
use app\index\model\User as UserModel;
class Usercenter extends Home
{
    public $theuser;
    public function __construct()
    {
        parent::__construct();
        $userid = $this->request->param('user');
        $this->theuser = \app\index\model\User::get($userid);
        $this->assign('theuser',$this->theuser);
    }


    public function space(){

        return $this->fetch();
    }
    /**
     * 我的证书
     * @return mixed
     */
    public function certificate(){
        return $this->fetch();
    }
    /**
     * @return mixed
     */
    public function conversion(){
        return $this->fetch();
    }
    /**关注/粉丝
     * @return mixed
     */
    public function fans(){
        return $this->fetch();
    }
    /**
     * 收藏课程
     * @return mixed
     */
    public function collect(){
        return $this->fetch();
    }
    /**
     * 我的小组
     * @return mixed
     */
    public function group(){
        return $this->fetch();
    }
    /**
     * 我的积分
     * @return mixed
     */
    public function integral(){
        return $this->fetch();
    }

    /**
     * 课程表
     * @return mixed
     */
    public function timetable(){
        return $this->fetch();
    }
    /**
     * 在学班级
     * @return mixed
     */
    public function learning(){
        return $this->fetch();
    }
    /**
     * 在学课程
     * @return mixed
     */
    public function curriculum(){
        return $this->fetch();
    }

}
