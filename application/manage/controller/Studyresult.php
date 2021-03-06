<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/6
 * Time: 16:41
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Studyresult extends Base{

    public function index(){

        $userid=$this->request->param('userid')+0;//在学生列表跳转到本列表是使用
        $info = input('get.');
        $data['realname']='';
        $data['classname']='';
        $where = [];
        if(!empty($info['realname'])){
            $data['realname']=$info['realname'];
            $where['u.username'] = ['like',"%{$info['realname']}%"];
        }
        if(!empty($info['classname'])){
            $data['classname']=$info['classname'];
            $where['b.title'] = ['like',"%{$info['classname']}%"];
        }
        if($userid){
            $where['a.userid']=$userid;
        }
        $list = Db::table('study_result_log a')
            ->field('a.*,b.title,c.title ctit,u.username,ct.title as cttitle,ct.length')
            ->join('course b','a.courseid=b.id','LEFT')
            ->join('course_chapter c','a.chapterid=c.id','LEFT')
            ->join('user u','a.userid=u.id','LEFT')
            ->join('course_task ct','b.id=ct.courseId')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);
        $test=$this->Percentage($list);//算百分比
        $course = Db::table('course')->field('id,title')->select();

        $this->assign('info',$data);
        $this->assign('list',$test);
        $this->assign('course',$course);
        $this->assign('typename','学习记录');
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    public function Percentage($info){
        $data=[];
        foreach($info as $k=>$value){
            $minute=floor((strtotime($value['endtime'])-strtotime($value['starttime']))%86400%60);

            $time=date('G',strtotime($value['length']))*60*60+date('i',strtotime($value['length']))*60+date('s',strtotime($value['length']));

            $percentage1=0;
            if($time>0) {
                if ($value['status'] == 1) {
                    $percentage1 = 100;
                } else if (!empty($minute)) {
                    $percentage = $minute / $time;
                    $test=round($percentage,4);
                    $percentage1=sprintf('%.4f',$test)*100;
                }
            }

            $data[$k]['id']=$value['id'];
            $data[$k]['userid']=$value['userid'];
            $data[$k]['status']=$value['status'];
            $data[$k]['title']=$value['title'];
            $data[$k]['ctit']=$value['ctit'];
            $data[$k]['username']=$value['username'];
            $data[$k]['cttitle']=$value['cttitle'];
            $data[$k]['percentage']=$percentage1.'%';
        }
        return $data;
    }
}