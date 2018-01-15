<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Db;
use app\index\model\User;
class Home extends Controller
{
    protected $codeMessage;
    public $user;
    public function __construct()
    {
        parent::__construct();
        $this->codeMessage = Config::get('apicode_message');

        if(session('userid')){
            define('UID',session('userid'));
            $this->user = User::get(UID);
            $this->assign('user',$this->user);
        }

    }

    public function oo()
    {
        $data['mediaSource'] = 'http://111.202.98.158/variety.tc.qq.com/AqjZKdRh6zi5ZqnhGI7pJu4WfWYktDQ75Nx7TgeJjT2A/s02003or44p.p201.1.mp4?vkey=10399D12C2CE096521FE31E264A3E95ECB1B993AD19EFBA3C6E8276F823312D43C1F3904A7BEA4E6DF4FF6BCF2174E458B0E4DCABFFFD55262F0AD13607ADC9F1019577532A466EF1591B48F3EC6EC0526DA2821EC5B70424C36E28D942582935374A3B82A9A5060DAFA326D0724D7CC177E477FC0860C50&platform=10201&sdtfrom=&fmt=shd&level=0';
        Db::name('course_task')->where('id',7)->update($data);
    }


}
