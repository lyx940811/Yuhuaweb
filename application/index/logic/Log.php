<?php

namespace app\index\logic;

use app\index\model\Log as LogModel;
use think\Exception;

class Log
{
    public function __construct()
    {

    }

    public function createLog($userid,$module,$action,$message,$data,$level){
        $data = [
            'userid'    =>  $userid,
            'data'      =>  $data,
            'action'    =>  $action,
            'message'   =>  $message,
            'level'     =>  $level,
            'ip'        =>  $_SERVER['REMOTE_ADDR'],
            'module'    =>  $module,
            'createdTime'    =>  date('Y-m-d H:i:s',time()),
        ];
        LogModel::create($data);
    }

    public function exception(){
        throw new Exception('LogException',700);
    }





}
?>
