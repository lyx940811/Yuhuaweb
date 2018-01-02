<?php
namespace app\api\controller;

use think\Loader;
use think\Db;
use app\index\model\User;
use app\index\model\Like;
use app\index\model\Course as CourseModel;
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
        $courseid = $this->data['courseid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        if(!\app\index\model\Course::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $comment = Db::name('course_review')
            ->where('courseid',$courseid)
            ->where('parentid',0)
            ->field('id,userid,content,createdTime')
            ->page($page,10)
            ->select();
        if($comment){
            foreach ($comment as &$c){
                $user = User::get($c['userid']);
                $c['username'] = $user->username;
                $c['avatar']   = $this->request->domain().DS.$user->title;
                $c['sonreviewNum']   = Db::name('course_review')->where('parentid',$c['id'])->count();
                $c['likeNum']   = Db::name('like')->where('type','comments')->where('articleid',$c['id'])->count();
                if(!empty($this->user)){
                    if(Like::get(['userid'=>$this->user->id,'type'=>'comments','articleid'=>$c['id']])){
                        $c['is_like'] = 1;
                    }
                    else{
                        $c['is_like'] = 0;
                    }
                }
            }
        }

        return json_data(0,$this->codeMessage[0],$comment);
    }

    /**
     * 获得某个评论的详细内容及这个评论的一级，二级评论
     * @return array
     */
    public function getcomdetail(){
        $commentid = $this->data['commentid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        if(!\app\index\model\CourseReview::get($commentid)){
            return json_data(600,$this->codeMessage[600],'');
        }

        $comment = Db::name('course_review')
            ->field('id,userid,content,createdTime')
            ->page($page,10)
            ->find($commentid);

        $user = User::get($comment['userid']);
        $comment['username']       = $user->username;
        $comment['avatar']         = $user->title;
        $comment['sonreviewNum']   = Db::name('course_review')->where('parentid',$comment['id'])->count();
        $comment['likeNum']        = Db::name('like')->where('type','comments')->where('articleid',$comment['id'])->count();
        if(!empty($this->user)){
            if(Like::get(['userid'=>$this->user->id,'type'=>'comments','articleid'=>$commentid])){
                $comment['is_like'] = 1;
            }
            else{
                $comment['is_like'] = 0;
            }
        }
        $son = Db::name('course_review')->where('parentid',$commentid)->field('id,userid,content,createdTime,touserId')->select();
        if($son){
            foreach ($son as &$s){
                $s['username'] = Db::name('user')->where('id',$s['userid'])->value('username');
                $s['tousername'] = Db::name('user')->where('id',$s['touserId'])->value('username');
                $s['avatar'] = $this->request->domain().DS.Db::name('user')->where('id',$s['userid'])->value('title');
            }
        }
        $comment['son'] = $son;
        unset($comment['id']);

        return json_data(0,$this->codeMessage[0],$comment);
    }

    /**
     * 获得某个课程下的所有问答
     */
    public function courseasklist(){
        $courseid = $this->data['courseid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        if(!CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $askList = Db::name('asklist')
            ->where('courseid',$courseid)
            ->page($page,10)
            ->select();
        foreach ($askList as &$a){
            $user = User::get($a['userID']);
            $a['username'] = $user->username;
            $a['avatar']   = $this->request->domain().DS.$user->title;
            $a['category'] = Db::name('category')->where('code',$a['category_id'])->value('name');
            unset($a['category_id'],$a['userID'],$a['courseid']);
            $a['answerNum'] = Db::name('ask_answer')->where('askID',$a['id'])->count();
            if(!empty($this->user)){
                if(Like::get(['userid'=>$this->user->id,'type'=>'ask','articleid'=>$a['id']])){
                    $a['is_like'] = 1;
                }
                else{
                    $a['is_like'] = 0;
                }
            }
        }
        return json_data(0,$this->codeMessage[0],$askList);
    }

    public function coursedetail(){
        $courseid = $this->data['courseid'];

        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }

        $user = User::get($course['userid']);

        $data = [
            'about'         =>  $course->about,
            'teacher_name'  =>  $user->username,
            'avatar'        =>  $user->title,
            'achivement'    =>  '教师成就'
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }





}
