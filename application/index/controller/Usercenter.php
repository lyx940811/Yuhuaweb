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
     * 我的积分
     * @return mixed
     */
    public function integral(){
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
        $course = Db::name('study_result')->alias('sr')
            ->join('course c','sr.courseid=c.id')
            ->field('sr.courseid as id,c.title,c.smallPicture')
            ->group('sr.courseid')
            ->where('sr.userid',$this->user->id)
            ->paginate(8);

//        foreach ( $course as &$c ){
//            $people = Db::name('study_result')->where('courseid',$c['id'])->group('userid')->select();
//            $c['learnNum']      = count($people);
//            $c['commentsNum']   = Db::name('course_review')->where('courseid',$c['id'])->count();
//        }

        $course = StudyResult::where('userid',$this->user->id)->where('courseid!=0')->group('courseid')->paginate(8);
        $this->assign('course',$course);

        $page = $course->render();
        $this->assign('page', $page);
        return $this->fetch();
    }

}
