<?php
namespace app\api\controller;

use Flc\Alidayu\Client;
use Flc\Alidayu\App;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use Flc\Alidayu\Requests\IRequest;

/**
 * Class Course 在教师角色下的我的教学-在教课程-课程管理中的一些功能
 * @package app\index\controller
 */
class Plugin extends Home
{
    public function sendtext()
    {
        // 配置信息
        $config = [
            'app_key'    => '23953147',
            'app_secret' => '3904c9cae716283181c0465bb0df45c4',
            // 'sandbox'    => true,  // 是否为沙箱环境，默认false
        ];

        // 使用方法一
        $client = new Client(new App($config));
        $req    = new AlibabaAliqinFcSmsNumSend;

        $req->setRecNum('17600738252')
            ->setSmsParam([
                'num' => rand(100000, 999999)
            ])
            ->setSmsFreeSignName("觅食森林")
            ->setSmsTemplateCode('SMS_70250225');

        $resp = $client->execute($req);
        var_dump($resp);
    }
}
