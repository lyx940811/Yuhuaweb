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

        $userid=$this->request->param('id')+0;//在学生列表跳转到本列表是使用
        $info = input('get.');

        $where = [];
        if(!empty($info['userid'])){

            $where['d.realname'] = ['like',"%{$info['realname']}%"];
        }
        if($userid){
            $where['a.userid']=$userid;
        }
        $list = Db::table('study_result a')
            ->field('a.id,a.status,b.title,c.title ctit,d.realname')
            ->join('course b','a.courseid=b.id','LEFT')
            ->join('course_chapter c','a.chapterid=c.id','LEFT')
            ->join('user_profile d','a.userid=d.userid','LEFT')
            ->where($where)
            ->paginate(20,['query'=>$info]);

        $course = Db::table('course')->field('id,title')->select();

        $this->assign('list',$list);
        $this->assign('course',$course);
        $this->assign('typename','学习记录');
        $this->assign('page',$list->render());
        return $this->fetch();
    }
}