<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Loader;
use think\Db;
use think\Exception;
use app\index\model\QuestionType;
use app\index\model\Testpaper as TestpaperModel;
use app\index\model\TestpaperItem;
use app\index\model\CourseFile;
use app\index\model\Course;
class Teacher extends User
{
    public function __construct()
    {
        parent::__construct();
        if($this->user->type!=2){
            $this->error('您没有该权限');
        }
    }

    public function teacherclass(){
        $course = Db::name('course')->where('userid',UID)->page(1,10)->select();
        $this->assign('course',$course);
        return $this->fetch();
    }
    public function teachroom(){
        return $this->fetch();
    }
    public function teacherask(){
        return $this->fetch();
    }
    public function browse(){
        $map['id']  = ['>',1];
        if($this->request->param('keyword')){
            $keyword = "%".$this->request->param('keyword')."%";
            $map['filename']  = ['like',$keyword];
        }
        $file = Db::name('course_file')
            ->where($map)
            ->order('createTime desc')
            ->paginate(8);
        $this->assign('file',$file);

        $page = $file->render();
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function classindex(){
        $courseid = $this->request->param('courseid');
        $course = Course::get($courseid);
        $this->assign('course',$course);
        return $this->fetch();
    }

    public function classfiles(){
        $map['courseid'] = $this->request->param('courseid');
        $file = Db::name('course_file')
            ->where($map)
            ->order('createTime desc')
            ->paginate(10);
        $this->assign('file',$file);

        $page = $file->render();
        $this->assign('page', $page);

        return $this->fetch();
    }
}
