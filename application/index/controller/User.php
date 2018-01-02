<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
use think\Request;
use app\index\model\User as UserModel;
use app\index\model\UserProfile;
class User extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    public function setting(){
        $profile = UserProfile::get(['userid'=>UID]);
        $this->assign('profile',$profile);
        return $this->fetch();
    }

    public function email(){
        return $this->fetch();
    }
    public function password(){
        return $this->fetch();
    }
    public function portrait(){
        return $this->fetch();
    }
    public function security(){
        return $this->fetch();
    }
    public function submit(){
        return $this->fetch();
    }

    public function space(){
        return $this->fetch();
    }

    public function userlayout(){
        return $this->fetch();
    }

    public function attention(){
        $data = $this->request->param();
        $file = $_FILES;
        var_dump($data,$file);
    }

    /**
     * 个人设置页面的ajax
     * @return array
     */
    public function settingajax(){
        $data = $this->request->param();
        $user_key = ["nickname"=>""];
        $user_data = array_intersect_key($data,$user_key);
        $profile_data = array_diff_key($data,$user_key);
        $user = UserModel::update($user_data,['id'=>UID]);
        $profile = UserProfile::update($profile_data,['userid'=>UID]);
        if($user&&$profile){
            return json_data(0,$this->codeMessage[0],'');
        }
    }

    /**
     * 修改密码ajax
     * @return array
     */
    public function chpwd(){
        $data = $this->request->param();
        $userid = UID;
        if($data['newpwd']!=$data['renewpwd']){
            return json_data(130,'两次输入密码不一致！','');
        }
        if( $user = UserModel::get($userid) ){
            if(password_verify($data['pwd'],$user->password)){
                $user->password = password_hash($data['newpwd'],PASSWORD_DEFAULT);
                $user->save();

                return json_data(0,$this->codeMessage[0],'');
            }
            else{
                return json_data(130,'原密码输入错误！','');
            }
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }


}
