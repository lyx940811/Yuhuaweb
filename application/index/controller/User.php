<?php
namespace app\index\controller;

use app\index\model\UserProfile;
use think\Loader;
use think\Config;
use app\index\model\UserProfile as UserProfileModel;
use app\index\model\User as UserModel;
use app\index\model\Friend as FriendModel;
use think\Db;
use think\Validate;
/**
 * Class User
 * @package app\index\controller
 * 用户资料（新建/修改）模块，对应网站首页-个人设置（或教师、学生某些重叠的功能）
 */
class User extends Home
{
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * 新增/修改资料
     */
    public function chprofile(){
        $result = $this->LogicUser->chUserProfile($this->data);
        return $result;
    }

    /**
     * 上传图片
     */
    public function uploadimg(){
        $file = $_FILES;
        $res = uploadPic($file);
        if($res['code']!=0){
            return json_data($res['code'],$this->codeMessage[$res['code']],$res['path']);
        }
        return json_data($res['code'],$this->codeMessage[$res['code']],$res['path']);
    }

    /**
     * 修改头像
     */
    public function chheadicon(){
        $file = $_FILES;
        $res  = $this->LogicUser->upUserHeadImg($file,$this->data);
        return $res;
    }
    /**
     * 实名认证
     */
    public function attestation(){
        $file = $_FILES;
        $data = $this->data;

        $res = $this->LogicUser->userAttestation($file,$data);
        return $res;
    }

    /**
     * 修改密码
     */
    public function chpwd(){
        $data = $this->data;
        $userid = $data['userid'];
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


    /**
     * 修改用户名
     */
    public function chusername(){
        $result = $this->LogicUser->chUsername($this->data);
        return $result;
    }

    /**
     * 获得个人设置内信息（type传入不同的选项来获得下方不同的内容）
     */
    public function getuserinfo(){
        $data = $this->data;
        if(!UserModel::get($data['userid'])){
            return json_data(110,$this->codeMessage[110],'');
        }
        switch ($data['type']){
            case 'base':
                $user_profile = $this->LogicUser->getUserInfo($data['userid']);
                return json_data(0,$this->codeMessage[0],$user_profile);
                break;
            case 'avatar':
                $user_profile = $this->LogicUser->getUserAvatar($data['userid']);
                return json_data(0,$this->codeMessage[0],$user_profile);
                break;
            case 'attestation':
                $user_profile = $this->LogicUser->getUserAttestation($data['userid']);
                return json_data(0,$this->codeMessage[0],$user_profile);
                break;
            default:
                return json_data(1000,$this->codeMessage[1000],'');
                break;
        }
    }


    /**
     * 获得个人主页内容（type传入不同的选项来获得下方不同的内容）
     */
    public function getuserpage(){
        $data['type'] = 'following';
        $data['userid'] = 3;//登陆的uid
        $data['touserid'] = 2;//查看用户的uid

        $base_profile   =   $this->LogicUser->getBaseUserinfo($data);
        $contain        =   $this->LogicUser->getUserTypeInfo($data);
        $is_follow      =   $this->LogicUser->isFollow($data);

        $user_profile = [
            'base_profile'  =>  $base_profile,
            'is_follow'     =>  $is_follow,
            'contain'       =>  $contain
        ];

        var_dump($user_profile);
//        return $user_profile;

    }

    /**
     * 关注某个用户
     */
    public function followuser(){
        $data['userid'] = 2;//登陆的uid
        $data['touserid'] = 3;//查看用户的uid

        $res = $this->LogicUser->followUser($data);

        return $res;
    }
    /**
     * 取消关注
     */
    public function disfollowuser(){
        $data['userid'] = 2;//登陆的uid
        $data['touserid'] = 3;//查看用户的uid

        $res = $this->LogicUser->disFollowUser($data);

        return $res;
    }





}
