<?php

namespace app\api\logic;

use app\api\model\User;
use think\Loader;
class Login
{
    public function userAdd($data){
        $data['password']       =   md5($data['password']);
        $data['title']          =   123;
        $data['createdTime']    =   date('Y-m-d H:i:s');
        //verified data
        $validate = Loader::validate('User');
        if(!$validate->check($data)){
            return $validate->getError();
        }
        else{
            //add data
            $result = User::create($data);
            if($result){
                return json_data(0,'');
            }
            else{
                return json_data(100,'');
            }
        }
    }

    public function userLogin($data){
        $user = User::get(['email'=>$data['email']]);
        if($user){
            if(strcmp($user['password'],md5($data['password']))){
                return json_data(0,$user);
            }
        }
        else{
            return json_data(110,'');
        }
    }



}
?>
