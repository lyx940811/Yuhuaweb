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
            ->field('id,sn,realname,userid')
            ->whereOr($where)
            ->paginate(20);
        $newarr=$this->getTeacherInfo($list);

        $totalteacher = 0;
        $totalmedia = 0;
        $totalcheck = 0;
        $totalanswer = 0;
        $totalask = 0;

//        $newarr2 = [];
        $alllist = Db::table('teacher_info')->select();
        foreach ($alllist as $k=>$v){
            $totalteacher ++;
            $totalcheck += Db::table('testpaper_result')->where('checkTeacherId',$v['userid'])->value('count(id) as num');
            $totalanswer += Db::table('ask_answer')->where('answerUserId',$v['userid'])->value('count(id) as num');
            $totalask += Db::table('asklist')->where('userID',$v['userid'])->value('count(id) as num');

        }
        $array=['test','exam','plan'];
        $totalmedia += Db::table('course_task')
            ->where('type','not in',$array)->count();
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
    public function getTeacherInfo($list)
    {
        $newarr = [];
        $totaltime = 0;
        foreach ($list as $k => $v) {
            $newarr[$k] = $v;
            //登录次数
            $newarr[$k]['loginnum'] = Db::table('user_login_log')->where('userid', $v['userid'])->count();
            //登录总时长
            $logintime = Db::table('user_login_log')->where('userid', $v['userid'])->sum('loginAllTime');
            $newarr[$k]['logintime'] = round($logintime / 60 / 60, 2);
            //登录频率
            $totalLoginDay = Db::table('user_login_log')->where('userid', $v['userid'])->field("DATE_FORMAT(FROM_UNIXTIME(LoginTime),'%Y%m%d') days,COUNT(id) as num")->group('days')->find();
            $newarr[$k]['totalLoginDay'] = 0;
            if ($totalLoginDay['num'] > 0 && $newarr[$k]['loginnum'] > 0) {
                $newarr[$k]['totalLoginDay'] = round($newarr[$k]['loginnum'] / $totalLoginDay['num'], 1);
            }
            //在教课程数量
            $newarr[$k]['teachnum'] = Db::table('course')->where('teacherIds', $v['userid'])->value('count(id) as num');
//            $newarr[$k]['medianum'] = Db::table('course_task a')
//                ->join('course b','a.courseId=b.id','LEFT')->where('b.teacherIds',$v['id'])
//                ->value('count(a.id) as num');
            //资源上传数量
            $array=['test',  'exam','plan'];
            $newarr[$k]['medianum'] = Db::table('course_task')->where('createdUserId', $v['userid'])->where('type','not in',$array)->count();
            $newarr[$k]['resource']='--';
            $newarr[$k]['checknum'] = Db::table('testpaper_result')->where('checkTeacherId', $v['userid'])->value('count(id) as num');
            $newarr[$k]['answernum'] = Db::table('ask_answer')->where('answerUserId', $v['userid'])->value('count(id) as num');
            $newarr[$k]['asknum'] = Db::table('asklist')->where('userID', $v['userid'])->value('count(id) as num');
            //在教学生数量
            $medianum = Db::table('course')->where('teacherIds', $v['userid'])->column('categoryId');
            $majorsid=[];
            foreach($medianum as $key=>$val){
                $info=explode(',',ltrim(rtrim($val,",")));
                foreach($info as $ks=>$vs){
                    $majorsid[]=$vs;
                }
            }
//            $majorsid = Db::name('course')->where('id', 'in', $medianum)->value('categoryId');
            $newarr[$k]['student'] = DB::name('student_school')->where('studentstatus',0)->where('majors', 'in', array_unique($majorsid))->count();
        }
        return $newarr;
    }

    //导出
    public function excel(){
        $list = Db::table('teacher_info')
            ->field('id,sn,realname')
            ->select();
        $info=$this->getTeacherInfo($list);
        $name='教师统计';
        $excelname="数据统计-教师统计";
        $title=[
            'sn'=>'工号',
            'realname'=>'教师姓名',
            'loginnum'=>'登录次数',
            'logintime'=>'登录总时长',
            'totalLoginDay'=>'登录频率',
            'studytime'=>'在教课程数量',
            'medianum'=>'上传资源数量',
            'resource'=>'资源访问量',
            'checknum'=>'作业批阅量',
            'answernum'=>'回帖数量',
            'asknum'=>'发帖数量',
            'student'=>'在教学生数量',
        ];

        $excel = new Excel();
        $info = $excel->excelExport($name,$title,$info,$excelname,2);
    }
}