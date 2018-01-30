<?php
namespace app\api\controller;

use think\Loader;
use think\Db;
use app\index\model\User;
use app\index\model\Testpaper;
use app\index\model\Like;
use app\index\model\Course as CourseModel;
use think\Cache;
/**
 * Class Course 在教师角色下的我的教学-在教课程-课程管理中的一些功能
 * @package app\index\controller
 */
class Course extends Home
{
    protected $LogicCourse;
    protected $LogicUpload;
    protected $LogicReview;
    public function __construct()
    {
        parent::__construct();
        $this->LogicCourse = Loader::controller('Course','logic');
        $this->LogicUpload = Loader::controller('Upload','logic');
        $this->LogicReview = Loader::controller('Review','logic');
    }


    /**
     * 图片缩放测试
     */
    public function chpicsize(){
        $path = 'G:\wamp64\www\tp5yuhuaweb\public\uploads\2017\12\12\flowers-background-butterflies-beautiful-87452.jpeg';
        myImageResize(iconv("utf-8","gb2312",$path),400,400);
    }

    /**
     * 压缩测试
     */
    public function press(){
        $path = 'G:\wamp64\www\tp5yuhuaweb\public\uploads\2017\12\12\flowers-background-butterflies-beautiful-87452.jpeg';
        $path = 'G:\wamp64\www\tp5yuhuaweb\public\uploads\2017\12\12\3.jpg';
        compresspic($path);
    }

    /**
     * 获得某课程下的所有课程文件
     */
    public function getfilelist(){
        $courseid = 3;//$this->data['courseid'];
        $fileList = $this->LogicCourse->getCourseFile($courseid);
        //类型转换为中文?现在是英文
        var_dump($fileList);
    }


