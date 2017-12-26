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
use app\index\model\User;


/** 学生类
 * 功能：
 *【问答部分】
 *【评论部分】
 *【笔记部分】
 *【学习部分】
 * Class Student
 * @package app\index\controller
 */
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
     * 得到【我的提问】列表
     */
    public function getmyask(){
        $userid = 1;//$this->user->id;
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $askList = Db::name('asklist')->where('userID',$userid)->page($page,10)->select();
        if($askList){
            foreach ($askList as &$a){
                $user = User::get($a['userID']);
                $a['username'] = $user->name;
                $a['avatar']   = $user->title;
                $a['category'] = Db::name('category')->where('code',$a['category_id'])->value('name');
            }
        }
        return json_data(0,$this->codeMessage[0],$askList);
    }

    /**
     * 得到【我的回答】列表
     */
    public function getmyanswer(){
        $userid = 1;//$this->user->id;
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $askList = Db::name('ask_answer')->where('answerUserID',$userid)->page($page,10)->select();
    }

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
     * 【学习部分】
     */

    /**
     * 得到正在学习的课程
     */
    public function learncourse(){}
    /**
     * 得到已经学完的课程
     */
    public function donecourse(){}
}
