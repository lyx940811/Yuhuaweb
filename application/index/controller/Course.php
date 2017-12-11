<?php
namespace app\index\controller;

use think\Loader;
use think\Config;
use app\index\model\Course as CourseModel;
use app\index\model\User as UserModel;
use think\Db;
use think\Validate;

/**
 * Class Course 在教师角色下的我的教学-在教课程-课程管理中的一些功能
 * @package app\index\controller
 */
class Course extends Home
{


    public function __construct()
    {
        parent::__construct();
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
     * 教师在我的教学-课程中获得课程信息
     * 现有type对应：
     * base（基本信息）
     * detail（详细信息）
     * cover（封面图片）
     * 课程文件
     * 试卷管理
     * 题目管理
     * 计划任务
     * 计划设置
     * 营销设置
     * teachers(教师设置)
     * 学员管理
     * 试卷批阅
     * 作业批阅
     * 学习数据
     * 订单查询
     * 教学计划管理
     */
    public function getcourse(){
        $data['courseid'] = 5;
        $data['type'] = 'detail';
        $res = $this->LogicCourse->getCourseInfo($data);
        return $res;
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


}
