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



class Course extends Home
{

    public $course;
    public function __construct()
    {
        parent::__construct();
        if(!$this->request->param('course')){
            $this->redirect('index/index/allcourse');
        }
        $courseid = $this->request->param('course');
        $this->course = \app\index\model\Course::get($courseid);
        $this->assign('course',$this->course);
    }

    public function catalogue(){
        return $this->fetch();
    }
    public function discussion(){
        return $this->fetch();
    }
    public function evaluate(){
        return $this->fetch();
    }
    public function note(){
        return $this->fetch();
    }
    public function material(){
        return $this->fetch();
    }
    public function summary(){
        return $this->fetch();
    }

}
