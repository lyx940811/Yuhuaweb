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
        $where['sex']=array('neq','');
        $list = Db::table('user_profile')
                ->field('sex,count(id) as num')
                ->where($where)
                ->group('sex')
                ->select();
        $count = Db::table('user_profile')->count();
        $array=['0'=>'男','1'=>'女','2'=>'保密'];
        foreach($list as $k=>$v){
            if(is_numeric($v['sex'])){
                $sex1[$k]=$array[$v['sex']];
            }else{
                $sex1[$k]=$array[2];
            }

            $test=$v['num']/$count*100;
            $num1[$k]=round($test,1);
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
            $num1[$k]=round($test,1);
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
        $num=$list/$data*100;
//        dump($list);dump($data);dump($num);die;
        $test[0]=round($num,1);
        $num1=($data-$list)/$data*100;
        $test[1]=round($num1,1);
        $num2=json_encode($test);
        $this->assign('num',$num2);
        $this->assign('title','课程分布展示');
        return $this->fetch();

    }
    public function teacher(){
        $education1=[];
        $num1=[];
        $list = Db::table('teacher_level')
            ->field('count(education) as num,education')
            ->group('education')
            ->select();
        $count = Db::table('teacher_level')->count();
        $array=['0'=>'初中','1'=>'高中','2'=>'中专','3'=>'专科','4'=>'本科及以上'];
        foreach($list as $k=>$v){
            if(is_numeric($v['education'])){
                $education1[$k]=$array[$v['education']];
                $test=$v['num']/$count*100;
                $num1[$k]=round($test,1);
            }else{
                $education1[$k]='未知';
                $test=$v['num']/$count*100;
                $num1[$k]=round($test,1);
            }
        }
        $education=json_encode($education1);
        $num=json_encode($num1);
        $this->assign('name',$education);
        $this->assign('num',$num);
        $this->assign('title','教师学历分布展示');
        $this->assign('smallt','教师学历');
        return $this->fetch();
    }
    public function title(){
        $title1=[];
        $num1=[];
        $total=0;
        $list = Db::table('teacher_work')
            ->field('count(title) as num,title')
            ->group('title')
            ->select();

        $count = Db::table('teacher_info')->count();
        foreach($list as $k=>$v){
                $title1 [$k]=$v['title'];
                $total+=$v['num'];
                $test=$v['num']/$count*100;
                $num1[$k]=round($test,1);
        }
        $title1[]='未知';
        $test=($count-$total)/$count*100;
        $num1[]=round($test,1);
        $title=json_encode($title1);
        $num=json_encode($num1);
        $this->assign('name',$title);
        $this->assign('num',$num);
        $this->assign('title','教师职称分布展示');
        $this->assign('smallt','教师职称');
        return $this->fetch();
    }
}