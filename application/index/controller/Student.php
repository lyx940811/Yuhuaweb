<?php
namespace app\index\controller;

use app\index\model\Asklist;
use think\Controller;
use think\Config;
use think\Loader;
use think\Db;

use app\index\model\User as UserModel;
class Student extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    public function mystudy()
    {
        !empty($this->request->param('page'))?$page = $this->request->param('page'):$page = 1;
        $video_type = ['mp4','url'];
        //先选择学习记录并group掉重复的课程
        $course = Db::name('study_result')
            ->alias('sr')
            ->join('course c','sr.courseid=c.id')
            ->field('sr.courseid as id,c.title,c.smallPicture')
            ->where('sr.userid',$this->user->id)
            ->group('sr.courseid')
            ->paginate(9);
        //这里以课程为基底，查出对应数据
        $done_course = [];
        if($course){
            foreach ( $course as &$c ){
                //拼上域名
//                $c['smallPicture'] = $this->request->domain()."/".$c['smallPicture'];
                //算课程总数，算法为完成的课程数/课程总数，pdf和ppt直接为整个的课程数1，视频的话按看过的时间/该课程总时间，得到一个完成的比例，当作小数用
                $all_task_num = Db::name('course_task')->where('courseId',$c['id'])->where('status',1)->count();
                //一共学的时间
                //先赋值一个基底防止没有数据
                $c['lastwatch'] = '';
                $c['plan'] = "0";
                //选出对应课程id的学习记录,结果是学过了这节课的哪些task
                $learn_task = Db::name('study_result')
                    ->where('courseid',$c['id'])
                    ->where('userid',$this->user->id)
                    ->order('endtime desc')
                    ->select();
                //完成的课程数目从0开始计算
                $has_learn_time = 0;
                //如果找到该课程的学习记录了（原则上碰不到找不到的情况）
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
                    //拿倒序排列的第一个作为最后观看时间
                    $c['lastwatch'] = $learn_task[0]['endtime'];
                }
                //计算这个课程的完成比，换算成百分比
                $c['plan'] = (round($has_learn_time/$all_task_num,2)*100);
                //如果不是100%的话，放入正在学习的数组
                if($c['plan']!='100'){
                    $done_course[] = $c;
                }
            }
        }
        $this->assign('page',$page);
        $this->assign('course',$done_course);
        return $this->fetch();
    }

    public function getmyclass()
    {
        $type = $this->request->param('type');

    }

    public function discussions()
    {
        return $this->fetch();
    }
    public function donestudy()
    {
        !empty($this->request->param('page'))?$page = $this->request->param('page'):$page = 1;
        $video_type = ['mp4','url'];
        //先选择学习记录并group掉重复的课程
        $course = Db::name('study_result')
            ->alias('sr')
            ->join('course c','sr.courseid=c.id')
            ->field('sr.courseid as id,c.title,c.smallPicture')
            ->where('sr.userid',$this->user->id)
            ->group('sr.courseid')
            ->page($page,10)
            ->select();

        $done_course = array();

        //这里以课程为基底，查出对应数据
        if($course){
            foreach ( $course as &$c ){
                //拼上域名
//                $c['smallPicture'] = $this->request->domain()."/".$c['smallPicture'];
                //算课程总数，算法为完成的课程数/课程总数，pdf和ppt直接为整个的课程数1，视频的话按看过的时间/该课程总时间，得到一个完成的比例，当作小数用
                $all_task_num = Db::name('course_task')->where('courseId',$c['id'])->where('status',1)->count();
                //一共学的时间
                //先赋值一个基底防止没有数据
                $c['lastwatch'] = '';
                $c['plan'] = "0";
                //选出对应课程id的学习记录,结果是学过了这节课的哪些task
                $learn_task = Db::name('study_result')
                    ->where('courseid',$c['id'])
                    ->where('userid',$this->user->id)
                    ->order('endtime desc')
                    ->select();
                //完成的课程数目从0开始计算
                $has_learn_time = 0;
                //如果找到该课程的学习记录了（原则上碰不到找不到的情况）
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
                    //拿倒序排列的第一个作为最后观看时间
                    $c['lastwatch'] = $learn_task[0]['endtime'];
                }
                //计算这个课程的完成比，换算成百分比
                $c['plan'] = (round($has_learn_time/$all_task_num,2)*100);
                //如果不是100%的话，放入正在学习的数组
                if($c['plan']=='100'){
                    $done_course[] = $c;
                }
            }
        }
        else{
        }
        $this->assign('page',$page);
        $this->assign('course',$done_course);
        return $this->fetch();
    }
    public function collect()
    {
        $video_type = ['mp4','url'];
        !empty($this->request->param('page'))?$page = $this->request->param('page'):$page = 1;
        $field = 'cf.id,cf.courseid,cf.userid,c.title,c.smallPicture';
        $course = Db::name('course_favorite')
            ->alias('cf')
            ->join('course c','cf.courseid=c.id')
            ->field($field)
            ->where('cf.userid',$this->user->id)
            ->page($page,10)
            ->select();
        $done_course = array();
        //这里以课程为基底，查出对应数据
        if($course){

            foreach ( $course as &$c ){
                //拼上域名
//                $c['smallPicture'] = $this->request->domain()."/".$c['smallPicture'];
                //算课程总数，算法为完成的课程数/课程总数，pdf和ppt直接为整个的课程数1，视频的话按看过的时间/该课程总时间，得到一个完成的比例，当作小数用
                $all_task_num = Db::name('course_task')->where('courseId',$c['courseid'])->where('status',1)->count();

                //一共学的时间
                //先赋值一个基底防止没有数据
                $c['lastwatch'] = '';
                $c['plan'] = "0";
                if($all_task_num!=0){
                    //选出对应课程id的学习记录,结果是学过了这节课的哪些task
                    $learn_task = Db::name('study_result')
                        ->where('courseid',$c['courseid'])
                        ->where('userid',$this->user->id)
                        ->order('endtime desc')
                        ->select();
                    //完成的课程数目从0开始计算
                    $has_learn_time = 0;
                    //如果找到该课程的学习记录了（原则上碰不到找不到的情况）
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
                        //拿倒序排列的第一个作为最后观看时间
                        $c['lastwatch'] = date('Y-m-d',strtotime($learn_task[0]['endtime']));
                    }
                    //计算这个课程的完成比，换算成百分比
                    $c['plan'] = (round($has_learn_time/$all_task_num,2)*100);
                }

                $done_course[] = $c;
            }
        }
        $this->assign('page',$page);
        $this->assign('course',$done_course);

        return $this->fetch();
    }
    public function homeworkfirst()
    {
        return $this->fetch();
    }
    public function questions()
    {
        $asklist = Asklist::where('userID',$this->user->id)->paginate(10);
        $this->assign('asklist',$asklist);
        return $this->fetch();
    }





}
