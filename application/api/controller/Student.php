<?php
namespace app\api\controller;

use app\index\model\Asklist;
use app\index\model\CourseReview;
use think\Loader;
use think\Db;
use think\Exception;
use app\index\model\User as UserModel;
use app\index\model\Course;
use app\index\model\CourseNote;
use app\index\model\CourseFavorite;
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
        $askList = Db::name('asklist')
            ->where('userID',$userid)
            ->order('addtime desc')
            ->field('id as askID,userID,addtime,title,content,category_id')
            ->page($page,10)
            ->select();
        if($askList){
            foreach ($askList as &$a){
                $user = UserModel::get($a['userID']);
                $a['username'] = $user->username;
                $a['avatar']   = $this->request->domain()."/".$user->title;
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
            ->order('aa.addtime desc')
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
        if(!empty($this->data['id'])){
            $id = $this->data['id'];
        }
        $data = [
            'title'         =>  $this->data['title'],
            'content'       =>  $this->data['content'],
            'userID'        =>  $this->user->id,
            'courseid'      =>  $this->data['courseid'],
            'category_id'   =>  $this->data['category_id'],
            'addtime'       =>  date('Y-m-d H:i:s',time()),
        ];

        $validate = Loader::validate('index/Asklist');
        if(!$validate->check($data)){
            return json_data(130,$validate->getError(),'');
        }
        if(isset($id)){
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
     * write/updaste course note
     */
    public function editnote(){
        $id  =  $this->data['id'];
        $data = [
            'userid'    =>  $this->user->id,
            'courseId'  =>  $this->data['courseId'],
            'content'   =>  $this->data['content'],
            'lessonid'  =>  $this->data['lessonid'],
            'createdTime'=> date('Y-m-d H:i:s',time())
        ];
        if(!\app\index\model\Course::get($data['courseId'])){
            return json_data(200,$this->codeMessage[200],'');
        }

        $validate = Loader::validate('index/CourseNote');
        if(!$validate->check($data)){
            return json_data(130,$validate->getError(),'');
        }
        //还没定用不用注释中的
//        if(!$note = CourseNote::get(['userid'=>$this->user->id,'courseId'=>$this->data['courseId'],'lessonid'=>$this->data['lessonid']])){
//            CourseNote::create($data);
//            return json_data(0,$this->codeMessage[0],'');
//        }
//        else{
//            $data['content'] = $note['content'].$data['content'];
//            CourseNote::update($data,['userid'=>$this->user->id,'courseId'=>$this->data['courseId'],'lessonid'=>$this->data['lessonid']]);
//            return json_data(0,$this->codeMessage[0],'');
//        }
        if(empty($id)){
            CourseNote::create($data);
        }
        else{
            $note = new CourseNote;
            $note->data($data)
                ->isUpdate(true)
                ->save(['id' => $id]);
        }

        return json_data(0,$this->codeMessage[0],'');
    }

    /**
     * get lesson note
     */
    public function getlessonnote(){
        $map['courseId'] = $this->data['courseId'];
        $map['userid']   = $this->user->id;
        $map['lessonid'] = $this->data['lessonid'];

        if($note = CourseNote::get($map)){
            $data = [
                'id'        =>  $note['id'],
                'content'   =>  $note['content'],
                'createdTime'=> $note['createdTime'],
            ];
            return json_data(0,$this->codeMessage[0],$data);
        }
        else{
            return json_data(230,$this->codeMessage[230],'');
        }
    }
    /**
     * get course note
     */
    public function getcoursenote(){
        $map['cn.userid'] = $this->user->id;
        $map['cn.courseId'] = $this->data['courseId'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $note = Db::name('course_note')
            ->alias('cn')
            ->where($map)
            ->order('cn.createdTime desc')
            ->field('cn.content,cn.id,cn.lessonid,cn.courseId,cn.createdTime')
            ->page($page,10)
            ->select();
        foreach ( $note as &$n ){
            $n['createdTime'] = date('Y.m.d',strtotime($n['createdTime']));
            $n['title']   = Db::name('course_task')->where(['courseId'=>$n['courseId'],'chapterid'=>$n['lessonid']])->value('title');
            $n['chapter'] = Db::name('course_chapter')->where(['courseid'=>$n['courseId'],'id'=>$n['lessonid']])->value('title');
        }
        return json_data(0,$this->codeMessage[0],$note);
    }
    /**
     * 【收藏部分】
     */
    /**
     * 判断是否收藏
     * @return array
     */
    public function is_collect(){
        $courseid = $this->data['courseid'];
        if(!CourseFavorite::get(['userid'=>$this->user->id,'courseid'=>$courseid])){
            return json_data(0,$this->codeMessage[0],['is_collect'=>0]);
        }
        return json_data(0,$this->codeMessage[0],['is_collect'=>1]);
    }
    /**
     * 收藏动作
     */
    public function collect(){
        $courseid = $this->data['courseid'];
        $data = [
            'courseid'  =>  $courseid,
            'userid'    =>  $this->user->id
        ];
        if(!Course::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        if(CourseFavorite::get($data)){
            return json_data(240,$this->codeMessage[240],'');
        }
        $data['createTime'] = date('Y-m-d H:i:s',time());
        CourseFavorite::create($data);
        return json_data(0,$this->codeMessage[0],'');
    }
    /**
     * 取消收藏
     */
    public function canclecollect(){
        $courseid = $this->data['courseid'];
        $data = [
            'courseid'  =>  $courseid,
            'userid'    =>  $this->user->id
        ];
        if(!Course::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        if(!CourseFavorite::get($data)){
            return json_data(250,$this->codeMessage[250],'');
        }
        CourseFavorite::destroy($data);
        return json_data(0,$this->codeMessage[0],'');
    }


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
