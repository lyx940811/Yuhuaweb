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
class Course extends Home
{
    protected $LogicCourse;
    protected $LogicUpload;
    protected $LogicReview;
    public function __construct()
    {
        parent::__construct();
        $this->LogicCourse = Loader::controller('Course','logic');
        $this->LogicUpload = Loader::controller('Upload','logic');
        $this->LogicReview = Loader::controller('Review','logic');
    }

    /**
     * 关于有关教师部分的全部迁移到了教师文件中
     */
//    /**
//     * 添加课程（教师）
//     */
//    public function createcourse(){
//        $title  = $this->data['title'];
//        $userid = $this->data['userid'];
//        $res    = $this->LogicCourse->createCourse($title,$userid);
//        return $res;
//    }
//
//    /**
//     * 教师在我的教学-课程中【获得】课程信息
//     * 现有type对应：
//     * base（基本信息）
//     * detail（详细信息）
//     * cover（封面图片）
//     * files（课程文件）
//     * testpaper（试卷管理）
//     * question（题目管理）
//     * 计划任务//course_task
//     * 计划设置//旧模板course_v8，新的没有对应表
//     * 营销设置
//     * teachers(教师设置)
//     *
//     * 学员管理
//     * 试卷批阅
//     * 作业批阅
//     * 学习数据
//     * 订单查询
//     * 教学计划管理
//     */
//    public function getcourse(){
//        $data['courseid'] = 5;
//        $data['type'] = 'question';
//        $res = $this->LogicCourse->getCourseInfo($data);
//        return $res;
//    }
//
//
//
//    /**
//     * 教师在我的教学-课程中【设置、更新】课程信息
//     * 现有type对应：
//     * base（基本信息）
//     * detail（详细信息）
//     * （封面图片）
//     * （课程文件）
//     * （试卷管理）
//     * （题目管理）
//     * 计划任务//course_task
//     * 计划设置//旧模板course_v8，新的没有对应表
//     * 营销设置
//     * （教师设置)
//     *
//     * 学员管理
//     * 试卷批阅
//     * 作业批阅
//     * 学习数据
//     * 订单查询
//     * 教学计划管理
//     */
//    public function setcourse(){
//        $type = 'cover';//$this->data['type'];
//        $courseid = 5;//$this->data['courseid'];
//        $data = [
//            'title'=>'123update test',
//            'subtitle'=>'vice title',
//            'tags'=>'test|tags',
//            'categoryId'=>1,
//            'status'=>3,
//        ];//$this->data;
//        switch ($type){
//            case 'base':
//                //基本信息
//                $key = ['title'=>'','subtitle'=>'','tags'=>'','categoryId'=>'','status'=>''];
//                $data = array_intersect_key($data,$key);
//                return $this->LogicCourse->updateCourseInfo($courseid,$data);
//                break;
//            case 'detail':
//                //详细信息
//                $key = ['about'=>'','goals'=>'','audiences'=>''];
//                $data = array_intersect_key($data,$key);
//                return $this->LogicCourse->updateCourseInfo($courseid,$data);
//                break;
//            case 'cover':
//                //上传图片
//                $file = $_FILES;
//                $res = $this->LogicCourse->uploadFile($file);
//                var_dump($res);
//                if(!empty($res)){
//                    //update
//
//                }
//                break;
//        }
//
//    }

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
     * 教师页面上传课程文件               迁移到了teacher中
     * 记得修改php.ini中的上传选项
     * 还没有上传type的限制，还没有大小的限制
     */
//    public function uploadfile(){
//        try{
//            $courseid = $this->data['courseid'];
//            $files = $_FILES;
//            $res = $this->LogicUpload->uploadFile($files);
//
//            foreach ($res as &$r){
//                $r['courseid']   = $courseid;
//                $r['createTime'] = date('Y-m-d H:i:s',time());
//                $name_type = explode('.',$r['filename']);
//                //确定文件类型
//                $type = null;
//                if($name_type[1]){
//                    $type = Db::name('course_file_type')
//                        ->where('ietype|firefoxtype',$r['type'])
//                        ->where('simpletype',$name_type[1])
//                        ->value('simpletype');
//                }
//                !empty($type)?$r['type'] = $type:$r['type'] = 'others';
//            }
//
//            $coursefile = new CourseFile();
//            $coursefile->saveAll($res);
//            return json_data(0,$this->codeMessage[0],'');
//        }
//        catch( Exception $e){
//            return json_data($e->getCode(),$e->getMessage(),'');
//        }
//    }

    /**
     * 获得某课程下的所有课程文件
     */
    public function getfilelist(){
        $courseid = 3;//$this->data['courseid'];
        $fileList = $this->LogicCourse->getCourseFile($courseid);
        //类型转换为中文?现在是英文
        var_dump($fileList);
    }


    /**
     * 获得某课程下的所有一级评论
     */
    public function getcoursecomments(){
        $courseid = 5;
        if(!\app\index\model\Course::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $coursecomment = $this->LogicReview->getcoursecomment($courseid);

        return json_data(0,$this->codeMessage[0],$coursecomment);
    }

    /**
     * 获得某个评论的详细内容及这个评论的一级，二级评论
     * @return array
     */
    public function getcomdetail(){
        $commentid = 1;
        if(!\app\index\model\CourseReview::get($commentid)){
            return json_data(200,$this->codeMessage[200],'');
        }

        $coursecomment = $this->LogicReview->getcommentdetail($commentid);
        return json_data(0,$this->codeMessage[0],$coursecomment);
    }





}
