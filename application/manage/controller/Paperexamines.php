<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/231
 * Time: 10:40
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Paperexamines extends Base{

    public function index(){

        $info = input('get.');
        $list["type"] ='';
        $list["courseid"] ='';
        $list["status"] ='';
        $list["name"] ='';
        $data=$this->selectList($info);
        $course = Db::table('course')->where('teacherIds',session('admin_uid'))->select();

        $this->assign('course',$course);
        $this->assign('page',$data['rander']);
        unset($data['rander']);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function selectList($list){
        $data=[];
        $where=[];
        if(!empty($list['type'])){
            $where['t.type']=$list['type'];
        }
        if(!empty($list['courseid'])){
            $where['t.courseid']=$list['courseid'];
        }
        if(!empty($list['status'])){
        }
        if(!empty($list['name'])){
            $where['t.name']=['like',"%{$list['name']}%"];
        }
        $info=Db::name('testpaper_item_result tir')
            ->join('user_profile up','tir.userid=up.userid','LEFT')
            ->join('student_school ss','up.userid=ss.userid','LEFT')
            ->join('testpaper t','tir.paperid=t.id','LEFT')
            ->join('classroom c','ss.class=c.id','LEFT')
            ->field('tir.userid,tir.paperid,sum(tir.score) as myscore,tir.status,t.name,t.score,t.type as ttype,up.realname,c.title')
            ->order('tir.paperid')
            ->where($where)
            ->group('tir.userid,tir.paperid')
            ->paginate(20);

        foreach($info as $k=>$v){
            $where=[];
            $where['userid']=$v['userid'];
            $where['paperid']=$v['paperid'];
            $where['status']=0;
            $type=DB::name('testpaper_item_result')
                ->where($where)
                ->count();//查询是否阅卷
            $data[$k]=$v;
            if($type>0){
                $data[$k]['type']=2;//未阅卷
            }else{
                $data[$k]['type']=1;
            }
        }
        $data['rander']=$info->render();
       return $data;
    }

    public function paperexamines(){
        $userid=$this->request->param('userid');
        $paperid=$this->request->param('paperid');
        $where['tir.userid']=$userid;
        $where['tir.paperid']=$paperid;
        $where['q.type']='essay';
        $data=DB::name('testpaper_item_result tir')
            ->join('question q','tir.questionid=q.id')
            ->join('testpaper t','tir.paperid=t.id')
            ->field('q.type,q.stem,tir.*,t.score')
            ->where($where)
            ->select();
        foreach($data as $k=>$v){
            $where=[];
            $where['paperId']=$v['paperID'];
            $where['questionid']=$v['questionId'];
            $score=Db::name('testpaper_item')
                ->field('score')
                ->where($where)->find();
            $test[$k]=$v;
//            $test[$k]['answer']=json_decode($v['score'],true);
            $test[$k]['scores']=$score['score'];
        }
        $this->assign('info',$test);
        return $this->fetch();
    }
}