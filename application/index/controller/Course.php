<?php
namespace app\index\controller;

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
class Course extends Home
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
     * 添加课程（教师）
     */
    public function createcourse(){
        $title  = $this->data['title'];
        $userid = $this->data['userid'];
        $res    = $this->LogicCourse->createCourse($title,$userid);
        return $res;
    }

    /**
     * 教师在我的教学-课程中【获得】课程信息
     * 现有type对应：
     * base（基本信息）
     * detail（详细信息）
     * cover（封面图片）
     * files（课程文件）
     * testpaper（试卷管理）
     * question（题目管理）
     * 计划任务//course_task
     * 计划设置//旧模板course_v8，新的没有对应表
     * 营销设置
     * teachers(教师设置)
     *
     * 学员管理
     * 试卷批阅
     * 作业批阅
     * 学习数据
     * 订单查询
     * 教学计划管理
     */
    public function getcourse(){
        $data['courseid'] = 5;
        $data['type'] = 'question';
        $res = $this->LogicCourse->getCourseInfo($data);
        return $res;
    }



    /**
     * 教师在我的教学-课程中【设置、更新】课程信息
     * 现有type对应：
     * base（基本信息）
     * detail（详细信息）
     * （封面图片）
     * （课程文件）
     * （试卷管理）
     * （题目管理）
     * 计划任务//course_task
     * 计划设置//旧模板course_v8，新的没有对应表
     * 营销设置
     * （教师设置)
     *
     * 学员管理
     * 试卷批阅
     * 作业批阅
     * 学习数据
     * 订单查询
     * 教学计划管理
     */
    public function setcourse(){
        $type = 'cover';//$this->data['type'];
        $courseid = 5;//$this->data['courseid'];
        $data = [
            'title'=>'123update test',
            'subtitle'=>'vice title',
            'tags'=>'test|tags',
            'categoryId'=>1,
            'status'=>3,
        ];//$this->data;
        switch ($type){
            case 'base':
                //基本信息
                $key = ['title'=>'','subtitle'=>'','tags'=>'','categoryId'=>'','status'=>''];
                $data = array_intersect_key($data,$key);
                return $this->LogicCourse->updateCourseInfo($courseid,$data);
                break;
            case 'detail':
                //详细信息
                $key = ['about'=>'','goals'=>'','audiences'=>''];
                $data = array_intersect_key($data,$key);
                return $this->LogicCourse->updateCourseInfo($courseid,$data);
                break;
            case 'cover':
                //上传图片
                $file = $_FILES;
                $res = $this->LogicCourse->uploadFile($file);
                var_dump($res);
                if(!empty($res)){
                    //update

                }
                break;
        }

    }

    /**
     * 图片缩放测试
     */
    public function chpicsize(){
        $path = 'G:\wamp64\www\tp5yuhuaweb\public\uploads\2017\12\12\flowers-background-butterflies-beautiful-87452.jpeg';
        myImageResize(iconv("utf-8","gb2312",$path),400,400);
    }

    /**
     * 压缩测试
     */
    public function press(){
        $path = 'G:\wamp64\www\tp5yuhuaweb\public\uploads\2017\12\12\flowers-background-butterflies-beautiful-87452.jpeg';
        $path = 'G:\wamp64\www\tp5yuhuaweb\public\uploads\2017\12\12\3.jpg';
        compresspic($path);
    }

    /**
     * 改变课程发布的状态
     * @return mixed
     */
    public function chcoursestatu(){
        $courseid = $this->data['courseid'];
        $status   = $this->data['status'];

        return $this->LogicCourse->chCourseStatus($courseid,$status);
    }

    /**
     * 教师页面上传课程文件
     */
    public function uploadfile(){
//        $courseid = $this->data['courseid'];
//        $lessonid = $this->data['lessonid'];
        $files = $_FILES;
        $res = $this->LogicUpload->uploadFile($files);
        var_dump($res);
//        $coursefile = new CourseFile();
//        $save = $coursefile->saveAll($res);
//        var_dump($save);
    }



}
