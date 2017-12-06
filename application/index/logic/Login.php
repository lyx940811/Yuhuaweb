<?php

namespace app\index\logic;

use app\index\model\User;

use think\Loader;
use think\Validate;
class Login extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function userAdd($data){
        $data['password']       =   password_hash($data['password'],PASSWORD_DEFAULT);
        $data['title']          =   123;
        $data['type']           =   1;
        $data['createdTime']    =   date('Y-m-d H:i:s');
        //is user exist?
        if(User::get(['email'=>$data['email']])){
            return json_data(120,$this->codeMessage[120],'');
        }
        else{
            //verified data
            $validate = Loader::validate('User');
            if(!$validate->check($data)){
                return json_data(130,$validate->getError(),'');
            }
            else{
                //add data
                $result = User::create($data);
                if($result){
                    return json_data(0,$this->codeMessage[0],$result);
                }
                else{
                    return json_data(100,$this->codeMessage[100],'');
                }
            }
        }
    }

    public function userLogin($data){
        $user = User::get(['email'=>$data['email']]);
        if($user){
            if(password_verify($data['password'],$user['password'])){
                //需要对返回数据进行整理
                //strcmp($user['password'],md5($data['password']))==0


                return json_data(0,$this->codeMessage[0],$user);
            }
            else{
                return json_data(140,$this->codeMessage[140],'');
            }
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }

    public function sendChEmail($data){
        if(User::get(['email'=>$data['email']])){
            //send email
            $title = '密码重置';
            $content = '您的密码重置地址为：http://www.baidu.com';
            if(send_email($data['email'],$title,$content)){
                return json_data(0,$this->codeMessage[0],'');
            }
            else{
                return json_data(800,$this->codeMessage[800],'');
            }
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }

    public function ChUserPassword($data){
        $validate = new Validate([
            'email'      => 'require|email',
            'password'   => 'require|length:1,100',
        ]);

        if(!$user = User::get([ 'email' => $data['email'] ])){
            return json_data(110,$this->codeMessage[110],'');
        }

        if(!$validate->check($data)){
            // 验证失败 输出错误信息
            return json_data(130,$validate->getError(),'');
        }
        else{
            //update password
            $user = new User();
            $user->save(['password'=>md5($data['password'])],['email'=>$data['email']]);
            return json_data(0,$this->codeMessage[0],'');
        }
    }



}
?>
