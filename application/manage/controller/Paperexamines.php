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
            ->field('tir.userid,tir.paperid,sum(tir.score) as myscore,t.courseid,tir.status,t.name,t.score,t.type as ttype,up.realname,c.title')
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
        $test=[];
        $userid=$this->request->param('userid');
        $paperid=$this->request->param('paperid');
        $courseid=$this->request->param('courseid');
        $name=$this->request->param('name');
        $title=$this->request->param('title');

        $where['tir.userid']=$userid;
        $where['tir.paperid']=$paperid;
        $where['q.type']='essay';
        $data=DB::name('testpaper_item_result tir')
            ->join('question q','tir.questionid=q.id')
            ->join('testpaper t','tir.paperid=t.id')
            ->field('q.type,q.stem,tir.*,t.score as tscore')
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
            $test[$k]['answer']=json_decode($v['answer'],true);
            $test[$k]['scores']=$score['score'];
        }
        $this->assign('name',$name);
        $this->assign('title',$title);
        $this->assign('userid',$userid);
        $this->assign('paperid',$paperid);
        $this->assign('courseid',$courseid);
        $this->assign('info',$test);
        return $this->fetch();
    }

    //试卷批阅
    public function add(){
        $info=input('get.');
        foreach($info as $k=>$v){
            if(is_array($v)) {
                $data = [];
                if ($v['passedscores'] > 0) {
                    $data['status'] = 1;
                } else {
                    $data['status'] = 3;
                }
                $data['score'] = $v['passedscores'];
                $save = DB::table('testpaper_item_result')->where('id', $v['id'])->update($data);
                if (!is_numeric($save)) {
                    return ['error' => '保存失败', 'code' => '200'];
                }
            }else{
                unset($info[$k]);
            }
        }
        return ['info'=>'保存成功','code'=>'000'];
    }

    //查看考生试卷
    public function lookpaper(){
        $userid=$this->request->param('userid');
        $paperid=$this->request->param('paperid');
        $courseid=$this->request->param('courseid');
        $name=$this->request->param('name');
        $title=$this->request->param('title');
        $list=Db::name('testpaper')->where('courseid',$courseid)->order('createTime desc')->find();
        $examination=$this->selectExamination($courseid,$list['createTime'],$userid);//查询题目;
        $array=['0'=>'A','1'=>'B','2'=>'C','3'=>'D','4'=>'E'];
        $num=$this->getExamination($courseid);//查询每种题型有几个积分

        $this->assign('userid',$userid);
        $this->assign('paperid',$paperid);
        $this->assign('name',$name);
        $this->assign('title',$title);
        $this->assign('objective',$examination['objective']);
        $this->assign('subjective',$examination['subjective']);
        unset($examination['objective']);unset($examination['subjective']);
        $this->assign('examination',$examination);
        $this->assign('num',$num);
        $this->assign('status',$array);
        return $this->fetch();
    }
    //查询题目一级
    public function selectExamination($courseid,$createtime,$userid){
        $where['t.courseid']=$courseid;
        $where['t.createTime']=$createtime;
        $info=Db::name('testpaper_item as ti')
            ->join('testpaper t','ti.paperID=t.id')
            ->join('question q','ti.questionId=q.id')
            ->field('q.answer,q.id,ti.paperID,ti.questionid,ti.score,ti.questiontype,t.courseid,t.passedScore,t.name,t.description,t.score as total,q.stem,q.answer,q.metas')
            ->where($where)
            ->order('ti.id')
            ->select();
        $data=$this->getSelectExamination($info,$userid);
        return $data;
    }
    //查询题目拼装数据
    public function getSelectExamination($info,$userid){
        $data=[];
        $subjective=0;//主观题初始分数
        $objective=0;//客观题初始分数
        foreach($info as $k=>$v){

            $where['paperID']=$v['paperID'];
            $where['questionid']=$v['questionid'];
            $where['userid']=$userid;
            $list=Db::name('testpaper_item_result')->where($where)->find();
            if($v['questiontype']=='single_choice'){
                $name='single';
                $objective+=$list['score'];
            }elseif($v['questiontype']=='choice'){
                $name='choice';
                $objective+=$list['score'];
            }elseif($v['questiontype']=='determine'){
                $name='determine';
                $objective+=$list['score'];
            }elseif($v['questiontype']=='essay'){
                $name='essay';
                $subjective+=$list['score'];
            }
            $data[$name][$k]=$v;
            $data[$name][$k]['answer']=json_decode($v['answer'],true);
            $data[$name][$k]['myanswer']=json_decode($list['answer'],true);
            if(!empty($list)){
                $data[$name][$k]['status']=$list['status'];
            }else{
                $data[$name][$k]['status']=3;
            }

            if(!empty($v['metas'])){
                $title=json_decode($v['metas']);
                $title1=$title->choices;
                $data[$name][$k]['question']=$title1;
            }else{
                $data[$name][$k]['question']=[];
            }

        }
        $data['objective']=$objective;
        $data['subjective']=$subjective;
        return $data;
    }
  //考试页面数据处理
    public function getExamination($courseid){
        $where['t.courseid']=$courseid;
        $list=Db::name('testpaper')->where('courseid',$courseid)->order('createTime desc')->field('createTime')->find();
        if(!empty($list)){
            $where['t.createTime']=$list['createTime'];
        }
        $info=Db::name('testpaper_item as ti')
            ->join('testpaper t','ti.paperID=t.id')
            ->field('count(ti.id) as num,sum(ti.score) as score,questiontype')
            ->where($where)
            ->order('ti.id')
            ->group('questiontype')
            ->select();
        return $info;

    }
}