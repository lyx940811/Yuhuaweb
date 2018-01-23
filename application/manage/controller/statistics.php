<?php
/**
 * Created by PhpStorm.
 * User: M'S
 * Date: 2017/12/26
 * Time: 15:52
 */
namespace app\manage\controller;

use think\Db;

class Statistics extends Base{

    public function index()
    {
        $sex1=[];
        $num1=[];
        $list = Db::table('user_profile')
                ->field('sex,count(id) as num')
                ->group('sex')
                ->select();
        $count = Db::table('user_profile')->count();
        $array=['0'=>'男','1'=>'女','2'=>'保密'];
        foreach($list as $k=>$v){
            $sex1[$k]=$array[$v['sex']];
            $test=$v['num']/$count*100;
            $num1[$k]=round($test);
        }
        $sex=json_encode($sex1);
        $num=json_encode($num1);
        $this->assign('sex',$sex);
        $this->assign('num',$num);
        $this->assign('title','学生性别分布展示');
        $this->assign('smallt','学生性别');
        return $this->fetch();
    }
    public function majors(){
        $name1=[];
        $num1=[];
        $list = Db::table('student_school s')
            ->join('category c','s.majors=c.code')
            ->field('c.name,count(s.id) as num')
            ->group('s.majors')
            ->select();
        $count = Db::table('student_school')->count();
        foreach($list as $k=>$v){
            $name1[$k]=$v['name'];
            $test=$v['num']/$count*100;
            $num1[$k]=round($test);
        }
        $name=json_encode($name1);
        $num=json_encode($num1);
        $this->assign('name',$name);
        $this->assign('num',$num);
        $this->assign('title','学生专业分布展示');
        $this->assign('smallt','学生性别');
        return $this->fetch();
    }
    public function course(){
        $list = Db::table('study_result')
            ->group('courseid')
            ->where('status',0)
            ->count();
        $data=Db::table('study_result')
            ->group('courseid')
            ->count();
        $num=$test/$data*100;
        dump($data);die;
    }
}