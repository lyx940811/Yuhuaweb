<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
use think\Request;
use app\index\model\Course;
use app\index\model\User as UserModel;
class Index extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 首页
     */
    public function index(){
        $courseModel = new Course();
        $course = $courseModel->where('is_new',1)->limit(12)->order('createdTime desc')->select();
        $this->assign('course',$course);

        return $this->fetch();
    }

    /**
     * 登陆
     */
    public function login(){

        return $this->fetch();
    }
    public function loginajax(){
        $data = $this->request->param();

        $allow_type = [2,3];
        if(preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/',$data['username'])){
            $key = 'email';
        }
        elseif(preg_match('/^[1][3,4,5,7,8][0-9]{9}$/',$data['username'])){
            $key = 'mobile';
        }
        else{
            $key = 'username';
        }
        if($user = UserModel::get([ $key => $data['username'] ])){

            if(!in_array($user['type'],$allow_type)){
                return json_data(150,$this->codeMessage[150],'');
            }

            if($user['status']==0){
                return json_data(170,$this->codeMessage[170],'');
            }

            if($user['locked']==1){
                return json_data(160,$this->codeMessage[160],'');
            }

            if(password_verify($data['password'],$user['password'])){
                //需要对返回数据进行整理，这里需要改成只返回access_token
                session('userid',$user['id']);
                return json_data(0,$this->codeMessage[0],'');

            }
            else{
                //密码错误，次数+1，到达3的时候锁定
                $redis_key = 'wrongpwd'.$user['id'];
                $redis = new \Redis();
                $redis->connect('127.0.0.1', 6379);
                if($redis->exists($redis_key)){
                    $num = $redis->get($redis_key);
                    $num = $num+1;
                    if($num == 3){
                        //locked
                        $user->locked = 1;
                        $user->save();
                        $redis->delete($redis_key);
                    }
                    else{
                        $redis->setex($redis_key, 86400, $num);
                    }
                }
                else{
                    $redis->setex($redis_key, 86400, 1);
                }

                return json_data(140,$this->codeMessage[140],'');
            }
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }

    public function logout(){
        session('userid',null);
        $this->redirect('index/index/index');
    }

    /**
     * 注册
     */
    public function register(){
        $data = $this->request->param();
        $data['createdIp'] = $this->request->ip();

        $LogicLogin  = Loader::controller('Login','logic');
        $result = $LogicLogin->userAdd($data);
        return $result;
    }

    /**
     * 找回密码
     */
    public function findaccount(){}

    /**
     * 重置密码
     */
    public function reset(){}

    public function layout(){
        return $this->fetch();
    }
    public function loginnew(){
        return $this->fetch();
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
