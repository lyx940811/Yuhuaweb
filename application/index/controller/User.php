<?php
namespace app\index\controller;

use think\Loader;
use think\Config;
use app\index\model\UserProfile as UserProfileModel;
use think\Db;
use think\Validate;
/**
 * Class User
 * @package app\index\controller
 * 用户资料（新建/修改）模块，对应网站首页-个人设置
 */
class User extends Home
{
    protected $LogicLogin;

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

}
