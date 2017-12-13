<?php

namespace app\index\logic;
use think\Loader;
use think\Config;
use think\Validate;
class Base
{
    protected $codeMessage;
    protected $redis;
    public function __construct()
    {
        $this->codeMessage = Config::get('apicode_message');
        $this->setRedis();
    }

    public function setRedis(){
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

}
?>
