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

        $info = input('get.');

        $where = [];
        if(!empty($info['userid'])){

            $where['d.realname'] = ['like',"%{$info['realname']}%"];
        }

        $list = Db::table('study_result a')
            ->field('b.title,c.title ctit,d.realname')
            ->join('course b','a.courseid=b.id','LEFT')
            ->join('course_chapter c','a.chapterid=c.id','LEFT')
            ->join('user_profile d','a.userid=d.id','LEFT')
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