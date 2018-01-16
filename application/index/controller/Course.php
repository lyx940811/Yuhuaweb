<?php
namespace app\index\controller;

use app\index\model\AskAnswer;
use app\index\model\Asklist;
use app\index\model\CourseNote;
use app\index\model\CourseReview;
use app\index\model\CourseTask;
use app\index\model\StudyResult;
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
        $people = Db::name('study_result')->where('courseid',$courseid)->group('userid')->select();
        $this->course['learnNum'] = count($people);
        $this->assign('course',$this->course);

        //头部信息
        $courseinfo = $this->getcourseinfo($courseid);
        $this->assign('coursedata',$courseinfo);

        //右侧学生信息
        $student = $this->newstudent($courseid);
        $this->assign('student',$student);


    }

    protected function newstudent($courseid)
    {
        $student = Db::name('study_result')
            ->alias('sr')
            ->join('user u','u.id=sr.userid')
            ->where('sr.courseid',$courseid)
            ->field('u.title,u.id,sr.courseid,sr.chapterid,u.username')
            ->group('sr.userid')
            ->limit(10)
            ->select();
        if($student){
            foreach ( $student as &$s ){
                $s['task'] = Db::name('course_task')->where(['courseId'=>$s['courseid'],'chapterid'=>$s['chapterid']])->value('title');
            }
        }
        return $student;
    }

    protected function getcourseinfo($courseid)
    {
        //course top begin
        $video_type = ['mp4','url'];
        //为了拿顶部的title
        $course = Db::name('course')->field('title')->find($courseid);
        //课程下的所有任务，为了计算时间
        $task = Db::name('course_task')
            ->where('courseId',$courseid)
            ->where('status',1)
            ->field('id,courseId,chapterid,length,title,status')
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
                        ->where('status',1)
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
                $has_done = count($has_done_task);
            }
            else{
                //没找到学习记录
                $plan = '0';
                $has_done = 0;
            }
        }
        else{
            //没登陆
            $plan = '0';
            $has_done = 0;
        }

        return $data = [
            'title'     =>  $course['title'],
            'plan'      =>  $plan,
            'has_done'  =>  $has_done,
            'task_num'  =>  $taskNum,
            'next_task' =>  $next_task,
            'next_task_id'    =>  $learn_taskid,
        ];
    }

    public function catalogue()
    {
        $video_type = ['mp4','url'];
        $courseid = $this->course['id'];
        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $fields = 'ct.id as taskid,ct.courseid,ct.length,ct.type,ct.chapterid,ct.title,cc.title as chapter,cc.seq,ct.mediaSource,ct.status';
        $lesson = Db::name('course_task')
            ->alias('ct')
            ->join('course_chapter cc','ct.chapterid = cc.id')
            ->field($fields)
            ->order('cc.seq')
            ->where('ct.courseid',$courseid)
            ->where('ct.status',1)
            ->select();

        if(!empty($this->user)){
            foreach ($lesson as &$l){
                if($course['type']!='url'){
                    $l['mediaSource'] = $this->request->domain()."/".$l['mediaSource'];
                }
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

        $this->assign('task',$lesson);
        return $this->fetch();
    }
    public function discussion()
    {
        $asklist = Asklist::where('courseid',$this->course['id'])->order('addtime desc')->paginate(10);
        $page = $asklist->render();
        $this->assign('page',$page);
        $this->assign('asklist',$asklist);
        return $this->fetch();
    }
    public function evaluate()
    {
        $review = $this->course->review()->where('parentid',0)->order('createdTime desc')->paginate(10);
        $this->assign('review',$review);
        $this->assign('page',$review->render());
        return $this->fetch();
    }
    public function note()
    {
        if($this->user){
            $note = $this->course->note()->where('userid',$this->user->id)->paginate(10);
            $this->assign('note',$note);
            $this->assign('page',$note->render());
        }
        return $this->fetch();
    }
    public function material()
    {
        $file = $this->course->file()->order('createTime desc')->paginate(10);
        $page = $file->render();
        $this->assign('file',$file);
        $this->assign('page',$page);

        return $this->fetch();
    }

    public function down()
    {
        $fileid = $this->request->param('fileid');
        $file = CourseFile::get($fileid);
        $file_path = ".\\".str_replace("/","\\",$file['filepath']);;
        $file_path = iconv("utf-8","gb2312",$file_path);
        if(file_exists($file_path)){
            $fp=fopen($file_path,"r");
            $file_size=filesize($file_path);
            //下载文件需要用到的头
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length:".$file_size);
            Header("Content-Disposition: attachment; filename=".iconv("utf-8","gb2312",$file['filename']));
            $buffer=1024;
            $file_count=0;
            //向浏览器返回数据
            while(!feof($fp) && $file_count<$file_size){
                $file_con=fread($fp,$buffer);
                $file_count+=$buffer;
                echo $file_con;
            }
            fclose($fp);
        }
        else{
            $this->error('文件不存在');
        }


    }
    public function summary()
    {
        return $this->fetch();
    }
    public function askdetail()
    {
        $askid = $this->request->param('askid');
        $ask = Asklist::get($askid);
        $this->assign('ask',$ask);
        $answer = $ask->answer()->order('addtime desc')->paginate(10);
        $this->assign('answer',$answer);
        $this->assign('page',$answer->render());

        $data['hit'] = $ask['hit']+1;
        Asklist::update($data,['id'=>$ask['id']]);
        return $this->fetch();
    }
    public function createask()
    {
        if($this->request->isAjax()){
            $data = $this->request->param();
            $course = CourseModel::get($data['course']);
            $data['category_id'] = $course->category->code;
            $data['courseid'] = $data['course'];
            $data['addtime'] = date('Y-m-d H:i:s',time());
            unset($data['course']);
            if(Asklist::create($data)){
                return 1;
            }
            else{
                return 0;
            }

        }
        return $this->fetch();
    }

    public function taskdetail()
    {
        $taskid = $this->request->param('taskid');
        $task = CourseTask::get($taskid);
        if($task['type']!='url'){
            $task['mediaSource'] = $this->request->domain()."/".$task['mediaSource'];
        }
        $this->assign('task',$task);
        //课程目录
        $video_type = ['mp4','url'];
        $courseid = $this->course['id'];
        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $fields = 'ct.id as taskid,ct.courseid,ct.length,ct.type,ct.chapterid,ct.title,cc.title as chapter,cc.seq,ct.mediaSource,ct.status';
        $lesson = Db::name('course_task')
            ->alias('ct')
            ->join('course_chapter cc','ct.chapterid = cc.id')
            ->field($fields)
            ->order('cc.seq')
            ->where('ct.courseid',$courseid)
            ->where('ct.status',1)
            ->select();

        if(!empty($this->user)){
            foreach ($lesson as &$l){
                if($course['type']!='url'){
                    $l['mediaSource'] = $this->request->domain()."/".$l['mediaSource'];
                }
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
                unset($l['seq']);
            }
        }

        $this->assign('tasklist',$lesson);

        $this->assign('domain',$this->request->domain());
        return $this->fetch();
    }

    public function downtaskfile()
    {
        $taskid = $this->request->param('taskid');
        $task = CourseTask::get($taskid);

        $file_path = ".\\".str_replace("/","\\",$task['mediaSource']);;
        $file_path = iconv("utf-8","gb2312",$file_path);

        if(!file_exists($file_path)){
            $this->error('文件不存在');
        }
        if($this->user){
            $data = [
                'userid'    =>  $this->user->id,
                'courseid'  =>  $task->courseId,
                'chapterid' =>  $task->chapterid,
                'status'    =>  1,
                'starttime' =>  date('Y-m-d H:i:s',time()),
                'endtime'   =>  date('Y-m-d H:i:s',time()),
            ];
            if(!StudyResult::get(['userid'    =>  $this->user->id, 'courseid'  =>  $task->courseId, 'chapterid' =>  $task->chapterid,])){
                StudyResult::create($data);
            }
        }

        $fp=fopen($file_path,"r");
        $file_size=filesize($file_path);

//        $filelastname = explode('.',$task['mediaSource']);
//        $filename = time().".".$filelastname[1];
        $filename = preg_replace('/^.+[\\\\\\/]/', '', $task['mediaSource']);

        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".iconv("utf-8","gb2312",$filename));
        $buffer=1024;
        $file_count=0;
        //向浏览器返回数据
        while(!feof($fp) && $file_count<$file_size){
            $file_con=fread($fp,$buffer);
            $file_count+=$buffer;
            echo $file_con;
        }
        fclose($fp);

    }

    public function savenote()
    {
        $data = $this->request->param();
        $data['courseId'] = $data['course'];
        $data['createdTime'] = date('Y-m-d H:i:s',time());
        unset($data['course']);
        if(CourseNote::create($data)){
            return 1;
        }
        else{
            return 0;
        }

    }

    public function saveanswer()
    {

        $data = $this->request->param();
        $data['addtime'] = date('Y-m-d H:i:s',time());
        unset($data['course']);
        if(AskAnswer::create($data)){
            return 1;
        }
        else{
            return 0;
        }
    }

    public function savereview()
    {
        $data = $this->request->param();
        $data['createdTime'] = date('Y-m-d H:i:s',time());
        $data['courseid'] = $data['course'];
        unset($data['course']);
        if(CourseReview::create($data)){
            return 1;
        }
        else{
            return 0;
        }
    }

}
