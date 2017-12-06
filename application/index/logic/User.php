<?php

namespace app\index\logic;

use app\index\model\UserProfile as UserProfileModel;
use think\Loader;
use think\Validate;
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




}
?>
