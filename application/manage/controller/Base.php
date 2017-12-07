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

    protected $access_token = ACCESS_TOKEN;
    public function _initialize(){
        //判断登陆没登陆
        echo '请先登陆.<br/>';

    }

}