<?php
/**
 * Created by PhpStorm.
 * User: M'S
 * Date: 2017/12/26
 * Time: 15:52
 */
namespace app\manage\controller;

use think\Db;
use think\query;

class Coursestatistics extends Base{

    public function index(){
        $search=input('get.');
        $where=[];
        if(!empty($search['class'])){
            $where['id']=$search['class'];
        }
        if(!empty($search['name'])){
            $where['realname']=['like|idcard',"%{$search['name']}%"];
        }
        $where['status']=1;
        $data=DB::table('course c')
            ->join('teacher_info tf','c.teacherIds=tf.id')
            ->field('c.id,c.title,tf.realname,tf.sn')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);
//        $title=$this->getTitle();//列表头部总和统计
        $info=$this->studentstatistics($data);
        $this->assign('info',$info);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    //计算所有的是统计数据
    public function studentstatistics($data){
        $info=[];
        foreach($data as $key=>$value){
            $info[$key]=$value;
            $alltask=Db::table('course_task')->where('courseid',$value['id'])->column('id');
            $info[$key]['coursechapter']=count($alltask);//课程章节数量
            $coursetask=DB::table('course_task')
                  ->where('type',['=','mp4'],['=','flv'],'or')
                  ->where('courseid',$value['id'])->column('id');//视频资源数量

            $info[$key]['videonum']=count($coursetask);
            $array=['mp4','flv','test','exam'];
            $info[$key]['filenum']=Db::table('course_task')
                  ->where('type','not in',$array)
                  ->where('courseid',$value['id'])->count();//文档资源数量
            $info[$key]['coursetime']=DB::table('study_result_v13_log srl')//学习时长
                  ->where('taskid','in',$coursetask)
                  ->sum('watchTime');
            $postnum=Db::table('asklist')->where('courseid',$value['id'])->column('id');//所有发帖的id
            $info[$key]['postnum']=count($postnum);//发帖数量
            $info[$key]['replies']=Db::table('ask_answer')->where('askid','in',$postnum)->count();

            //学习进度
            $majorsid=DB::table('categorycourse')->where('courseid',$value['id'])->value('categoryID');
            $allstudent=Db::table('student_school')->where('majors',$majorsid)->count();
            $courseporgress='0%';
            if($allstudent){
                $lastchapter=DB::table('course_chapter')->where('courseid',$value['id'])->order('seq desc')->value('id');
                if($lastchapter){
                    $lasttask=DB::table('course_task')->where('courseid',$value['id'])->where('chapterid',$lastchapter)->order('sort desc')->value('id');
                    $mystudy=Db::table('study_result_v13')->where('taskid',$lasttask)->where('ratio',100)->count();
                    if($mystudy>0){
                        $courseporgress=round($mystudy/$allstudent*100,2).'%';
                    }
                }
            }
            $info[$key]['courseporgress']=$courseporgress;
            //作业数量
//            $info[$key]['testpaper']=DB::table('')


        } dump($info);die;
        return $info;
    }

    //列表头部总和统计
    public function getTitle(){
        $all=Db::table('user_profile')->column('userid');//所有学生的userid
        $data['alluser']=count($all);//学生总数
        $data['postall']=Db::table('asklist')->where('userid','in',$all)->count();//发帖总数
        $data['replies']=Db::table('ask_answer')->where('answerUserID','in',$all)->count();//回帖总数
        $data['testpaper']=Db::table('testpaper_result')->count();//作业完成数量
        return $data;
    }
}