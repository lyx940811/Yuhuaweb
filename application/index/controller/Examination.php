<?php
namespace app\index\controller;

use think\Exception;
use think\Controller;
use think\Db;
use think\Validate;
use think\Requst;

class Examination extends Controller{

    //跳转考试弹框
    public function alert(){
        $courseid=$this->request->param('course');
        $where['courseid']=$courseid;
        $list=Db::name('testpaper')->where($where)->find();
        $list['count']=Db::name('testpaper_item')->where('paperId',$list['id'])->count();
        $this->assign('list',$list);
        $this->assign('courseid',$courseid);
        return $this->fetch();
    }
    //考试页面
//    public function examination(){
//        $courseid=$this->request->param('course');
//        $id=DB::name('testpaper')
//            ->where('courseid',$courseid)
//            ->field('id')
//            ->find();
//        $data=$this->getExamination($id);
//
//    }
//    public function getExamination($id){
//        $data=Db::name('testpaper_item')
//            ->where('paperID',$id['id'])
//            ->field('count(id) as num,sum(score) as score')
//            ->group('questiontype')
//            ->select();dump($data);die;
//        foreach($data as $k=>$v){
//
//        }
//
//
//    }
    //考试页面
    public function examination(){
        $courseid=$this->request->param('course');
        $where['t.courseid']=$courseid;
        $info=Db::name('testpaper_item as ti')
            ->join('testpaper t','ti.paperID=t.id')
            ->join('question q','ti.questionId=q.id')
            ->field('ti.score,ti.questiontype,t.courseid,t.passedScore,t.name,t.description,t.score as total,q.stem,q.answer,q.metas')
            ->where($where)
            ->order('ti.id')
            ->select();
        $array=['0'=>'A','1'=>'B','2'=>'C','3'=>'D','4'=>'E'];
        $num=$this->getExamination($courseid);//查询每种题型有几个积分
        foreach($info as $k=>$v){
            $data[$k]=$v;
            if(!empty($v['metas'])){
                $title=json_decode($v['metas']);
                $title1=$title->choices;
                $data[$k]['question']=$title1;
            }else{
                $data[$k]['question']=[];
            }

        }
        $this->assign('num',$num);
        $this->assign('info',$data);
        $this->assign('status',$array);
        return $this->fetch();
    }
//    //考试页面数据处理
    public function getExamination($courseid){
        $where['t.courseid']=$courseid;
        $info=Db::name('testpaper_item as ti')
            ->join('testpaper t','ti.paperID=t.id')
            ->field('count(ti.id) as num,sum(ti.score) as score,questiontype')
            ->where($where)
            ->order('ti.id')
            ->group('questiontype')
            ->select();
        return $info;

    }
    //考试成绩
    public function examresults(){

        return $this->fetch();
    }
    //结束考试
    public function examend(){

        return $this->fetch();
    }
}