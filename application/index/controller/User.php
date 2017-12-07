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
     * 获得个人主页内容（type传入不同的选项来获得不同的内容）
     */
    public function getuserpage(){
        $data['type'] = 'following';
        $data['userid'] = 3;
        $contain = '';
        switch ($data['type']){

            case 'userpage':
                //取得个人介绍
                $contain = UserProfile::where( [ 'userid' => $data[ 'userid' ] ] )->value('about');
                break;
            case 'following':
                //取得关注列表
                $contain = FriendModel::all( [ 'fromId' => $data['userid'] = 3 ] );
                $star = [];
                foreach ($contain as $item){
                    $star[] = [
                        'userid'    =>  $item->star->id,
                        'nickname'  =>  $item->star->nickname,
                        'about'     =>  $item->starProfile->about,
                    ];
                }
                $contain = $star;
                break;
            case 'follower':
                //取得粉丝列表
                $contain = FriendModel::all( [ 'toId' => $data['userid'] = 3 ] );
                $star = [];
                foreach ($contain as $item){
                    $star[] = [
                        'userid'    =>  $item->fan->id,
                        'nickname'  =>  $item->fan->nickname,
                        'about'     =>  $item->fanProfile->about,
                    ];
                }
                $contain = $star;
                break;


        }

        $base_profile = $this->getBaseUserinfo($data);
        $user_profile = [
            'base_profile'  =>  $base_profile,
            'contain'       =>  $contain
        ];
        var_dump($user_profile);


    }

    /**
     * 获得个人介绍
     */
    public function getBaseUserinfo($data){
        $data['userid'] = 3;
        $user = UserProfile::get( [ 'userid' => $data[ 'userid' ] ] );

        $follower   = Db::name('friend')->where('toId', $data['userid'] )->count();
        $following      = Db::name('friend')->where('fromId', $data['userid'] )->count();

        $base_profile = [
            'username'  =>  $user->user->nickname,
            'signature' =>  $user['signature'],
            'following' =>  $following,
            'follower'  =>  $follower,
        ];

        return $base_profile;

    }



}
