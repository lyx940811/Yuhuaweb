<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/2/6
 * Time: 14:10
 */
namespace app\manage\controller;

use think\Db;

class Teacherstatistics extends  Base{
    public function index(){


        $title = input('get.title');

        $where = [];
        if(!empty($title)){
            $where['realname'] = ['like',"%$title%"];
            $where['sn'] = ['like',"%$title%"];
        }

        $list = Db::table('teacher_info')
            ->whereOr($where)
            ->paginate(20);

        $totalmedia = 0;
        $totalcheck = 0;
        $totalanswer = 0;
        $totalask = 0;

        $newarr = [];
        foreach ($list->items() as $k=>$v){
            $newarr[$k] = $v;
            $newarr[$k]['teachnum'] = Db::table('course')->where('teacherIds',$v['id'])->value('count(id) as num');
            $newarr[$k]['medianum'] = Db::table('course_task a')
                ->join('course b','a.courseId=b.id','LEFT')->where('b.teacherIds',$v['id'])
                ->value('count(a.id) as num');
            $totalmedia +=$newarr[$k]['medianum'];
            $newarr[$k]['checknum'] = Db::table('testpaper_result')->where('checkTeacherId',$v['id'])->value('count(id) as num');
            $totalcheck +=$newarr[$k]['checknum'];
            $newarr[$k]['answernum'] = Db::table('ask_answer')->where('answerUserId',$v['id'])->value('count(id) as num');
            $totalanswer += $newarr[$k]['answernum'];
            $newarr[$k]['asknum'] = Db::table('asklist')->where('userID',$v['id'])->value('count(id) as num');
            $totalask +=$newarr[$k]['asknum'];
        }


        $totalteacher = $list->total();


        $this->assign('list',$newarr);
        $this->assign('page',$list->render());
        $this->assign('typename','教师统计');
        $this->assign('totalteacher',$totalteacher);
        $this->assign('totalmedia',$totalmedia);
        $this->assign('totalcheck',$totalcheck);
        $this->assign('totalanswer',$totalanswer);
        $this->assign('totalask',$totalask);
        $this->assign('uid',session('admin_uid'));
        return $this->fetch();
    }
}