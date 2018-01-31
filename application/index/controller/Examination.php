<?php
namespace app\index\controller;

use think\Exception;
use think\Controller;
use think\Db;
use think\Validate;
use think\Requst;


class Examination extends Home{

    //跳转考试弹框
    public function alert(){
        $courseid=$this->request->param('course');
        $where['courseid']=$courseid;
        $list=Db::name('testpaper')->where($where)->order('createTime desc')->find();
        $list['count']=Db::name('testpaper_item')->where('paperId',$list['id'])->count();
        $this->assign('list',$list);
        $this->assign('courseid',$courseid);
        return $this->fetch();
    }

    //考试页面
    public function examination(){
        $date=date('Y-m-d H:i:s');
        $courseid=$this->request->param('course');
        $where['t.courseid']=$courseid;
        $list=Db::name('testpaper')->where('courseid',$courseid)->order('createTime desc')->field('createTime')->find();
        if(!empty($list)){
            $where['t.createTime']=$list['createTime'];
        }

        $info=Db::name('testpaper_item as ti')
            ->join('testpaper t','ti.paperID=t.id')
            ->join('question q','ti.questionId=q.id')
            ->field('q.answer,q.id,ti.paperID,ti.questionid,ti.score,ti.questiontype,t.courseid,t.passedScore,t.name,t.description,t.score as total,q.stem,q.answer,q.metas')
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
        $this->assign('time',$date);
        $this->assign('courseid',$courseid);
        $this->assign('num',$num);
        $this->assign('info',$data);
        $this->assign('status',$array);
        return $this->fetch();
    }
//    //考试页面数据处理
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
    //考试成绩
    public function examresults(){

        $courseid=$this->request->param('course');
        $list=Db::name('testpaper')->where('courseid',$courseid)->order('createTime desc')->find();
        //查询是否完成阅卷
        $marking=$this->isNotMarking($list['id']);
        $test=$this->getExamresults($list['id']);
        if($marking>0){
            $this->assign('type',1);
            $this->assign('myscore',$test['myscore']);
            unset($test['myscore']);
            $this->assign('title',$test);
            $this->assign('list',$list);
        }else{
            $examination=$this->selectExamination($test['myscore'],$courseid,$list['createTime']);//查询题目;
            $array=['0'=>'A','1'=>'B','2'=>'C','3'=>'D','4'=>'E'];
            $num=$this->getExamination($courseid);//查询每种题型有几个积分
            $this->assign('num',$num);
            $this->assign('status',$array);
            $this->assign('type',2);
            $this->assign('myscore',$test['myscore']);
            unset($test['myscore']);
            $this->assign('title',$test);
            $this->assign('list',$list);
            $this->assign('info',$examination);
        }
        return $this->fetch();
    }
    //查看是否完成阅卷
    public function isNotMarking($testpaperid){

        $where['userid']=$this->user->id;
        $where['paperID']=$testpaperid;
        $where['status']=0;
        $count=Db::name('testpaper_item_result')->where($where)->count();
        return $count;
    }
    //查询考试成绩
    public function getExamresults($paperid){
        $where['paperID']=$paperid;
        $where['userid']=$this->user->id;
        $where1=$where;
        $where1['status']=1;
        $myscore=Db::name('testpaper_item_result tir')->where($where1)->sum('score');
        $test=$this->selectQuesNum($where);
        $test['myscore']=$myscore;
        return $test;
    }
    public function selectQuesNum($where){
        $list=Db::name('testpaper_item_result tir')
            ->join('question q','tir.questionId=q.id')
            ->field('count(q.type) as count,q.type,sum(tir.score) as totalscore')
            ->where($where)
            ->group('q.type')
            ->select();
        $info=[];
        foreach($list as $k=>$v){
            $where['q.type']=$v['type'];
            $where['tir.status']=1;
            $data=Db::name('testpaper_item_result tir')
               ->join('question q','tir.questionId=q.id')
               ->where($where)
               ->group('q.type')
               ->count();
            $info[$v['type']]=$v;
            $info[$v['type']]['true']=$data;
            $info[$v['type']]['flase']=$v['count']-$data;

        }
        return $info;
    }
    //结束考试
    public function examend(){
        $info=input('post.');
        $list=Db::name('testpaper')->where('courseid',$info['courseid'])->order('createTime desc')->field('createTime')->find();
        if(!empty($list)){
            $where['createTime']=$list['createTime'];
        }
        $where['id']=$info['paperid'];
        $list=Db::name('testpaper')
                ->where($where)
                ->find();
        $data=json_decode($list['metas'],true);
        $test=$this->getExamend($info,$data);
        $examination=$this->selectExamination($test['myscore'],$info['courseid'],$list['createTime']);//查询题目;
//        dump($examination);
        $array=['0'=>'A','1'=>'B','2'=>'C','3'=>'D','4'=>'E'];
        $num=$this->getExamination($info['courseid']);//查询每种题型有几个积分
        $this->assign('num',$num);
        $this->assign('status',$array);

        $this->assign('myscore',$test['myscore']);
        unset($test['myscore']);
        $this->assign('title',$test);
        $this->assign('list',$list);
        $this->assign('info',$examination);
        return $this->fetch();
    }

