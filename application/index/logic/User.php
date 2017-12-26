<?php

namespace app\index\logic;

use app\index\model\UserProfile as UserProfileModel;
use app\index\model\User as UserModel;
use app\index\model\Friend as FriendModel;
use think\Loader;
use think\Validate;
use think\Db;
class User extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获得用户token
     * @return array
     */
    public function getusertoken($data){
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

            if($user['locked']==1){
                return json_data(160,$this->codeMessage[160],'');
            }

            if(password_verify($data['password'],$user['password'])){
                //需要对返回数据进行整理，这里需要改成只返回access_token
                $user_token = [
                    'user_token'    =>  md5($user->id.time().uniqid()),
                    'expiretime'    =>  time()+7200,
                ];
                $user->save($user_token);
                $user_token['expire'] = 7200;
                return json_data(0,$this->codeMessage[0],$user_token);
            }
            else{

                return json_data(140,$this->codeMessage[140],'');
            }
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }

    /**
     * 新增/修改资料
     */
    public function chUserProfile($data){
        //verify data
        $key = ['username','nickname'=>''];
        $profile_data = array_diff_key($data,$key);

        $validate = Loader::validate('UserProfile');
        if(!$validate->check($profile_data)){
            return json_data(130,$validate->getError(),'');
        }

        if($user_profile = UserProfileModel::get(['userid' => $data['userid']])){
            //update data
            $user_profile->data($profile_data)
                ->isUpdate(true)
                ->save(['userid' => $data['userid']]);
            //update user data
            $user = UserModel::get($data['userid']);
            $user->nickname=$data['nickname'];
            $user->save();
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            $data['createdTime'] = date('Y-m-d H:i:s',time());
            //add data
            $new_profile = new UserProfileModel;
            $new_profile->data($profile_data)->isUpdate(false)->save();
            return json_data(0,$this->codeMessage[0],'');
        }
    }

    /**
     * 修改用户名
     */
    /*public function chUsername($data){
        if($user = UserModel::get([ 'id' => $data['userid'] ])){
            $user->nickname = $data['nickname'];
            $user->save();
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }*/

    /**
     * 根据type不同来获得不同的内容
     */
    public function getUserTypeInfo($data){
        $contain = '';
        switch ($data['type']){

            case 'userpage':
                //取得个人介绍
                $contain = UserProfileModel::where( [ 'userid' => $data[ 'userid' ] ] )->value('about');
                break;
            case 'following':
                //取得关注列表
                $contain = FriendModel::all( [ 'fromId' => $data['userid'] ] );
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
                $contain = FriendModel::all( [ 'toId' => $data['userid'] ] );
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
        return $contain;
    }

    /**
     * 获得基本个人介绍（个人主页上部分）
     */
    public function getBaseUserinfo($data){
        $user = UserProfileModel::get( [ 'userid' => $data[ 'userid' ] ] );

        $follower   = Db::name('friend')->where('toId'  , $data['userid'] )->count();
        $following  = Db::name('friend')->where('fromId', $data['userid'] )->count();

        $base_profile = [
            'username'  =>  $user->user->nickname,
            'signature' =>  $user['signature'],
            'following' =>  $following,
            'follower'  =>  $follower,
        ];

        return $base_profile;

    }

    /**
     * 上传并更新用户头像
     * @param $file 图片信息
     * @param $data 从里面获取用户id
     * @return string
     */
    public function upUserHeadImg($file,$userid){
//        $userid = $data['userid'];
        $res = uploadPic($file,$userid);
        if($res['code']!=0){
            return json_data($res['code'],$this->codeMessage[$res['code']],$res['path']);
        }

        if( $user = UserModel::get($userid) ){
            $user->title = $res['path']['head_icon'];
            $user->save();
            return json_data(0,$this->codeMessage[0],$res['path']);
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }

    /**
     * 实名认证
     * @param $file 身份证文件
     * @param $data 个人信息
     * @return string
     */
    public function userAttestation($file,$data){
        $userid = $data['userid'];
        $res  = uploadPic($file);
        if($res['code']!=0){
            return json_data($res['code'],$this->codeMessage[$res['code']],'');
        }
        $data['cardpic'] = serialize($res['path']);

        $validate = new Validate([
            'realname' => 'require',
            'idcard'   => 'require|length:1,18',
            'cardpic'  => 'require'
        ]);
        if(!$validate->check($data)){
            return json_data(130,$validate->getError(),'');
        }

        //如果已经生成user_profile的话，就更新，没有的话则生成对应的userprofile
        if($user = UserProfileModel::get([ 'userid' => $userid ])){
            $user->realname =   $data['realname'];
            $user->idcard   =   $data['idcard'];
            $user->cardpic  =   $data['cardpic'];
            $user->save();
            return json_data(0,$this->codeMessage[0],$res['path']);
        }
        else{
            $new_profile = new UserProfileModel;
            $new_profile->data($data)->isUpdate(false)->save();
            return json_data(0,$this->codeMessage[0],'');
        }
    }

    /**
     * 得到用户基本信息
     */
    public function getUserInfo($userid){
        $key = [
            'sex'=>'',
            'mobile'=>'',
            'company'=>'',
            'job'=>'',
            'signature'=>'',
            'about'=>'',
            'site'=>'',
            'weibo'=>'',
            'weixin'=>'',
            'qq'=>'',
            'isQQPublic'=>'',
            'isWeixinPublic'=>'',
            'isWeiboPublic'=>'',
            ];
        $user_profile = UserProfileModel::get([ 'userid' => $userid])->toArray();
        $user_profile = array_intersect_key($user_profile,$key);

        $user = UserModel::get($userid);
        $user_profile['username'] = $user['username'];
        $user_profile['nickname'] = $user['nickname'];

        return $user_profile;
    }

    /**
     * 得到用户头像
     * @param $userid
     * @return array
     */
    public function getUserAvatar($userid){
        $key = [
            'title'=>'',
        ];
        $user_profile = UserModel::get($userid)->toArray();
        $user_profile = array_intersect_key($user_profile,$key);
        return $user_profile;
    }

    /**
     * 得到用户实名认证的信息
     * @param $userid
     * @return array
     */
    public function getUserAttestation($userid){
        $key = [
            'realname'=>'',
            'idcard'=>'',
            'cardpic'=>'',
        ];
        $user_profile = UserProfileModel::get([ 'userid' => $userid])->toArray();
        $user_profile = array_intersect_key($user_profile,$key);
        $cardpic = unserialize($user_profile['cardpic']);
        $user_profile['front_pic'] = $cardpic['front_pic'];
        $user_profile['behind_pic'] = $cardpic['behind_pic'];
        unset($user_profile['cardpic']);
        return $user_profile;
    }


    /**
     * 判断登陆的用户与访问页面的用户是否关注
     */
    public function isFollow($data){
        //是否关注
        if(FriendModel::get( [ 'fromId' => $data[ 'userid' ] , 'toId' => $data['touserid'] ] )){
            $data['is_follow'] = 1;
        }
        elseif ($data['userid'] == $data['touserid']){
            $data['is_follow'] = 0;
        }
        else{
            $data['is_follow'] = -1;
        }
        return $data['is_follow'];
    }

    /**
     * 关注某个用户
     */
    public function followUser($data){
        if(UserModel::get([ 'id ' => $data[ 'userid' ] ]) && UserModel::get([ 'id ' => $data[ 'touserid' ] ])){
            $follow = [
                'fromId'        =>  $data['userid'],
                'toId'          =>  $data['touserid'],
                'createdTime'   =>  time()
            ];
            FriendModel::create($follow);
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }


    /**
     * 取消关注某个用户
     */
    public function disfollowUser($data){
        if(UserModel::get([ 'id ' => $data[ 'userid' ] ]) && UserModel::get([ 'id ' => $data[ 'touserid' ] ])){

            FriendModel::destroy([ 'fromId ' => $data['userid'] ,'toId' => $data['touserid'], ]);
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }








}
?>
