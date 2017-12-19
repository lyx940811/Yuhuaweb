<?php

namespace app\index\logic;

use app\index\model\Course as CourseModel;
use app\index\model\Question;
use app\index\model\Testpaper as TestpaperModel;
use app\index\model\User   as UserModel;
use app\index\model\CourseFile   as CourseFileModel;
use think\Loader;
use think\Db;
use think\Validate;
use think\Exception;
class Testpaper extends Base
{
    public function __construct()
    {
        parent::__construct();
    }


    public function createQuestion($data){
        $validate = Loader::validate('Question');
        if(!$validate->check($data)){
            throw new Exception($validate->getError(),130);
        }
        Question::create($data);
    }

    public function getQuestionDetail($id){
        $question = Question::get($id);
        if(!$question){
            throw new Exception('not find the question',300);
        }
        $key = ['createUserid','subCount','passedTimes','finishedTimes','createdUserId'];
        $question = $question->toArray();
        $question = array_diff_key($question,$key);
        return $question;
    }

    public function delQuestion($id){
        if(!is_array($id)){
            if(!Question::get($id)){
                throw new Exception('not find the question',300);
            }
        }
        else{
            if(!Question::all($id)){
                throw new Exception('not find the question',300);
            }
        }
        Question::destroy($id);
    }


    public function searchQuestion($type,$courseid,$keywords,$page){
        if(!empty($type)){
            $map['type'] = $type;
        }
        if(!empty($courseid)){
            $map['courseid'] = $courseid;
        }
        if(!empty($keywords)){
            $map['stem'] = ['like','%'.$keywords.'%'];
        }
        if(!$course = CourseModel::get($courseid)){
            throw  new Exception("course dont exist",200);
        }
        if(empty($page)){
            throw new Exception("page cannot be null",310);
        }

        $res = Db::name('question')
            ->where($map)
            ->limit(10)
            ->page($page)
            ->field('id,type,stem,createdTime,createUserid')
            ->select();
        if($res){
            foreach ($res as &$r){
                $r['typename'] = Db::name('question_type')->where('id',$r['type'])->value('name');
                $r['username'] = Db::name('user')->where('id',$r['createUserid'])->value('username');
                unset($r['type'],$r['createUserid']);
            }
        }
        return $res;
    }

    public function getQuestionList($courseid,$page){
        if(!$course = CourseModel::get($courseid)){
            throw  new Exception("course dont exist",200);
        }
        if(empty($page)){
            throw new Exception("page cannot be null",310);
        }
        $res = Db::name('question')
            ->where('courseId',$courseid)
            ->limit(10)
            ->page($page)
            ->field('id,type,stem,createdTime,createUserid')
            ->select();
        if($res){
            foreach ($res as &$r){
                $r['typename'] = Db::name('question_type')->where('id',$r['type'])->value('name');
                $r['username'] = Db::name('user')->where('id',$r['createUserid'])->value('username');
                unset($r['type'],$r['createUserid']);
            }
        }

        return $res;
    }



}
?>
