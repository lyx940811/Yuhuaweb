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
        $course = Course::where('userid',UID)->paginate(5);
        $this->assign('course',$course);
        $this->assign('page',$course->render());
        return $this->fetch();
    }
    public function teachroom(){
        return $this->fetch();
    }
    public function teacherask(){
        $asklist = Db::name('asklist')
            ->alias('a')
            ->join('course c','a.courseid=c.id')
            ->join('category ca','a.category_id=ca.code')
            ->where('c.userid',UID)
            ->field('a.*,c.userid,c.title as coursename,ca.name as catename')
            ->paginate(10);

        $this->assign('asklist',$asklist);
        $page = $asklist->render();
        $this->assign('page', $page);

        return $this->fetch();
    }
    public function browse(){
        if($this->request->isAjax()){
            $map = array();
            if(!empty($this->request->param('keyword'))){
                $keyword = "%".$this->request->param('keyword')."%";
                $map['filename']  = ['like',$keyword];
            }
            if(!empty($type = $this->request->param('type'))){
                switch ($type){
                    case 'all':
                        $map['id']  = ['>',1];
                        break;
                    case 'video':
                        $map['type']  = ['in',['mp4','rmvb','avi']];
                        break;
                    case 'flash':
                        $map['type']  = ['in',['swf','flv']];
                        break;
                    case 'audio':
                        $map['type']  = ['in',['mp3']];
                        break;
                    case 'image':
                        $map['type']  = ['in',['jpg','png','gif']];
                        break;
                    case 'document':
                        $map['type']  = ['in',['doc','txt','docx']];
                        break;
                    case 'ppt':
                        $map['type']  = ['in',['ppt','pptx']];
                        break;
                    case 'other':
                        $map['type'] = 'others';
                        break;
                }
            }
            $file = Db::name('course_file')
                ->where($map)
                ->order('createTime desc')
                ->paginate(8);
            $this->assign('file',$file);

            $page = $file->render();
            $this->assign('page', $page);
//            return $file;
            return $this->fetch('browseajax');
        }

//        $map['id']  = ['>',1];
//        if($this->request->param('keyword')){
//            $keyword = "%".$this->request->param('keyword')."%";
//            $map['filename']  = ['like',$keyword];
//        }

        $file = Db::name('course_file')
//            ->where($map)
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
        $file = CourseFile::where($map)
            ->order('createTime desc')
            ->paginate(5);
        $this->assign('file',$file);

        $page = $file->render();
        $this->assign('page', $page);

        $this->assign('courseid',$map['courseid']);
        return $this->fetch();
    }

    public function savecoursefile(){
        $data = $this->request->param();
        $data['filesize'] = filesize($data['filepath']);
        $data['filename'] = basename($data['filepath']);
        $data['type']     = explode('.',$data['filename']);
        $data['type']     = $data['type'][1];
        $data['createTime']     = date('Y-m-d H:i:s',time());
        if(CourseFile::create($data)){
            return 1;
        }
        else{
            return 0;
        }
    }

    public function deletefile(){
        $fileid = $this->request->param('fileid');
        $file = CourseFile::get($fileid);
        if(file_exists($file['filepath'])){
            unlink(iconv("utf-8","gb2312",$file['filepath']));
            CourseFile::destroy($fileid);
            return 1;
        }
        else{
            CourseFile::destroy($fileid);
            return 1;
        }
    }
}
