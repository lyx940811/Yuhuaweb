<?php
namespace app\admin\controller;
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
        echo '请先登陆.<br/>';
        $info = input('post.');//接收值

        $rule =   [
            'nickname'  => 'require|length:1,50', //我这里的token 是令牌验证
            'password'   => 'require',
        ];
        //错误信息提示
        $msg  =   [
            'nickname.require' => '登录账号不能为空',
            'password.require' => '密码不能为空',
        ];
        //  实例化验证类
        $validate = new Validate($rule, $msg);
        $result   = $validate->check($info);
        var_dump($result);
        $error = $validate->getError();//打印错误规则
        if(empty($error)){
            echo '验证通过';
        }else{
            echo $error; //错误信息
        }
    }

}