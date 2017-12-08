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
     * 修改头像
     */
    public function chheadicon(){
        $file = $_FILES;//request()->file('head_icon');
//        $type = array(".gif",".jpg",".png",".bmp");
//        $fileType = strrchr($_FILES['img']['name'],".");
        var_dump($file);
        var_dump(preg_match('/^image\//i', $file['head_icon']['type']));
//        if (!in_array($fileType,$type)){
//            echo "不是允许的图片类型";
//        }
//
//        var_dump($file);
    }

    /**
     * 修改用户名
     */
    public function chusername(){
        $result = $this->LogicUser->chUsername($this->data);
        return $result;
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