    //查询题目一级
    public function selectExamination($myscore,$courseid,$createtime){
        $where['t.courseid']=$courseid;
        $where['t.createTime']=$createtime;
        $info=Db::name('testpaper_item as ti')
            ->join('testpaper t','ti.paperID=t.id')
            ->join('question q','ti.questionId=q.id')
            ->field('q.answer,q.id,ti.paperID,ti.questionid,ti.score,ti.questiontype,t.courseid,t.passedScore,t.name,t.description,t.score as total,q.stem,q.answer,q.metas')
            ->where($where)
            ->order('ti.id')
            ->select();
        $data=$this->getSelectExamination($info);
        return $data;
    }
    //查询题目拼装数据
    public function getSelectExamination($info){
        $data=[];
        foreach($info as $k=>$v){

            $where['paperID']=$v['paperID'];
            $where['questionid']=$v['questionid'];
            $list=Db::name('testpaper_item_result')->where($where)->find();
            if($v['questiontype']=='single_choice'){
                $name='single';
            }elseif($v['questiontype']=='choice'){
                $name='choice';
            }elseif($v['questiontype']=='determine'){
                $name='determine';
            }elseif($v['questiontype']=='essay'){
                $name='essay';
            }
            $data[$name][$k]=$v;
            $data[$name][$k]['answer']=json_decode($v['answer'],true);
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
        return $data;
    }
    //计算成绩存表
    public function getExamend($data,$mater){
        $info=[];
        $essay=2;
        $choicetrue=$signtrue=$determinetrue=$choiceflase=$signflase=$determineflase=$choicenone=$signnone=$determinenone=$choicescore=$signscore=$determinescore=0;
        $examination=[];
        foreach($data['data'] as $key=>$val){
            foreach($val['question'] as $k=>$v){
                if(isset($val['answer'][$v])) {
                    $list = Db::name('question')->where('id', $v)->find();
                    $info['paperID'] = $data['paperid'];
                    $info['itemID'] = 0;
                    $info['userid'] = $this->user->id;
                    $info['questionId'] = $v;
                    if(is_array($val['answer'][$v])){
                        foreach($val['answer'][$v] as $ka=>$va){
                            $ans[]=$va;
                        }
                        $answer=$ans;
                        $ans=[];
                    }else{
                        $answer=[$val['answer'][$v]];
                    }
                    $info['answer'] = json_encode($answer,true);
                    $info['resultId'] = 1;
                    if ($key == 'choice') {
                        $test = $val['answer'][$v];
                        $type = json_decode($list['answer'], true);
                        $choice = array_diff($test, $type);
                        if ($choice) {
                            $info['score'] = 0;
                            $info['status'] = 3;
                            $choiceflase+= 1;//多选答错几道题
                        } elseif (count($test) == count($type)) {
                            $info['score'] = $list['score'];
                            $choicescore+=$list['score'];//答对多少分
                            $info['status'] = 1;
                            $choicetrue = $choicetrue + 1;//多选答对几道题
                        } else {
                            $info['score'] = $list['score'] - (count($type) - count($test)) * $mater['missScores']['choice'];
                            $choicescore+=$info['score'];
                            $info['status'] = 2;
                            $choicetrue = $choicetrue + 1;//多选答对几道题
                        }
                    }elseif($key == 'essay'){
                        $essay=1;
                        $info['score'] = 0;
                        $info['status'] = 0;
                    }else {
                        $test = $val['answer'][$v];
                        $type = json_decode($list['answer'], true);

                        if ($test == $type[0]) {
                            $info['score'] = $list['score'];
                            $info['status'] = 1;
                            if ($key == 'sign') {
                                $signtrue += 1;
                                $signscore+=$list['score'];
                            } else {
                                $determinetrue += 1;
                                $determinescore+=$list['score'];
                            }
                        } else {
                            $info['score'] = 0;
                            $info['status'] = 3;
                            if ($key == 'sign') {
                                $signflase += 1;
                            } else {
                                $determineflase += 1;
                            }
                        }
                    }
                    $where['paperID']=$info['paperID'];
                    $where['questionId']=$info['questionId'];
                    $paper=DB::table('testpaper_item_result')->where($where)->find();
                    if(!empty($paper)){
                        $info['resultId']=$paper['resultId']+1;
                        $save= DB::table('testpaper_item_result')->where('id',$paper['id'])->update($info);
                    }else{
                        $save = DB::table('testpaper_item_result')->insert($info);
                    }
//
                }else{
                    if($key=='sign'){
                        $signnone+=1;
                    }elseif($key="choice"){
                        $choicenone+=1;
                    }elseif($key="determine"){
                        $determinenone+=1;
                    }
                }
            }
        }
        $examination['myscore']=$signscore+$choicescore+$determinescore;
        $examination['sign']=['signtrue'=>$signtrue,'signflase'=>$signflase,'signnone'=>$signnone,'signscore'=>$signscore];
        $examination['choice']=['choicetrue'=>$choicetrue,'choiceflase'=>$choiceflase,'choicenone'=>$choicenone,'choicescore'=>$choicescore];
        $examination['determine']=['determinetrue'=>$determinetrue,'determineflase'=>$determineflase,'determinenone'=>$determinenone,'determinescore'=>$determinescore];
        $list=[];
        $list['paperID']=$data['paperid'];
        $list['userid']=$this->user->id;
        $list['score']=$examination['myscore'];
        if($essay==1){
            $list['Flag']=0;
        }else{
            $list['Flag']=1;
        }
        $list['subjectiveScore']=$examination['myscore'];
        $list['beginTime']=$data['starttime'];
        $list['endTime']=date('Y-m-d H:i:s');
        $savatr=DB::name('testpapter_result')->insert($list);
        if($savatr){
            return $examination;
        }else{
            exit;
        }

    }
}