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

    }



}
?>
