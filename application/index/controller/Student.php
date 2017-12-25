<?php
namespace app\index\controller;

use app\index\model\Asklist;
use Couchbase\Document;
use think\Loader;
use think\Db;
use think\Exception;
use app\index\model\QuestionType;
use app\index\model\Testpaper as TestpaperModel;
use app\index\model\TestpaperItem;
use app\index\model\CourseFile;
use app\index\model\Course;
use app\index\model\Like;
class Student extends User
{
    public $LogicTestpaper;
    public $LogicQuestion;
    public $LogicUpload;
    public $LogicCourse;
    public $ControllerAsk;
    public function __construct(){
        parent::__construct();
        $this->LogicTestpaper  = Loader::controller('Testpaper','logic');
        $this->LogicQuestion   = Loader::controller('Question','logic');
        $this->LogicUpload     = Loader::controller('Upload','logic');
        $this->LogicCourse     = Loader::controller('Course','logic');
        $this->ControllerAsk   = Loader::controller('Ask','logic');
    }

    /**
     * 【问答部分】
     */

    /**
     * 发起一个问答
     */
    public function editask(){
        try{
            $data = [
                'id'        =>  $this->data['id'],
                'title'     =>  $this->data['title'],
                'content'   =>  $this->data['content'],
                'userID'    =>  $this->user->id,
                'courseid'  =>  $this->data['courseid'],
                'addtime'   =>  date('Y-m-d H:i:s',time()),
            ];
            $this->ControllerAsk->editask($data);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }

    }

    /**
     * 对一个问答进行回复
     */
    public function answerask(){
        try{
            $data = [
                'askID'          =>  2,//$this->data['askID'],
                'answerUserID'   =>  2,//$this->user->id,
                'content'        =>  '该改改',//$this->data['content'],
                'addtime'        =>  date('Y-m-d H:i:s',time()),
            ];
            $this->ControllerAsk->answerask($data);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }

    }

    /**
     * 学生删除自己的问答
     */
    public function delask(){
        try{
            $id     = $this->data['id'];
            $userID = $this->user->id;
            if(!Asklist::get(['id'=>$id,'userID'=>$userID])){
                return json_data(500,$this->codeMessage[500],'');
            }
            $this->ControllerAsk->delask($id);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }
    }


    /**
     * 【评论部分】
     */

    /**
     * 对一个课程/评论  进行评论
     */
    public function commentcourse(){
        $data = [
            'userid'        =>  1,
            'touserId'      =>  2,
            'courseid'      =>  5,
            'content'       =>'改改改',
            'parentid'      =>  1,
            'createdTime'   => date('Y-m-d H:i:s'),
        ];
        $this->LogicReview->writeComment($data);
    }
    /**
     * 删除一条评论
     */
    public function delcomment(){}

    /**
     * 【笔记部分】
     */





    /**
     * 【点赞部分】
     */
    /**
     * 给某个问答、回答、评论点赞
     */
    public function like(){
        $type = ['ask','answer','commment'];
        $data = [
            'userid'        =>  1,
            'type'          =>  'ask',
            'articleid'     =>  1,
            'createTime'    =>  date('Y-m-d H:i:s'),
        ];
        if(!in_array($data['type'],$type)){
            return json_data(180,$this->codeMessage[180],'');
        }
        Like::create($data);
    }

    /**
     * 取消点赞
     */
    public function canclelike(){
        $delete = Like::destroy([
            'userid'    =>  1,
            'type'      =>  'ask',
            'articleid'   =>1,
        ]);

        if($delete){
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            return json_data(180,$this->codeMessage[180],'');
        }
    }
}
