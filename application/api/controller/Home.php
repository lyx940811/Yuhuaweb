<?php
namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Config;
class Home extends Controller
{
    public $access_token = ACCESS_TOKEN;
    public $request;
    public function __construct()
    {
        $this->request = Request::instance();
    }
}
