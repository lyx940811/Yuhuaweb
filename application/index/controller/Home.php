<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Config;
class Home extends Controller
{
    protected $access_token = ACCESS_TOKEN;
    protected $request;
    protected $codeMessage;
    public function __construct()
    {
        $this->request = Request::instance();
        //ajax return message
        $this->codeMessage = Config::get('apicode_message');
    }
}
