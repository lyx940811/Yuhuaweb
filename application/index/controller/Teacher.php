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
        $course = Course::where('teacherIds',UID)->paginate(5);
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
            ->where('c.teacherIds',UID)
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
            ->where('userid',$this->user->id)
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

        $this->assign('course',Course::get($map['courseid']));
        return $this->fetch();
    }

    public function savecoursefile(){
        $data = $this->request->param();

        $data['filesize'] = filesize(iconv("utf-8","gb2312",$data['filepath']));
        $data['filename'] = preg_replace('/^.+[\\\\\\/]/', '', $data['filepath']);
        $data['type']     = explode('.',$data['filename']);
        $data['type']     = $data['type'][1];
        $data['createTime']     = date('Y-m-d H:i:s',time());
        $data['userid'] = $this->user->id;
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

    public function down(){
        $fileid = $this->request->param('fileid');
        $file = CourseFile::get($fileid);
        if(file_exists(iconv("utf-8","gb2312",$file['filepath']))){
            $fp=fopen(iconv("utf-8","gb2312",$file['filepath']),"r");
            $file_size=filesize(iconv("utf-8","gb2312",$file['filepath']));
            //下载文件需要用到的头
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length:".$file_size);
            Header("Content-Disposition: attachment; filename=".iconv("utf-8","gb2312",$file['filename']));
            $buffer=1024;
            $file_count=0;
            //向浏览器返回数据
            while(!feof($fp) && $file_count<$file_size){
                $file_con=fread($fp,$buffer);
                $file_count+=$buffer;
                echo $file_con;
            }
            fclose($fp);
        }
        else{
            $this->error('文件不存在');
        }
    }
}
