<?php
namespace app\index\controller;

use app\index\model\Asklist;
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

    protected function newstudent($courseid){
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

    protected function getcourseinfo($courseid){
        //course top begin
        $video_type = ['mp4','url'];
        //为了拿顶部的title
        $course = Db::name('course')->field('title')->find($courseid);
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
            'next_task_id'  =>  $learn_taskid,
        ];
    }

    public function catalogue(){
        $video_type = ['mp4','url'];
        $courseid = $this->course['id'];
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
//        var_dump($lesson);die;
        $this->assign('task',$lesson);
        return $this->fetch();
    }
    public function discussion(){
        $asklist = Asklist::where('courseid',$this->course['id'])->order('addtime desc')->paginate(10);
        $page = $asklist->render();
        $this->assign('page',$page);
        $this->assign('asklist',$asklist);
        return $this->fetch();
    }
    public function evaluate(){
        return $this->fetch();
    }
    public function note(){
        return $this->fetch();
    }
    public function material(){

        $file = $this->course->file()->paginate(10);
        $page = $file->render();
        $this->assign('file',$file);
        $this->assign('page',$page);

        return $this->fetch();
    }
    public function summary(){
        return $this->fetch();
    }

}
