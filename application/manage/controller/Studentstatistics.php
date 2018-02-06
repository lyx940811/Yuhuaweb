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

class Studentstatistics extends Base{

    public function index(){
        $search=input('get.');
        $where=[];
        if(!empty($search['class'])){
            $where['cr.id']=$search['class'];
        }
        if(!empty($search['majors'])){
            $where['c.id']=$search['majors'];
        }
        if(!empty($search['name'])){
            $where['cr.realname']=['like|idcard',"%{$search['name']}%"];
        }
        $data=DB::table('user_profile up')
            ->join('student_school ss','up.userid=ss.userid')
            ->join('classroom cr','ss.class=cr.id','LEFT')
            ->join('category c','ss.majors=c.code','LEFT')
            ->field('up.sn,up.userid,up.realname,up.idcard,cr.title,c.name,ss.majors')
            ->where($where)
            ->order('up.sn')
            ->paginate(20,false,['query'=>request()->get()]);
        $title=$this->getTitle();//列表头部总和统计
        $info=$this->studentstatistics($data);
        $major=DB::table('category')->field('id,name')->select();
        $class=Db::table('classroom')->field('id,title')->select();
        $this->assign('major',$major);
        $this->assign('class',$class);
        $this->assign('info',$info);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    //计算所有的是统计数据
    public function studentstatistics($data){
        $info=[];
        foreach($data as $key=>$value){
            $info[$key]=$value;
            $studytime=DB::table('study_result_log srl')//学习时长
                ->where('userid',$value['userid'])
                ->sum('TIMESTAMPDIFF(SECOND,starttime,endtime)');
//            $studytime=DB::table('study_result_v13_log srl')
//                ->where('userid',$value['userid'])
//                ->sum('watchTime');
            $info[$key]['studytime']=round($studytime/60/60,1);
            //学习进度
            $majors=$value['majors'];
            $mycourse=Db::table('course_task')
                ->where('courseId','IN',function($query)use($majors){
                    $query->table('categorycourse')
                        ->where('categoryID',$majors)
                        ->where('Flag',1)->field('courseID');
                })->count();
            $mystudy=Db::table('study_result_v13')->where('userid',$value['userid'])->where('ratio',100)->column('taskid');
            $courseporgress='0%';
            $countcourse=count($mystudy);
            if($countcourse>0){
                $courseporgress=round($countcourse/$mycourse*100,2).'%';
            }
            $info[$key]['courseporgress']=$courseporgress;
            //学分
            $info[$key]['score']=0;
            if(!empty($mystudy)){
                $info[$key]['score']=DB::table('course_task')->where('id','in',$mystudy)->sum('point');
            }
            //作业完成数量
            $testpaper=Db::table('testpaper_result')->where('userid',$value['userid'])->count();
            $info[$key]['testpaper']=$testpaper;
            //发帖数量
            $info[$key]['post']=Db::table('asklist')->where('userid',$value['userid'])->count();
            //回帖数量
            $info[$key]['replies']=Db::table('ask_answer')->where('answerUserID',$value['userid'])->count();
        }
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