    /**
     * 获得某课程下的所有一级评论
     */
    public function getcoursecomments(){
        $courseid = $this->data['courseid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        if(!\app\index\model\Course::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $comment = Db::name('course_review')
            ->where('courseid',$courseid)
            ->where('parentid',0)
            ->field('id,userid,content,createdTime')
            ->order('createdTime desc')
            ->page($page,10)
            ->select();
        if($comment){
            foreach ($comment as &$c){
                $user = User::get($c['userid']);
                $c['username'] = $user->username;
                $c['avatar']   = $this->request->domain()."/".$user->title;
                $c['sonreviewNum']   = Db::name('course_review')->where('parentid',$c['id'])->count();
                $c['likeNum']   = Db::name('like')->where('type','comment')->where('articleid',$c['id'])->count();
                if(!empty($this->user)){
                    if(Like::get(['userid'=>$this->user->id,'type'=>'comment','articleid'=>$c['id']])){
                        $c['is_like'] = 1;
                    }
                    else{
                        $c['is_like'] = 0;
                    }
                }
            }
        }

        return json_data(0,$this->codeMessage[0],$comment);
    }

    /**
     * 获得某个评论的详细内容及这个评论的一级，二级评论
     * @return array
     */
    public function getcomdetail(){
        $commentid = $this->data['commentid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        if(!\app\index\model\CourseReview::get($commentid)){
            return json_data(600,$this->codeMessage[600],'');
        }

        $comment = Db::name('course_review')
            ->field('id,userid,content,createdTime')
            ->order('createdTime desc')
            ->find($commentid);

        $user = User::get($comment['userid']);
        $comment['username']       = $user->username;
        $comment['avatar']         = $this->request->domain()."/".$user->title;
        $comment['sonreviewNum']   = Db::name('course_review')->where('parentid',$comment['id'])->count();
        $comment['likeNum']        = Db::name('like')->where('type','comment')->where('articleid',$comment['id'])->count();
        if(!empty($this->user)){
            if(Like::get(['userid'=>$this->user->id,'type'=>'comments','articleid'=>$commentid])){
                $comment['is_like'] = 1;
            }
            else{
                $comment['is_like'] = 0;
            }
        }
        $son = Db::name('course_review')->where('parentid',$commentid)->field('id,userid,content,createdTime,touserId')->order('createdTime desc')->page($page,10)->select();
        if($son){
            foreach ($son as &$s){
                $s['username'] = Db::name('user')->where('id',$s['userid'])->value('username');
                $s['tousername'] = Db::name('user')->where('id',$s['touserId'])->value('username');
                $s['avatar'] = $this->request->domain()."/".Db::name('user')->where('id',$s['userid'])->value('title');
            }
        }
        $comment['son'] = $son;


        return json_data(0,$this->codeMessage[0],$comment);
    }

    /**
     * 获得某个课程下的所有问答
     */
    public function courseasklist(){
        $courseid = $this->data['courseid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        if(!CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $askList = Db::name('asklist')
            ->where('courseid',$courseid)
            ->order('addtime desc')
            ->page($page,10)
            ->select();
        foreach ($askList as &$a){
            $user = User::get($a['userID']);
            $a['username'] = $user->username;
            $a['avatar']   = $this->request->domain()."/".$user->title;
            $a['category'] = Db::name('category')->where('code',$a['category_id'])->value('name');
            unset($a['category_id'],$a['userID'],$a['courseid']);
            $a['answerNum'] = Db::name('ask_answer')->where('askID',$a['id'])->count();
            if(!empty($this->user)){
                if(Like::get(['userid'=>$this->user->id,'type'=>'ask','articleid'=>$a['id']])){
                    $a['is_like'] = 1;
                }
                else{
                    $a['is_like'] = 0;
                }
            }
        }
        return json_data(0,$this->codeMessage[0],$askList);
    }

    public function coursedetail(){
        $courseid = $this->data['courseid'];

        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }

        $user = User::get($course['userid']);

        if(!empty($course->teacherinfo->realname)){
            $teacher_realname = $course->teacherinfo->realname;
            $teacher_avatar = $course->teacherinfo->user->title;
        }
        else{
            $teacher_realname = '还未分配老师';
            $teacher_avatar = 'static/index/images/avatar.png';
        }

        $data = [
            'about'         =>  $course->about,
            'teacher_name'  =>  $teacher_realname,
            'avatar'        =>  $this->request->domain()."/".$teacher_avatar,
            'achivement'    =>  '教师成就'
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }
    /**
     * 获得课程目录(加了进度),abandoned use rebuild version
     */
/*    public function getcourselesson_abandoned(){
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $courseid = $this->data['courseid'];
        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $fields = 'ct.id as taskid,ct.courseid,ct.length,ct.chapterid,ct.title,cc.title as chapter,cc.seq';
        $lesson = Db::name('course_task')
            ->alias('ct')
            ->join('course_chapter cc','ct.chapterid = cc.id')
            ->field($fields)
            ->order('cc.seq')
            ->where('ct.courseid',$courseid)
            ->page($page,10)
            ->select();

        if(!empty($this->user)){
            foreach ($lesson as &$l){
                $length = explode(':',$l['length']);
                $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;
                if($watch_log = Db::name('study_result')->where(['userid'=>$this->user->id,'courseid'=>$l['courseid'],'chapterid'=>$l['chapterid']])->find()){
                    if($watch_log['status']==1){
                        $l['plan'] = '100';
                    }
                    else{
                        $watch_time = strtotime($watch_log['endtime'])-strtotime($watch_log['starttime']);
                        $l['plan'] = (round($watch_time/$couse_time,2)*100);
                    }
                }
                else{
                    $l['plan'] = '0';
                }
                unset($l['length'],$l['seq']);
            }
        }
        return json_data(0,$this->codeMessage[0],$lesson);
    }*/
    //rebuild version use this
    public function getcourselesson(){
        $video_type = ['mp4','url'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $courseid = $this->data['courseid'];
        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        $fields = 'ct.id as taskid,ct.courseid,ct.length,ct.type,ct.chapterid,ct.title,cc.title as chapter,cc.seq,ct.mediaSource,ct.status';
        $lesson = Db::name('course_task')
            ->alias('ct')
            ->join('course_chapter cc','ct.chapterid = cc.id')
            ->field($fields)
            ->order('cc.seq')
            ->where('ct.status',1)
            ->where('ct.courseid',$courseid)
            ->page($page,10)
            ->select();
        foreach ($lesson as &$l){
            if($l['type']!='url'){
                $l['mediaSource'] = $this->request->domain()."/".$l['mediaSource'];
            }
            $l['plan'] = '0';
            if(!empty($this->user)){


                if($watch_log = Db::name('study_result')->where(['userid'=>$this->user->id,'courseid'=>$l['courseid'],'chapterid'=>$l['chapterid']])->find()){
                    if($watch_log['status']==1){
                        $l['plan'] = '100';
                    }
                    else{
                        //这个是还没学完的课程
                        if(!in_array($l['type'],$video_type)){
                            $l['plan'] = '100';
                        }
                        else{
                            $length = explode(':',$l['length']);
                            $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;

                            $watch_time = strtotime($watch_log['endtime'])-strtotime($watch_log['starttime']);
                            $l['plan'] = (round($watch_time/$couse_time,2)*100);
                        }
                    }
                }
                else{
                    $l['plan'] = '0';
                }
                unset($l['length'],$l['seq']);
            }
        }
        return json_data(0,$this->codeMessage[0],$lesson);
    }

    //abandoned , use the rebuild version
/*    public function getcoursetop_abandoned(){
        $course_all_time = 0;
        $courseid = $this->data['courseid'];
        //为了拿顶部的title
        $course = Db::name('course')->field('title')->find($courseid);
        //课程下的所有任务，为了计算时间
        $task = Db::name('course_task')
            ->where('courseId',$courseid)
            ->field('id,courseId,chapterid,length,title')
            ->select();

        //计算这个课程的所有的task的总时间
        if($task){
            foreach ( $task as $t){
                $length = explode(':',$t['length']);
                $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;
                $course_all_time = $course_all_time+$couse_time;
            }
        }
        //为了计算任务比需要的分母
        $taskNum = count($task);

        empty($task)?$next_task='还未有新课程':$next_task = $task[0]['title'];
        empty($task)?$learn_taskid='0':$learn_taskid = $task[0]['id'];

        //假如登陆了
        if(!empty($this->user)){
            $learn_task = Db::name('study_result')
                ->where('courseid',$courseid)
                ->where('userid',$this->user->id)
                ->order('chapterid desc')
                ->find();
            //找到最后一条学习记录
            if($learn_task){
                if($learn_task['status']==0){
                    //还没学完,需要拿这一节课得名字
                    $next_task = Db::name('course_task')
                        ->where('courseid',$courseid)
                        ->where('chapterid',$learn_task['chapterid'])
                        ->find();
                    $learn_taskid = $next_task['id'];
                    $next_task    = $next_task['title'];
                }
                else{
                    //学完了，需要拿下一节课得名字
                    $next_task = Db::name('course_task')
                        ->where('courseid',$courseid)
                        ->where('chapterid','>',$learn_task['chapterid'])
                        ->find();

                    if(!empty($next_task)){
                        $learn_taskid = $next_task['id'];
                        $next_task = $next_task['title'];
                    }
                    else{
                        $next_task = '已学完';
                        $learn_taskid = 0;
                    }
                }
                //还需要算进度
                $learn_task = Db::name('study_result')
                    ->where('courseid',$courseid)
                    ->where('userid',$this->user->id)
                    ->order('chapterid desc')
                    ->select();
                $has_learn_time = 0;
                foreach ( $learn_task as $t){
                    if($t['status']==0){
                        $watch_time = strtotime($t['endtime'])-strtotime($t['starttime']);
                    }
                    else{
                        $length = Db::name('course_task')->where(['courseId'=>$t['courseid'],'chapterid'=>$t['chapterid']])->value('length');
                        $length = explode(':',$length);
                        $watch_time = $length[2]+$length[1]*60+$length[0]*3600;
                    }
                    $has_learn_time = $has_learn_time+$watch_time;
                }
                if($course_all_time!=0){
                    $plan = (round($has_learn_time/$course_all_time,2)*100);
                }

                //拿到完成的任务比（1/30）
                $has_done_task = Db::name('study_result')
                    ->where('courseid',$courseid)
                    ->where('userid',$this->user->id)
                    ->where('status',1)
                    ->select();
                $has_done = count($has_done_task).'/'.$taskNum;
            }
            else{
                //没找到学习记录
                $plan = '0';
                $has_done = '0/'.$taskNum;
            }
        }
        else{
            //没登陆
            $plan = '0';
            $has_done = '0/'.$taskNum;
        }
        $data = [
            'title'     =>  $course['title'],
            'plan'      =>  $plan,
            'has_done'  =>  $has_done,
            'next_task' =>  $next_task,
            'next_task_id'  =>  $learn_taskid,
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }*/

    //rebuild version,use this
    public function getcoursetop(){
        $video_type = ['mp4','url'];
        $courseid = $this->data['courseid'];
        //为了拿顶部的title
        $course = Db::name('course')->field('title,categoryId')->find($courseid);

        //课程下的所有任务，为了计算时间
        $task = Db::name('course_task')
            ->where('courseId',$courseid)
            ->field('id,courseId,chapterid,length,title,status')
            ->where('status',1)
            ->order('chapterid asc')
            ->select();

        //为了计算任务比需要的分母
        $taskNum = count($task);

        empty($task)?$next_task='还未有新课程':$next_task = $task[0]['title'];
        empty($task)?$learn_taskid='0':$learn_taskid = $task[0]['id'];

        //假如登陆了
        if(!empty($this->user)){
            $learn_task = Db::name('study_result')
                ->where('courseid',$courseid)
                ->where('userid',$this->user->id)
                ->order('chapterid desc')
                ->find();
            //找到最后一条学习记录
            if($learn_task){
                //找到学习记录了，所以在做百分比计算的时候除数绝对不可能是0
                if($learn_task['status']==0){
                    //还没学完,需要拿这一节课得名字
                    $next_task = Db::name('course_task')
                        ->where('courseid',$courseid)
                        ->where('chapterid',$learn_task['chapterid'])
                        ->find();
                    $learn_taskid = $next_task['id'];
                    $next_task    = $next_task['title'];
                }
                else{
                    //学完了，需要拿下一节课得名字
                    $next_task = Db::name('course_task')
                        ->where('courseid',$courseid)
                        ->where('status',1)
                        ->where('chapterid','>',$learn_task['chapterid'])
                        ->find();
                    //找到下一节课了，拿到并赋值
                    if(!empty($next_task)){
                        $learn_taskid = $next_task['id'];
                        $next_task = $next_task['title'];
                    }
                    else{
                        //没有找到下一节课的名字，代表是最后一节课了
                        $next_task = '已学完';
                        $learn_taskid = 0;
                    }
                }
                //还需要算进度
                //拿出所有学习记录
                $learn_task = Db::name('study_result')
                    ->where('courseid',$courseid)
                    ->where('userid',$this->user->id)
                    ->order('chapterid desc')
                    ->select();
                //先预定学完的课程为0，学完的课程+1
                $has_learn_time = 0;
                if($learn_task){
                    //循环学习记录，每一条对应该课程下的每个task的学习情况
                    foreach ( $learn_task as $t){
                        //如果没有学完的话
                        if($t['status']==0){
                            //先选出type，判断是否为视频
                            $task = Db::name('course_task')
                                ->where('courseid',$t['courseid'])
                                ->where('chapterid',$t['chapterid'])
                                ->field('type,length')
                                ->find();
                            //不是视频，直接完成数目+1
                            if(!in_array($task['type'],$video_type)){
                                $has_learn_time = $has_learn_time+1;
                            }
                            else{
                                //视频类型，计算看过的时间/该task的总时间，得到比例，取两位小数
                                $watch_time = strtotime($t['endtime'])-strtotime($t['starttime']);
                                $length = explode(':',$task['length']);
                                $task_all_time = $length[2]+$length[1]*60+$length[0]*3600;
                                $has_learn_time = $has_learn_time+round(($watch_time/$task_all_time),2);
                            }
                        }
                        else{
                            //如果学完了得话，直接完成课目+1
                            $has_learn_time = $has_learn_time+1;
                        }
                    }
                }
                //345
                $plan = (round($has_learn_time/$taskNum,2)*100);

                //拿到完成的任务比（1/30）
                $has_done_task = Db::name('study_result')
                    ->where('courseid',$courseid)
                    ->where('userid',$this->user->id)
                    ->where('status',1)
                    ->select();
                $has_done = count($has_done_task).'/'.$taskNum;
            }
            else{
                //没找到学习记录
                $plan = '0';
                $has_done = '0/'.$taskNum;
            }
        }
        else{
            //没登陆
            $plan = '0';
            $has_done = '0/'.$taskNum;
        }
        $data = [
            'categoryId'=>  $course['categoryId'],
            'title'     =>  $course['title'],
            'plan'      =>  $plan,
            'has_done'  =>  $has_done,
            'next_task' =>  $next_task,
            'next_task_id'  =>  $learn_taskid,
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }

    /**
     * 获得一节课得详细url和类型
     * @return array
     */
    public function getlessondetail()
    {
        $taskid = $this->data['taskid'];
        $course_key = ['id'=>"",'title'=>"",'courseid'=>"",'chapterid'=>"",'type'=>"",'mediaSource'=>"",'length'=>"",];
        $course = Db::name('course_task')->find($taskid);
        if(!$course){
            return json_data(200,$this->codeMessage[200],[]);
        }
        $course = array_intersect_key($course,$course_key);
        if($course['type']!='url'){
            $course['mediaSource'] = $this->request->domain()."/".$course['mediaSource'];
        }
        return json_data(0,$this->codeMessage[0],$course);
    }

    /**
     * 获得一节课的考试试卷
     */
    public function getpaper()
    {
//        $courseid = 35;
        $courseid = $this->data['courseid'];
        if(!CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }
        if(!Testpaper::get(['courseid'=>$courseid])){
            return json_data(400,$this->codeMessage[400],'');
        }
        $testpaper = Db::name('testpaper')->where('courseid',$courseid)->order('createTime desc')->find();
        $meta = json_decode($testpaper['metas']);


        $topicType = [];
        foreach ( (array)$meta->counts as $key=>$value){
            $question['type'] = $key;
            $question['num'] = $value;
            $topicType[] = $question;
        }

        $paper_question = array();

        foreach ( $topicType as &$t ){
            foreach ( (array)$meta->scores as $key=>$value ){
                if( $t['type']==$key ){
                    $t['score'] = $value;
                }
            }
            foreach ( (array)$meta->missScores as $key=>$value ){
                if( $t['type']==$key ){
                    $t['missScore'] = $value;
                }
            }
            $paper_question[$t['type']] = Db::name('testpaper_item')
                ->alias('ti')
                ->join('question q','q.id=ti.questionId')
                ->where('ti.questiontype',$t['type'])
                ->where('paperID',$testpaper['id'])
                ->field('q.id,q.type,q.stem,q.metas')
                ->select();
        }

        foreach ( $paper_question as &$pq ){
            foreach ( $pq as &$q ){
                $q['metas'] = (array)json_decode($q['metas']);
            }
        }
        $data = [
            'paperID'   =>  $testpaper['id'],
            'name'      =>  $testpaper['name'],
            'score'     =>  $testpaper['score'],
            'topType'   =>  $topicType
        ];
        $data = array_merge($data,$paper_question);

        return json_data(0,$this->codeMessage[0],$data);
    }

    public function pap(){
        dump(Cache::get('dopaper'));
    }

    /**
     * 交卷
     */
    public function handpaper()
    {
        $paperid = $this->data['paperID'];
//        $paperid = 81;
        $score = 0;
        if(!Testpaper::get($paperid)){
            return json_data(400,$this->codeMessage[400],'');
        }
        $dopaper = json_decode($this->data['dopaper']);
        $dopaper = (array)$dopaper;
//        $dopaper = [
//            64  =>  ["1"],
//            56  =>  ["0","1"],
//            57  =>  [1],
//        ];
        Cache::set('dopaper',$dopaper,3600);
//        var_dump($dopaper);die;
        $testpaper = Db::name('testpaper')->find($paperid);
        $meta = json_decode($testpaper['metas']);
        //$topicType是为了拿每种题型的分值及可能存在的漏选分值
        $topicType = [];
        foreach ( (array)$meta->counts as $key=>$value){
            $question['type'] = $key;
            $topicType[$key] = '';
        }

        foreach ( $topicType as $tkey=>$tvalue ){
            foreach ( (array)$meta->scores as $key=>$value ){
                if( $tkey==$key ){
                    $topicType[$tkey]['score'] = $value;
                }
            }
            foreach ( (array)$meta->missScores as $key=>$value ){
                if( $tkey==$key ){
                    $topicType[$tkey]['missScore'] = $value;
                }
            }
        }

        //拿到resultid
        $lastresultId = Db::name('testpaper_item_result')->order('resultId desc')->limit(1)->value('resultId');
        empty($lastresultId)?$lastresultId=1:$lastresultId = $lastresultId+1;

        foreach ( $dopaper as $key=>$value ){
            $question = Db::name('question')->find($key);
            $answer = json_decode($question['answer']);

            $resultData = [
                'paperID'       =>    $paperid,
                'userid'        =>    $this->user->id,
                'answer'        =>    json_encode($value),
                'questionId'    =>    $question['id'],
                'resultId'      =>    $lastresultId
            ];

            switch ( $question['type'] ){
                case 'single_choice':
                    if(empty(array_intersect($answer,$value))){
                        $resultData['score'] = 0;
                        $resultData['status'] = 3;
                        break;
                    }
                    $resultData['status'] = 1;
                    $resultData['score'] = $topicType['single_choice']['score'];
                    $score = $score+$topicType['single_choice']['score'];
                    break;
                case 'choice':
                    //没答题，直接break
                    if(empty($value)){
                        $resultData['status'] = 4;
                        $resultData['score'] = 0;
                        break;
                    }
                    //如果填写的答案有在标准答案外的答案的话算错，break
                    if(!empty(array_diff($value,$answer))){
                        $resultData['score'] = 0;
                        $resultData['status'] = 3;
                        break;
                    }
                    //剩下来的就是在答案内的了，取差集，拿个数
                    $diffNum = count(array_diff($answer,$value));
                    if($diffNum==0){
                        //没有差集，全部答对，得满分
                        $resultData['status'] = 1;
                        $resultData['score'] = $topicType['choice']['score'];
                        $score = $score+$topicType['choice']['score'];
                    } else{
                        //有差集，根据个数来减掉漏选分
                        $resultData['status'] = 2;
                        $resultData['score'] =$topicType['choice']['score']-$diffNum*$topicType['choice']['missScore'];
                        $score = ($score+$topicType['choice']['score'])-$diffNum*$topicType['choice']['missScore'];
                    }
                    break;
                case 'determine':
                    if(empty(array_intersect($answer,$value))){
                        $resultData['score'] = 0;
                        $resultData['status'] = 3;
                        break;
                    }
                    $resultData['status'] = 1;
                    $resultData['score'] = $topicType['determine']['score'];
                    $score = $score+$topicType['determine']['score'];
                    break;
            }
            Db::name('testpaper_item_result')->insert($resultData);
        }
        $data = [
            'score' =>  $score
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }

/*    public function handpaper_abandoned()
    {
        $paperid = 78;
//        $paperid = $this->data['paperID'];
        $score = 0;
        if(!Testpaper::get($paperid)){
            return json_data(400,$this->codeMessage[400],'');
        }
//        $dopaper = $this->data['dopaper'];

        $dopaper = [
            64  =>  [1],
            56  =>  [0,1],
//            57  =>  [1],
        ];

        $testpaper = Db::name('testpaper')->find($paperid);
        $meta = json_decode($testpaper['metas']);
        //$topicType是为了拿每种题型的分值及可能存在的漏选分值
        $topicType = [];
        foreach ( (array)$meta->counts as $key=>$value){
            $question['type'] = $key;
            $topicType[$key] = '';
        }

        foreach ( $topicType as $tkey=>$tvalue ){
            foreach ( (array)$meta->scores as $key=>$value ){
                if( $tkey==$key ){
                    $topicType[$tkey]['score'] = $value;
                }
            }
            foreach ( (array)$meta->missScores as $key=>$value ){
                if( $tkey==$key ){
                    $topicType[$tkey]['missScore'] = $value;
                }
            }
        }

        //拿到resultid
        $lastresultId = Db::name('testpaper_item_result')->order('resultId desc')->limit(1)->value('resultId');
        empty($lastresultId)?$lastresultId=1:$lastresultId = $lastresultId+1;

        Db::startTrans();
        try{
            foreach ( $dopaper as $key=>$value ){
                $question = Db::name('question')->find($key);
                $answer = json_decode($question['answer']);

                $resultData = [
                    'paperID'       =>    $paperid,
                    'userid'        =>    $this->user->id,
                    'answer'        =>    json_encode($value),
                    'questionId'    =>    $question['id'],
                    'resultId'      =>    $lastresultId
                ];

                switch ( $question['type'] ){
                    case 'single_choice':
                        if(empty(array_intersect($answer,$value))){
                            $resultData['score'] = 0;
                            $resultData['status'] = 3;
                            break;
                        }
                        $resultData['status'] = 1;
                        $resultData['score'] = $topicType['single_choice']['score'];
                        $score = $score+$topicType['single_choice']['score'];
                        break;
                    case 'choice':
                        //没答题，直接break
                        if(empty($value)){
                            $resultData['status'] = 4;
                            $resultData['score'] = 0;
                            break;
                        }
                        //如果填写的答案有在标准答案外的答案的话算错，break
                        if(!empty(array_diff($value,$answer))){
                            $resultData['score'] = 0;
                            $resultData['status'] = 3;
                            break;
                        }
                        //剩下来的就是在答案内的了，取差集，拿个数
                        $diffNum = count(array_diff($answer,$value));
                        if($diffNum==0){
                            //没有差集，全部答对，得满分
                            $resultData['status'] = 1;
                            $resultData['score'] = $topicType['choice']['score'];
                            $score = $score+$topicType['choice']['score'];
                        } else{
                            //有差集，根据个数来减掉漏选分
                            $resultData['status'] = 2;
                            $resultData['score'] =$topicType['choice']['score']-$diffNum*$topicType['choice']['missScore'];
                            $score = ($score+$topicType['choice']['score'])-$diffNum*$topicType['choice']['missScore'];
                        }
                        break;
                    case 'determine':
                        if(empty(array_intersect($answer,$value))){
                            $resultData['score'] = 0;
                            $resultData['status'] = 3;
                            break;
                        }
                        $resultData['status'] = 1;
                        $resultData['score'] = $topicType['determine']['score'];
                        $score = $score+$topicType['determine']['score'];
                        break;
                }
                Db::name('testpaper_item_result')->insert($resultData);
            }
            // 提交事务
            Db::commit();
            $data = [
                'score' =>  $score
            ];
            return json_data(0,$this->codeMessage[0],$data);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return json_data(2000,'error','');
        }

    }*/




    public function get_course_lesson_v13(){
        $courseid = 5;//$this->data['courseid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $chapter = Db::name('course_chapter')
            ->where('courseid',$courseid)
            ->where('flag',1)
            ->field('id as chapterid,title')
            ->page($page,10)
            ->select();

        foreach ( $chapter as &$c ){
            $c['task'] = Db::name('course_task')
                ->where('courseid',$courseid)
                ->where('chapterid',$c['chapterid'])
                ->where('status',1)
                ->field('id as taskid,title,type,length')
                ->order('sort asc')
                ->select();
            foreach ( $c['task'] as &$task){
                $task['plan'] = 0;
            }
        }
        var_dump($chapter[2]);die;
    }





}
