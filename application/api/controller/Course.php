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
            ->order('createdTime desc')
            ->page($page,10)
            ->select();
        if($comment){
            foreach ($comment as &$c){
                $user = User::get($c['userid']);
                $c['username'] = $user->username;
                $c['avatar']   = $this->request->domain()."/".$user->title;
                $c['sonreviewNum']   = Db::name('course_review')->where('parentid',$c['id'])->count();
                $c['likeNum']   = Db::name('like')->where('type','comment')->where('articleid',$c['id'])->count();
                if(!empty($this->user)){
                    if(Like::get(['userid'=>$this->user->id,'type'=>'comment','articleid'=>$c['id']])){
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
            ->order('createdTime desc')
            ->find($commentid);

        $user = User::get($comment['userid']);
        $comment['username']       = $user->username;
        $comment['avatar']         = $this->request->domain()."/".$user->title;
        $comment['sonreviewNum']   = Db::name('course_review')->where('parentid',$comment['id'])->count();
        $comment['likeNum']        = Db::name('like')->where('type','comment')->where('articleid',$comment['id'])->count();
        if(!empty($this->user)){
            if(Like::get(['userid'=>$this->user->id,'type'=>'comments','articleid'=>$commentid])){
                $comment['is_like'] = 1;
            }
            else{
                $comment['is_like'] = 0;
            }
        }
        $son = Db::name('course_review')->where('parentid',$commentid)->field('id,userid,content,createdTime,touserId')->order('createdTime desc')->page($page,10)->select();
        if($son){
            foreach ($son as &$s){
                $s['username'] = Db::name('user')->where('id',$s['userid'])->value('username');
                $s['tousername'] = Db::name('user')->where('id',$s['touserId'])->value('username');
                $s['avatar'] = $this->request->domain()."/".Db::name('user')->where('id',$s['userid'])->value('title');
            }
        }
        $comment['son'] = $son;
        

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
            ->order('addtime desc')
            ->page($page,10)
            ->select();
        foreach ($askList as &$a){
            $user = User::get($a['userID']);
            $a['username'] = $user->username;
            $a['avatar']   = $this->request->domain()."/".$user->title;
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

        if(!empty($course->teacherinfo->realname)){
            $teacher_realname = $course->teacherinfo->realname;
            $teacher_avatar = $course->teacherinfo->user->title;
        }
        else{
            $teacher_realname = '还未分配老师';
            $teacher_avatar = 'static/index/images/avatar.png';
        }

        $data = [
            'about'         =>  $course->about,
            'teacher_name'  =>  $teacher_realname,
            'avatar'        =>  $this->request->domain()."/".$teacher_avatar,
            'achivement'    =>  '教师成就'
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }
    /**
     * 获得课程目录(加了进度),abandoned use rebuild version
     */
    public function getcourselesson_abandoned(){
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $courseid = $this->data['courseid'];
        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $fields = 'ct.id as taskid,ct.courseid,ct.length,ct.chapterid,ct.title,cc.title as chapter,cc.seq';
        $lesson = Db::name('course_task')
            ->alias('ct')
            ->join('course_chapter cc','ct.chapterid = cc.id')
            ->field($fields)
            ->order('cc.seq')
            ->where('ct.courseid',$courseid)
            ->page($page,10)
            ->select();

        if(!empty($this->user)){
            foreach ($lesson as &$l){
                $length = explode(':',$l['length']);
                $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;
                if($watch_log = Db::name('study_result')->where(['userid'=>$this->user->id,'courseid'=>$l['courseid'],'chapterid'=>$l['chapterid']])->find()){
                    if($watch_log['status']==1){
                        $l['plan'] = '100';
                    }
                    else{
                        $watch_time = strtotime($watch_log['endtime'])-strtotime($watch_log['starttime']);
                        $l['plan'] = (round($watch_time/$couse_time,2)*100);
                    }
                }
                else{
                    $l['plan'] = '0';
                }
                unset($l['length'],$l['seq']);
            }
        }
        return json_data(0,$this->codeMessage[0],$lesson);
    }
    //rebuild version use this
    public function getcourselesson(){
        $video_type = ['mp4','url'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $courseid = $this->data['courseid'];
        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $fields = 'ct.id as taskid,ct.courseid,ct.length,ct.type,ct.chapterid,ct.title,cc.title as chapter,cc.seq,ct.mediaSource';
        $lesson = Db::name('course_task')
            ->alias('ct')
            ->join('course_chapter cc','ct.chapterid = cc.id')
            ->field($fields)
            ->order('cc.seq')
            ->where('ct.courseid',$courseid)
            ->page($page,10)
            ->select();
        foreach ($lesson as &$l){
        if($l['type']!='url'){
            $l['mediaSource'] = $this->request->domain()."/".$l['mediaSource'];
        }
        $l['plan'] = '0';
        if(!empty($this->user)){
            
                
                if($watch_log = Db::name('study_result')->where(['userid'=>$this->user->id,'courseid'=>$l['courseid'],'chapterid'=>$l['chapterid']])->find()){
                    if($watch_log['status']==1){
                        $l['plan'] = '100';
                    }
                    else{
                        //这个是还没学完的课程
                        if(!in_array($l['type'],$video_type)){
                            $l['plan'] = '100';
                        }
                        else{
                            $length = explode(':',$l['length']);
                            $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;

                            $watch_time = strtotime($watch_log['endtime'])-strtotime($watch_log['starttime']);
                            $l['plan'] = (round($watch_time/$couse_time,2)*100);
                        }
                    }
                }
                else{
                    $l['plan'] = '0';
                }
                unset($l['length'],$l['seq']); 
        }
        }
        return json_data(0,$this->codeMessage[0],$lesson);    }

    //abandoned , use the rebuild version
    public function getcoursetop_abandoned(){
        $course_all_time = 0;
        $courseid = $this->data['courseid'];
        //为了拿顶部的title
        $course = Db::name('course')->field('title')->find($courseid);
        //课程下的所有任务，为了计算时间
        $task = Db::name('course_task')
            ->where('courseId',$courseid)
            ->field('id,courseId,chapterid,length,title')
            ->select();

        //计算这个课程的所有的task的总时间
        if($task){
            foreach ( $task as $t){
                $length = explode(':',$t['length']);
                $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;
                $course_all_time = $course_all_time+$couse_time;
            }
        }
        //为了计算任务比需要的分母
        $taskNum = count($task);

        empty($task)?$next_task='还未有新课程':$next_task = $task[0]['title'];
        empty($task)?$learn_taskid='0':$learn_taskid = $task[0]['id'];
        
        //假如登陆了
        if(!empty($this->user)){
            $learn_task = Db::name('study_result')
                ->where('courseid',$courseid)
                ->where('userid',$this->user->id)
                ->order('chapterid desc')
                ->find();
            //找到最后一条学习记录
            if($learn_task){
                if($learn_task['status']==0){
                    //还没学完,需要拿这一节课得名字
                    $next_task = Db::name('course_task')
                        ->where('courseid',$courseid)
                        ->where('chapterid',$learn_task['chapterid'])
                        ->find();
                    $learn_taskid = $next_task['id'];
                    $next_task    = $next_task['title'];
                }
                else{
                    //学完了，需要拿下一节课得名字
                    $next_task = Db::name('course_task')
                        ->where('courseid',$courseid)
                        ->where('chapterid','>',$learn_task['chapterid'])
                        ->find();

                    if(!empty($next_task)){
                        $learn_taskid = $next_task['id'];
                        $next_task = $next_task['title'];
                    }
                    else{
                        $next_task = '已学完';
                        $learn_taskid = 0;
                    }
                }
                //还需要算进度
                $learn_task = Db::name('study_result')
                    ->where('courseid',$courseid)
                    ->where('userid',$this->user->id)
                    ->order('chapterid desc')
                    ->select();
                $has_learn_time = 0;
                foreach ( $learn_task as $t){
                    if($t['status']==0){
                        $watch_time = strtotime($t['endtime'])-strtotime($t['starttime']);
                    }
                    else{
                        $length = Db::name('course_task')->where(['courseId'=>$t['courseid'],'chapterid'=>$t['chapterid']])->value('length');
                        $length = explode(':',$length);
                        $watch_time = $length[2]+$length[1]*60+$length[0]*3600;
                    }
                    $has_learn_time = $has_learn_time+$watch_time;
                }
                if($course_all_time!=0){
                    $plan = (round($has_learn_time/$course_all_time,2)*100);
                }

                //拿到完成的任务比（1/30）
                $has_done_task = Db::name('study_result')
                    ->where('courseid',$courseid)
                    ->where('userid',$this->user->id)
                    ->where('status',1)
                    ->select();
                $has_done = count($has_done_task).'/'.$taskNum;
            }
            else{
                //没找到学习记录
                $plan = '0';
                $has_done = '0/'.$taskNum;
            }
        }
        else{
            //没登陆
            $plan = '0';
            $has_done = '0/'.$taskNum;
        }
        $data = [
            'title'     =>  $course['title'],
            'plan'      =>  $plan,
            'has_done'  =>  $has_done,
            'next_task' =>  $next_task,
            'next_task_id'  =>  $learn_taskid,
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }

    //rebuild version,use this
    public function getcoursetop(){
        $video_type = ['mp4','url'];
        $courseid = $this->data['courseid'];
        //为了拿顶部的title
        $course = Db::name('course')->field('title,categoryId')->find($courseid);
        
        //课程下的所有任务，为了计算时间
        $task = Db::name('course_task')
            ->where('courseId',$courseid)
            ->field('id,courseId,chapterid,length,title')
            ->order('chapterid asc')
            ->select();

        //为了计算任务比需要的分母
        $taskNum = count($task);

        empty($task)?$next_task='还未有新课程':$next_task = $task[0]['title'];
        empty($task)?$learn_taskid='0':$learn_taskid = $task[0]['id'];

        //假如登陆了
        if(!empty($this->user)){
            $learn_task = Db::name('study_result')
                ->where('courseid',$courseid)
                ->where('userid',$this->user->id)
                ->order('chapterid desc')
                ->find();
            //找到最后一条学习记录
            if($learn_task){
                //找到学习记录了，所以在做百分比计算的时候除数绝对不可能是0
                if($learn_task['status']==0){
                    //还没学完,需要拿这一节课得名字
                    $next_task = Db::name('course_task')
                        ->where('courseid',$courseid)
                        ->where('chapterid',$learn_task['chapterid'])
                        ->find();
                    $learn_taskid = $next_task['id'];
                    $next_task    = $next_task['title'];
                }
                else{
                    //学完了，需要拿下一节课得名字
                    $next_task = Db::name('course_task')
                        ->where('courseid',$courseid)
                        ->where('chapterid','>',$learn_task['chapterid'])
                        ->find();
                    //找到下一节课了，拿到并赋值
                    if(!empty($next_task)){
                        $learn_taskid = $next_task['id'];
                        $next_task = $next_task['title'];
                    }
                    else{
                        //没有找到下一节课的名字，代表是最后一节课了
                        $next_task = '已学完';
                        $learn_taskid = 0;
                    }
                }
                //还需要算进度
                //拿出所有学习记录
                $learn_task = Db::name('study_result')
                    ->where('courseid',$courseid)
                    ->where('userid',$this->user->id)
                    ->order('chapterid desc')
                    ->select();
                //先预定学完的课程为0，学完的课程+1
                $has_learn_time = 0;
                if($learn_task){
                    //循环学习记录，每一条对应该课程下的每个task的学习情况
                    foreach ( $learn_task as $t){
                        //如果没有学完的话
                        if($t['status']==0){
                            //先选出type，判断是否为视频
                            $task = Db::name('course_task')
                                ->where('courseid',$t['courseid'])
                                ->where('chapterid',$t['chapterid'])
                                ->field('type,length')
                                ->find();
                            //不是视频，直接完成数目+1
                            if(!in_array($task['type'],$video_type)){
                                $has_learn_time = $has_learn_time+1;
                            }
                            else{
                                //视频类型，计算看过的时间/该task的总时间，得到比例，取两位小数
                                $watch_time = strtotime($t['endtime'])-strtotime($t['starttime']);
                                $length = explode(':',$task['length']);
                                $task_all_time = $length[2]+$length[1]*60+$length[0]*3600;
                                $has_learn_time = $has_learn_time+round(($watch_time/$task_all_time),2);
                            }
                        }
                        else{
                            //如果学完了得话，直接完成课目+1
                            $has_learn_time = $has_learn_time+1;
                        }
                    }
                }
                //345
                $plan = (round($has_learn_time/$taskNum,2)*100);

                //拿到完成的任务比（1/30）
                $has_done_task = Db::name('study_result')
                    ->where('courseid',$courseid)
                    ->where('userid',$this->user->id)
                    ->where('status',1)
                    ->select();
                $has_done = count($has_done_task).'/'.$taskNum;
            }
            else{
                //没找到学习记录
                $plan = '0';
                $has_done = '0/'.$taskNum;
            }
        }
        else{
            //没登陆
            $plan = '0';
            $has_done = '0/'.$taskNum;
        }
        $data = [
            'categoryId'=>  $course['categoryId'],
            'title'     =>  $course['title'],
            'plan'      =>  $plan,
            'has_done'  =>  $has_done,
            'next_task' =>  $next_task,
            'next_task_id'  =>  $learn_taskid,
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }

    /**
     * 获得一节课得详细url和类型
     * @return array
     */
    public function getlessondetail(){
        $taskid = $this->data['taskid'];
        $course_key = ['id'=>"",'title'=>"",'courseid'=>"",'chapterid'=>"",'type'=>"",'mediaSource'=>"",'length'=>"",];
        $course = Db::name('course_task')->find($taskid);
        if(!$course){
            return json_data(200,$this->codeMessage[200],[]);
        }
        $course = array_intersect_key($course,$course_key);
        if($course['type']!='url'){
            $course['mediaSource'] = $this->request->domain()."/".$course['mediaSource'];
        }
        return json_data(0,$this->codeMessage[0],$course);
    }





}
