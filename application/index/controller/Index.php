<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
use think\Db;
use think\Request;
use app\index\model\Course;
use app\index\model\User as UserModel;
use app\index\model\UserProfile;
class Index extends Home
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 首页
     */
    public function index()
    {
        //轮播图
        $ad = Db::name('ad')->where(['flag'=>1,'type'=>'pc'])->select();
        $this->assign('ad',$ad);

        //最新课程
        $courseModel = new Course();
        $map['status'] = 1;
        if(!empty($this->user)){
            $map['categoryId'] = $this->user->stuclass->majors;
        }
        $course = $courseModel->limit(12)->where($map)->order('createdTime desc')->select();
        $this->assign('course',$course);
        //分类

        if(!empty($this->user)){
            $condition['code'] = $this->user->stuclass->majors;
            $category = Db::name('category')->where($condition)->field('name,code')->select();
        } else{
            $category = Db::name('category')->field('name,code')->where('grade',3)->select();
        }

        $this->assign('category',$category);

        return $this->fetch();
    }
    /**
     * 全部课程
     */
    public function allcourse()
    {
        $map['status'] = 1;
        if(!empty($this->user)){
            $map['categoryId'] = $this->user->stuclass->majors;
        }
        if($this->request->isAjax()){

            $cate = $this->request->param('category');
            if(empty($cate)){
                $course = Course::order('createdTime desc')->where($map)->paginate(20);
            }
            else{
                $course = Course::where('categoryId',$cate)->where($map)->order('createdTime desc')->paginate(20);
            }

            $this->assign('course',$course);
            $this->assign('page',$course->render());

            return $this->fetch('allcourseajax');
        }

        if(!empty($this->user)){
            $condition['code'] = $this->user->stuclass->majors;
            $category = Db::name('category')->where($condition)->field('name,code')->select();
        } else{
            $category = Db::name('category')->field('name,code')->where('grade',3)->select();
        }
        $this->assign('category',$category);

        $course = Course::order('createdTime desc')->where($map)->paginate(20);

        $this->assign('course',$course);
        $this->assign('page',$course->render());

        return $this->fetch();
    }

    /**
     * 登陆
     */
    public function login()
    {

        return $this->fetch();
    }
    /**
     * 注册
     */
    public function regist()
    {

        return $this->fetch();
    }

    public function categoryajax()
    {
        $category = $this->request->param('category');
        $map['status'] = 1;
        if(empty($category)){
            if(!empty($this->user)){
                $map['categoryId'] = $this->user->stuclass->majors;
            }
            $course = Db::name('course')->order('createdTime desc')->where($map)->limit(8)->select();
        }
        else{
            if(!empty($this->user)){
                $map['categoryId'] = $this->user->stuclass->majors;
            }
            $course = Db::name('course')->where('categoryId',$category)->where($map)->order('createdTime desc')->limit(8)->select();
        }
        $this->assign('course',$course);
        return $this->fetch('categoryajax');
    }

    public function loginajax()
    {
        $data = $this->request->param();

        $allow_type = [2,3];
//        if(preg_match('/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/',$data['username'])){
//            $key = 'email';
//        }
//        elseif(preg_match('/^[1][3,4,5,7,8][0-9]{9}$/',$data['username'])){
//            $key = 'mobile';
//        }
//        else{
//            $key = 'username';
//        }
        //身份证登陆
        if(!$user_profile = UserProfile::get(['idcard'=>$data['username']])){
            return json_data(110,$this->codeMessage[110],'' );
        }

        if($user = UserModel::get($user_profile['userid'])){

            if(!in_array($user['type'],$allow_type)){
                return json_data(150,$this->codeMessage[150],'');
            }

            if($user['status']==0){
                return json_data(170,$this->codeMessage[170],'');
            }
            if(!in_array($user['type'],[2,3])){
                return json_data(171,$this->codeMessage[171],'');
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
//                $redis_key = 'wrongpwd'.$user['id'];
//                $redis = new \Redis();
//                $redis->connect('127.0.0.1', 6379);
//                if($redis->exists($redis_key)){
//                    $num = $redis->get($redis_key);
//                    $num = $num+1;
//                    if($num == 3){
//                        //locked
//                        $user->locked = 1;
//                        $user->save();
//                        $redis->delete($redis_key);
//                    }
//                    else{
//                        $redis->setex($redis_key, 86400, $num);
//                    }
//                }
//                else{
//                    $redis->setex($redis_key, 86400, 1);
//                }

                return json_data(140,$this->codeMessage[140],'');
            }
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }

    public function logout()
    {
        session('userid',null);
        $this->redirect('index/index/index');
    }

    /**
     * 注册
     */
    public function register()
    {
        $data = $this->request->param();
        $data['createdIp'] = $this->request->ip();

        $LogicLogin  = Loader::controller('Login','logic');
        $result = $LogicLogin->userAdd($data);
        return $result;
    }

    public function service()
    {
        return $this->fetch();
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
    public function loginnew()
    {
        return $this->fetch();
    }



    public function space()
    {
        return $this->fetch();
    }
    /**
     * 我的证书
     * @return mixed
     */
    public function certificate()
    {
        return $this->fetch();
    }
    /**
     * @return mixed
     */
    public function conversion()
    {
        return $this->fetch();
    }
    /**关注/粉丝
     * @return mixed
     */
    public function fans()
    {
        return $this->fetch();
    }
    /**
     * 收藏课程
     * @return mixed
     */
    public function collect()
    {
        return $this->fetch();
    }
    /**
     * 我的小组
     * @return mixed
     */
    public function group()
    {
        return $this->fetch();
    }
    /**
     * 我的积分
     * @return mixed
     */
    public function integral()
    {
        return $this->fetch();
    }

    /**
     * 课程表
     * @return mixed
     */
    public function timetable()
    {
        return $this->fetch();
    }
    /**
     * 在学班级
     * @return mixed
     */
    public function learning()
    {
        return $this->fetch();
    }
    /**
     * 在学课程
     * @return mixed
     */
    public function curriculum()
    {
        return $this->fetch();
    }

    public function testajax()
    {
        if($this->request->isAjax()){
            return 1;
        }
        else {
            return 0;
        }
    }

}
