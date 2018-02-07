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

        $newarr = [];
        $totaltime = 0;
        foreach ($list->items() as $k=>$v){
            $newarr[$k] = $v;
            $newarr[$k]['teachnum'] = Db::table('course')->where('teacherIds',$v['id'])->value('count(id) as num');
            $newarr[$k]['medianum'] = Db::table('course_task a')
                ->join('course b','a.courseId=b.id','LEFT')->where('b.teacherIds',$v['id'])
                ->value('count(a.id) as num');
            $newarr[$k]['checknum'] = Db::table('testpaper_result')->where('checkTeacherId',$v['id'])->value('count(id) as num');
            $newarr[$k]['answernum'] = Db::table('ask_answer')->where('answerUserId',$v['id'])->value('count(id) as num');
            $newarr[$k]['asknum'] = Db::table('asklist')->where('userID',$v['id'])->value('count(id) as num');
            $newarr[$k]['totalLoginNum'] = Db::table('user_login_log')->where('userid',$v['id'])->field('count(id) as num,sum(loginAllTime) as alltime')->find();
            $newarr[$k]['totalLoginDay'] = Db::table('user_login_log')->where('userid',$v['id'])->field("DATE_FORMAT(FROM_UNIXTIME(LoginTime),'%Y%m%d') days,COUNT(id) as num")->group('days')->find();
        }


        $totalteacher = 0;
        $totalmedia = 0;
        $totalcheck = 0;
        $totalanswer = 0;
        $totalask = 0;

//        $newarr2 = [];
        $alllist = Db::table('teacher_info')->select();
        foreach ($alllist as $k=>$v){
            $totalteacher ++;
            $totalmedia += Db::table('course_task a')
                ->join('course b','a.courseId=b.id','LEFT')->where('b.teacherIds',$v['id'])
                ->value('count(a.id) as num');
            $totalcheck += Db::table('testpaper_result')->where('checkTeacherId',$v['id'])->value('count(id) as num');
            $totalanswer += Db::table('ask_answer')->where('answerUserId',$v['id'])->value('count(id) as num');
            $totalask += Db::table('asklist')->where('userID',$v['id'])->value('count(id) as num');

        }


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