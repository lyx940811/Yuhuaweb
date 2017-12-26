<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2017/12/25
 * Time: 12:13
 */
namespace app\manage\controller;

use think\captcha\Captcha;
use think\Controller;

class Test extends Controller{
    public function index(){
        $aa = new Captcha();
        return $aa->entry();
    }


    public function checkc(){
        $info = input('post.');//接收值

        return 1;
//

//        if(!captcha_check($info['captcha'])){
//            //验证码错误
//            return ['error'=>'验证码错误','code'=>200];
//        }
        return ['info'=>'success'];
    }
}