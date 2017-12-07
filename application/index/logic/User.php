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
     * 新增/修改资料
     */
    public function chUserProfile($data){
        if($user_profile = UserProfileModel::get(['userid' => $data['userid']])){
            //verify data
            $validate = Loader::validate('UserProfile');

            if(!$validate->check($data)){
                return json_data(130,$validate->getError(),'');
            }
            else{
                //update data
                $user_profile->data($data)
                    ->isUpdate(true)
                    ->save(['userid' => $data['userid']]);

                return json_data(0,$this->codeMessage[0],'');
            }

        }
        else{
            $data['createdTime'] = date('Y-m-d H:i:s',time());
            //verify data
            $validate = new Validate([
                'userid'                  => 'require',
                'mobile|电话'             => 'require|length:1,20',
                'idcard|身份证号码'       => 'require|length:1,20',
                'city|城市'               => 'require',
                'createdTime|创建时间'    => 'require',
            ]);

            if(!$validate->check($data)){
                return json_data(130,$validate->getError(),'');
            }
            else{
                //add data
                $new_profile = new UserProfileModel;
                $new_profile->data($data)->isUpdate(false)->save();
                return json_data(0,$this->codeMessage[0],'');
            }

        }
    }

    /**
     * 修改用户名
     */
    public function chUsername($data){
        if($user = UserModel::get([ 'id' => $data['userid'] ])){
            $user->nickname = $data['nickname'];
            $user->save();
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }



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
