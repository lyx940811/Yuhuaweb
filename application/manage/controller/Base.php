<?php
namespace app\manage\controller;
use think\Controller;
use think\Validate;

/**
 * Created by phpstorm.
 * User: m's
 * Date: 2017/12/5
 * Time: 17:19
 */
class Base extends Controller{

    public $uid;

    public function _initialize(){
        //判断登陆没登陆
        $this->uid = session('admin_uid');
        if(!$this->uid){
            $this->error('请先登陆',url('Manage/login/index'));
        }

        $role = check($this->uid);

        if(!$role){
            $this->error('您没权限查看',url('Manage/login/index'));
        }


    }


}