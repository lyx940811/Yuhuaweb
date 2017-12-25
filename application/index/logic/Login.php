<?php

namespace app\index\logic;

use app\index\model\User as UserModel;
use think\Request;
use think\Loader;
use think\Validate;
class Login extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建用户
     * @param $data
     * @return array
     */
    public function userAdd($data){
        $data['password']       =   password_hash($data['password'],PASSWORD_DEFAULT);
        $data['title']          =   'static\index\images\avatar.png';
        $data['type']           =   3;
        $data['createdTime']    =   date('Y-m-d H:i:s');
        //is user exist?
        if(UserModel::get(['email'=>$data['email']])){
            return json_data(120,$this->codeMessage[120],'');
        }
        if(UserModel::get(['username'=>$data['username']])){
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
                $result = UserModel::create($data);
                if($result){
                    return json_data(0,$this->codeMessage[0],$result);
                }
                else{
                    return json_data(100,$this->codeMessage[100],'');
                }
            }
        }
    }

    /**
     * 用户登录
     * @param $data
     * @return array
     */
    public function userLogin($data){
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
                $key = [
                    'id'=>'',
                    'nickname'=>'',
                    'mobile'=>'',
                    'username'=>'',
                    'password'=>'',
                    'title'=>'',
                    'type'=>'',
                ];
                $user = $user->toArray();

                $user = array_intersect_key($user,$key);
                return json_data(0,$this->codeMessage[0],$user);
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

    /**
     * 发送重置密码的邮件
     * @param $email
     * @return array
     */
    public function sendChEmail($email){
        if($user = UserModel::get(['email'=> $email ])){
            //send email
            $title   = '【豫化在线】找回您的帐户密码';
            $content = '亲爱的用户 '.$user['nickname'].'：您好！您的密码重置地址为：http://www.baidu.com?email='.base64_encode($email);
            if(send_email($email,$title,$content)){
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

    /**
     * 通过邮件跳转的重置密码页面进行重置密码
     * @param $email
     * @param $password
     * @return array
     */
    public function ChUserPassword($email,$password){
        if(!$user = UserModel::get([ 'email' => $email ])){
            return json_data(110,$this->codeMessage[110],'');
        }

        $validate = new Validate([
            'email'      => 'require|email',
            'password'   => 'require|length:1,100',
        ]);
        $data = [
            'email'     =>  $email,
            'password'  =>  $password
        ];

        if(!$validate->check($data)){
            // 验证失败 输出错误信息
            return json_data(130,$validate->getError(),'');
        }
        else{
            //update password
            $user = new UserModel();
            $user->save(
                ['password'=>password_hash($data['password'],PASSWORD_DEFAULT)],
                ['email'=>$email]);

            $newuser = UserModel::get(['email'=>$data['email']]);
            $key = [
                'id'=>'',
                'nickname'=>'',
                'mobile'=>'',
                'username'=>'',
                'password'=>'',
                'title'=>'',
                'type'=>'',
            ];
            $newuser = $newuser->toArray();
            $newuser = array_intersect_key($newuser,$key);
            return json_data(0,$this->codeMessage[0],$newuser);
        }
    }



}
?>
