<?php

namespace app\index\logic;

use app\index\model\Course;

use think\Loader;
use think\Config;
use think\Validate;
class CourseLogic
{
    protected $codeMessage;
    public function __construct()
    {
        $this->codeMessage = Config::get('apicode_message');
    }




}
?>
