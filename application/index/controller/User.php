<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\UserProfile;
use think\Loader;
use think\Config;
use app\index\model\UserProfile as UserProfileModel;
use app\index\model\User as UserModel;
use app\index\model\Friend as FriendModel;
use app\index\model\Asklist;
use think\Db;
use think\Exception;
use think\Validate;
/**
 * Class User
 * @package app\index\controller
 * 用户资料（新建/修改）模块，对应网站首页-个人设置（或教师、学生某些重叠的功能）
 */
class User extends Controller
{
    protected $LogicUser;
    protected $LogicLogin;
    protected $user;
    protected $usertoken;
    protected $data;
    protected $codeMessage;
    protected $LogicLog;
    protected $LogicReview;
    public function __construct()
    {
        parent::__construct();

        //define ajax return message
        $this->codeMessage = Config::get('apicode_message');

        //controller from dir logic
        $this->LogicLog      = Loader::controller('Log','logic');
        $this->LogicUser     = Loader::controller('User','logic');
        $this->LogicLogin    = Loader::controller('Login','logic');
        $this->LogicReview   = Loader::controller('Review','logic');
        //unset the token which in the post data
        if($this->request->param()){
            $this->data = $this->request->param();
        }

        $user_token = $this->request->param('user_token');
//        $this->verifyUserToken($user_token);
    }

    protected function verifyUserToken($user_token){
        if(!$user_token){
            //没有token或token为空
            exit(json_encode(json_data(910,$this->codeMessage[910],'')));
        }

        if($user = UserModel::get(['user_token'=>$user_token])){
            //判断过期没
            if(time()>$user['expiretime']){
                //token过期
                exit(json_encode(json_data(910,$this->codeMessage[910],'')));
            }
            unset($this->data['user_token']);
            $this->user = $user;
        }
        else{
            //没有在数据库内找到对应token
            exit(json_encode(json_data(910,$this->codeMessage[910],'')));
        }
    }

    /**
     * 新增/修改资料
     */
    public function chprofile(){
        $this->data['userid'] = $this->user->id;
        $result = $this->LogicUser->chUserProfile($this->data);
        return $result;
    }

    /**
     * 修改头像
     */
    public function chheadicon(){
        $file = $_FILES;
        $this->data['userid'] = $this->user->id;
        $res  = $this->LogicUser->upUserHeadImg($file,$this->data);
        return $res;
    }
    /**
     * 实名认证
     */
    public function attestation(){
        $file = $_FILES;
        $data = $this->data;
        $data['userid'] = $this->user->id;
        if(!UserModel::get($data['userid'])){
            return json_data(110,$this->codeMessage[110],'');
        }
        $res = $this->LogicUser->userAttestation($file,$data);
        return $res;
    }

    /**
     * 修改密码
     */
    public function chpwd(){
        $data = $this->data;
        $userid = $this->user->id;
        if($data['newpwd']!=$data['renewpwd']){
            return json_data(130,'两次输入密码不一致！','');
        }
        if( $user = UserModel::get($userid) ){
            if(password_verify($data['pwd'],$user->password)){
                $user->password = password_hash($data['newpwd'],PASSWORD_DEFAULT);
                $user->save();
                //add log
                $this->LogicLog->createLog($userid,2,'update','更新密码',serialize($data),0);
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
/*    public function chusername(){
        $result = $this->LogicUser->chUsername($this->data);
        return $result;
    }*/

    /**
     * 获得个人设置内信息（type传入不同的选项来获得下方不同的内容）
     */
    public function getuserinfo(){
        $data = $this->data;
        $userid = $this->user->id;
        if(!UserModel::get($userid)){
            return json_data(110,$this->codeMessage[110],'');
        }
        switch ($data['type']){
            case 'base':
                $user_profile = $this->LogicUser->getUserInfo($userid);
                return json_data(0,$this->codeMessage[0],$user_profile);
                break;
            case 'avatar':
                $user_profile = $this->LogicUser->getUserAvatar($userid);
                return json_data(0,$this->codeMessage[0],$user_profile);
                break;
            case 'attestation':
                $user_profile = $this->LogicUser->getUserAttestation($userid);
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

    public function coursereview(){

        $res = $this->LogicReview->review(1);
        return $res;
    }









}
