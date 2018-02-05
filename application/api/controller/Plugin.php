<?php
namespace app\api\controller;

use Flc\Alidayu\Client;
use Flc\Alidayu\App;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use Flc\Alidayu\Requests\IRequest;
use think\Config;

/**
 * Class Course 在教师角色下的我的教学-在教课程-课程管理中的一些功能
 * @package app\index\controller
 */
class Plugin extends Home
{
    public function sendtext($phone)
    {
        // 配置信息
        $config = Config::get('alidayu');

        // 使用方法一
        $client = new Client(new App($config));
        $req    = new AlibabaAliqinFcSmsNumSend;

        $code = rand(100000, 999999);

        $req->setRecNum($phone)
            ->setSmsParam([
                'num' => $code,
            ])
            ->setSmsFreeSignName("觅食森林")
            ->setSmsTemplateCode('SMS_70250225');

        $resp = $client->execute($req);
        $resp = (array)$resp;
        if(isset($resp['result'])){
            $resp['result'] = (array)$resp['result'];
            if($resp['result']['err_code']==0){
                //set redis
                $redis = new \Redis();
                $redis->connect('127.0.0.1', 6379);
                $redis->setex($phone, 900, $code);

                return $data = [
                    'code'  =>  0,
                    'message'   =>  'success',
                ];
            }
        }else{
            //send error,     'sub_msg' is detail error message
            return $data = [
                'code'      =>  999,
                'message'   =>  $resp['sub_msg'],
            ];
        }
    }
}
