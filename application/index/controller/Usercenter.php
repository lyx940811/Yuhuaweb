<?php
namespace app\index\controller;

use app\index\model\CourseFavorite;
use app\index\model\StudyResult;
use app\index\model\UserProfile;
use think\Controller;
use think\Config;
use think\Loader;
use think\Db;
use app\index\model\Course;
use app\index\model\User as UserModel;
class Usercenter extends Home
{
    public $theuser;
    public function __construct()
    {
        parent::__construct();
        if(!$this->request->param('user')){
            $this->error('参数错误！');
        }
        $userid = $this->request->param('user');
        $this->theuser = \app\index\model\User::get($userid);
        $this->assign('theuser',$this->theuser);
    }


    public function space(){

        return $this->fetch();
    }
    /**
     * 我的证书
     * @return mixed
     */
    public function certificate(){
        return $this->fetch();
    }
    /**
     * @return mixed
     */
    public function conversion(){
        return $this->fetch();
    }
    /**关注/粉丝
     * @return mixed
     */
    public function fans(){
        return $this->fetch();
    }
    /**
     * 收藏课程
     * @return mixed
     */
    public function collect(){
        $course = CourseFavorite::where('userid',$this->user->id)->where('courseid!=0')
            ->paginate(8);
        $this->assign('course',$course);
        $this->assign('page',$course->render());
        return $this->fetch();
    }
    /**
     * 我的小组
     * @return mixed
     */
    public function group(){
        return $this->fetch();
    }
    /**
     * 我的学分
     * @return mixed
     */
    public function integral(){

        $title = input('get.title');

        $where = [];
        $where['a.userid'] = ['eq',$this->user->id];
        if(isset($title)){
            $where['c.title'] = ['like',"%$title%"];
        }

        $list = Db::table('get_point_log a')
            ->join('course_task b','a.taskid=b.id','LEFT')
            ->join('course c','b.courseId=c.id','LEFT')
            ->field('a.*,b.point as bpoint,c.title')
            ->where($where)->paginate(8);

        $major = Db::table('course a')
            ->join('student_school b','a.categoryId=b.majors','LEFT')
            ->field('a.id,a.title')->where('b.userid',$this->user->id)->select();

        $totalpoint = 0;
        foreach ($major as $k=>$v){
            $coursepoint = Db::table('course_task')->field('id,title,courseId,point')->where('courseId',$v['id'])->sum('point');
            $totalpoint = $totalpoint + $coursepoint;
        }

        $totalcredit = Db::table('get_point_log')->where('userid',$this->user->id)->sum('point');


        $this->assign('totalpoint',$totalpoint);
        $this->assign('totalcredit',$totalcredit);
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    /**
     * 课程表
     * @return mixed
     */
    public function timetable(){

        return $this->fetch();
    }
    /**
     * 在学班级
     * @return mixed
     */
    public function learning(){
        return $this->fetch();
    }
    /**
     * 在教课程
     * @return mixed
     */
    public function curriculum(){
        $course = Course::where('teacherIds',$this->theuser->id)->paginate(8);
        $this->assign('course',$course);

        $page = $course->render();
        $this->assign('page', $page);
        return $this->fetch();
    }

    /**
     * 在学课程
     * @return mixed
     */
    public function onstudy(){
//        $course = StudyResult::where('userid',$this->user->id)->where('courseid!=0')->group('courseid')->paginate(8);
        $course = Db::name('study_result_v13')
            ->alias('sr')
            ->join('course_task ct','sr.taskid=ct.id')
            ->where('sr.is_del',0)
            ->where('sr.userid',$this->user->id)
            ->group('ct.courseId')
            ->field('ct.courseId')
            ->paginate(8)
            ->each(function($item, $key){
                $thisCourse = Db::name('course')->find($item['courseId']);
                $item['smallPicture'] = $thisCourse['smallPicture'];
                $item['title'] = $thisCourse['title'];
                $learnNum = Db::name('study_result_v13')
                    ->alias('sr')
                    ->join('course_task ct','sr.taskid=ct.id')
                    ->where('ct.courseId',$item['courseId'])
                    ->group('sr.userid')
                    ->count();
                $item['learnNum'] = $learnNum;
                $item['commentsNum']   = Db::name('course_review')->where('courseid',$item['courseId'])->count();
                return $item;
            });

        $this->assign('course',$course);

        $page = $course->render();
        $this->assign('page', $page);
        return $this->fetch();
    }

}
