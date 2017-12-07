<?php
namespace app\manage\controller;
use app\manage\model\User;
use think\captcha\Captcha;
use think\Controller;
use think\Request;
use think\Validate;

/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/6
 * Time: 11:14
 */
//登陆类
class Login extends Controller{

    //登陆首页
    public function index(){
//        import('Captcha','captcha.Captcha');

//        $aa = new Captcha((array)Config::get('captcha'));


        if(Request::instance()->isPost()){

            $info = input('post.');//接收值
//            $user->nickname = input('post.username','','htmlspecialchars');
//            $user->password = input('post.pwd');

            $name = $this->request->post("username");

            $rule =   [
                'nickname'  => 'require|length:1,50|token', //我这里的token 是令牌验证
                'password'   => 'require',
            ];
            //错误信息提示
            $msg  =   [
                'nickname.require' => '登录账号不能为空',
                'password.require' => '密码不能为空',
            ];
            $validate = new Validate($rule,$msg);
            $validate->check($info);
            $error = $validate->getError();//打印错误规则

            if (strpos($name, "@") > 0) {//邮箱登陆
                $where['nickname'] = $name;
            } else {
                $where['password'] = $name;
            }

//            $password = User::get(['password'=>input('post.password','','htmlspecialchars')]);//原始密码
//            $hash_password = password_hash(Db::query,PASSWORD_BCRYPT);//使用BCRYPT算法加密密码
//            if (password_verify($password , $hash_password)){
//                echo "密码匹配";
//            }else{
//                echo "密码错误";
//            }
        }else{
            return view('index');
        }

    }

    public function captcha(){
        $aa = new Captcha();
    }

}