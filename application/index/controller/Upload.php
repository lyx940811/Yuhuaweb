<?php
namespace app\index\controller;

use think\Exception;
use think\Loader;
use think\Config;
use app\index\model\Course as CourseModel;
use app\index\model\User as UserModel;
use app\index\model\CourseFile;
use think\Db;
use think\Validate;


/**
 * Class Course 在教师角色下的我的教学-在教课程-课程管理中的一些功能
 * @package app\index\controller
 */
class Upload extends Home
{
    protected $LogicCourse;
    protected $LogicUpload;
    public function __construct()
    {
        parent::__construct();
        $this->LogicCourse = Loader::controller('Course','logic');
        $this->LogicUpload = Loader::controller('Upload','logic');
    }

    /**
     * 上传图片
     */
    public function uploadimg(){
        $file = $_FILES;
        $res = uploadPic($file);
        if($res['code']!=0){
            return json_data($res['code'],$this->codeMessage[$res['code']],'');
        }
        return json_data($res['code'],$this->codeMessage[$res['code']],$res['path']);
    }
}
