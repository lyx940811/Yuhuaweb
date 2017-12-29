<?php
namespace app\api\controller;

use app\index\model\Asklist;
use app\index\model\CourseReview;
use think\Loader;
use think\Db;
use think\Exception;
use app\index\model\User as UserModel;
use app\index\model\AskAnswer;

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
        $userid = $this->user->id;
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $askList = Db::name('asklist')->where('userID',$userid)->page($page,10)->select();
        if($askList){
            foreach ($askList as &$a){
                $user = UserModel::get($a['userID']);
                $a['username'] = $user->username;
                $a['avatar']   = $this->request->domain().DS.$user->title;
                $a['category'] = Db::name('category')->where('code',$a['category_id'])->value('name');
                $a['addtime'] = date('Y-m-d',strtotime($a['addtime']));
                unset($a['category_id'],$a['courseid']);
            }
        }
        return json_data(0,$this->codeMessage[0],$askList);
    }

    /**
     * 得到【我的回答】列表
     */
    public function getmyanswer(){
        $userid = 2;//$this->user->id;
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $field = 'aa.*,u.username as answerUsername,u.title as answerAvatar,al.content as askcontent,al.title,al.addtime as asktime,al.userID as askUserID,al.category_id';
        $answerList = Db::name('ask_answer')
            ->alias('aa')
            ->join('asklist al','aa.askID=al.id')
            ->join('user u','u.id=aa.answerUserID')
            ->field($field)
            ->where('answerUserID',$userid)
            ->page($page,10)
            ->select();

        foreach ( $answerList as &$a ){
            $askuser = UserModel::get($a['askUserID']);
            $a['askusername'] = $askuser->username;
            $a['askuseravatar'] = $this->request->domain().DS.$askuser->title;
            $a['answerAvatar'] = $this->request->domain().DS.$a['answerAvatar'];
            $a['askcategory'] = Db::name('category')->where('code',$a['category_id'])->value('name');
            $a['addtime'] = date('Y-m-d',strtotime($a['addtime']));
            $a['asktime'] = date('Y-m-d',strtotime($a['asktime']));
            $a['like'] = Db::name('like')->where('type','answer')->where('articleid',$a['id'])->count();
        }
        return json_data(0,$this->codeMessage[0],$answerList);

    }

    /**
     * 发起一个问答
     */
    public function editask(){
        $id = $this->data['id'];
        $data = [
            'title'         =>  $this->data['title'],
            'content'       =>  $this->data['content'],
            'userID'        =>  $this->user->id,
            'courseid'      =>  $this->data['courseid'],
            'category_id'   =>  $this->data['courseid'],
            'addtime'       =>  date('Y-m-d H:i:s',time()),
        ];

        $validate = Loader::validate('index/Asklist');
        if(!$validate->check($data)){
            return json_data(130,$validate->getError(),'');
        }
        if(!empty($id)){
            if(!Asklist::get($id)){
                return json_data(500,$this->codeMessage[500],'');
            }
            Asklist::where('id',$id)->update($data);
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            Asklist::create($data);
            return json_data(0,$this->codeMessage[0],'');
        }

    }

    /**
     * 对一个问答进行回复
     */
    public function answerask(){
        try{
            $data = [
                'askID'          =>  $this->data['askID'],
                'answerUserID'   =>  $this->user->id,
                'content'        =>  $this->data['content'],
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
     * 学员删除自己的问答
     */
    public function delask(){
        try{
            $id     = $this->data['askID'];
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
        try{
            $data = [
                'userid'        =>  $this->user->id,
                'touserId'      =>  $this->data['touserId'],
                'courseid'      =>  $this->data['courseid'],
                'content'       =>  $this->data['content'],
                'parentid'      =>  $this->data['parentid'],
                'createdTime'   => date('Y-m-d H:i:s'),
            ];
            $this->LogicReview->writeComment($data);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch ( Exception $e){
            return json_data($e->getCode(),$e->getMessage(),'');
        }

    }
    /**
     * 学员删除自己的评论
     */
    public function delcomment(){
        $id = $this->data['id'];
        if(!CourseReview::get(['id'=>$id,'userid'=>$this->user->id])){
            return json_data(600,$this->codeMessage[600],'');
        }
        CourseReview::destroy(['id'=>$id,'userid'=>$this->user->id]);
        return json_data(0,$this->codeMessage[0],'');
    }

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
