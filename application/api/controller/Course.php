<?php
namespace app\api\controller;

use app\index\model\CourseTask;
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
    public function chpicsize()
    {
        $path = 'G:\wamp64\www\tp5yuhuaweb\public\uploads\2017\12\12\flowers-background-butterflies-beautiful-87452.jpeg';
        myImageResize(iconv("utf-8","gb2312",$path),400,400);
    }

    /**
     * 压缩测试
     */
    public function press()
    {
        $path = 'G:\wamp64\www\tp5yuhuaweb\public\uploads\2017\12\12\flowers-background-butterflies-beautiful-87452.jpeg';
        $path = 'G:\wamp64\www\tp5yuhuaweb\public\uploads\2017\12\12\3.jpg';
        compresspic($path);
    }

    /**
     * 获得某课程下的所有课程文件
     */
    public function getfilelist()
    {
        $courseid = 3;//$this->data['courseid'];
        $fileList = $this->LogicCourse->getCourseFile($courseid);
        //类型转换为中文?现在是英文
        var_dump($fileList);
    }


    /**
     * 获得某课程下的所有一级评论
     */
    public function getcoursecomments()
    {
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
                $c['createdTime'] = date('Y.m.d',strtotime($c['createdTime']));
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
    public function getcomdetail()
    {
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
        $comment['createdTime']    = date('Y.m.d',strtotime($comment['createdTime']));
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
            $a['addtime'] = date('Y.m.d',strtotime($a['addtime']));
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
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $courseid = $this->data['courseid'];

        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }

        if($redis->exists('CourseDetail'.$courseid)){
            //有redis
            $data = $redis->get('CourseDetail'.$courseid);
            $data = json_decode($data,true);
        }else{
            //redis没存
            if(!empty($course->teacherinfo->realname)){
                $teacher_realname = $course->teacherinfo->realname;
                $teacher_avatar = $course->teacherinfo->user->title;
            }
            else{
                $teacher_realname = '还未分配老师';
                $teacher_avatar = 'static/index/images/avatar.png';
            }
            $learnNum = Db::name('study_result_v13')
                ->alias('sr')
                ->join('course_task ct','sr.taskid=ct.id')
                ->where('ct.courseId',$courseid)
                ->group('sr.userid')
                ->count();

            $data = [
                'title'         =>  $course->title,
                'about'         =>  $course->about,
                'category'         =>  $course->category->name,
                'teacher_name'  =>  $teacher_realname,
                'avatar'        =>  $this->request->domain()."/".$teacher_avatar,
                'achivement'    =>  '教师成就',
                'learnNum'      =>  $learnNum,
                'plan'          =>  $course->teachingplan
            ];
            $redis->setex('CourseDetail'.$courseid, 30,json_encode($data));
        }
        return json_data(0,$this->codeMessage[0],$data);
    }

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

    //rebuild version,use this
    public function getcoursetop()
    {
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
        $course = Db::name('course_task')
            ->field('id,title,courseid,chapterid,type,mediaSource,length,paperid,teachingplan as teachingPlan,courseware as courseWare,questionID')
            ->find($taskid);

        if(!$course){
            return json_data(200,$this->codeMessage[200],[]);
        }

        if($course['type']!='url'){
            $course['mediaSource'] = $this->request->domain()."/".$course['mediaSource'];
        }
        !empty($course['teachingPlan'])?$course['teachingPlan'] = $this->request->domain()."/".$course['teachingPlan']:$course['teachingPlan'];
        !empty($course['courseWare'])?$course['courseWare'] = $this->request->domain()."/".$course['courseWare']:$course['courseWare'];

        if(!empty($course['questionID'])){
            if($course['question'] = Db::name('question')->field('type,stem,analysis,answer,metas')->find($course['questionID'])){
                $course['question']['metas']  = json_decode($course['question']['metas'],true);
                $course['question']['metas']  = $course['question']['metas']['choices'];
                $course['question']['answer'] = json_decode($course['question']['answer'],true);
                $course['question']['answer'] = $course['question']['answer'][0];
            }
        }else{
            $course['question'] = (object)[];
        }

        unset($course['questionID']);

        return json_data(0,$this->codeMessage[0],$course);
    }

    /**
     * 获得一节课的考试试卷
     */
    public function getpaper()
    {
//        $courseid = 31;
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



    /**
     * 交卷
     */
    public function handpaper()
    {
        $paperid = $this->data['paperID'];
//        $paperid = 82;
        $score = 0;
        $not_now = false;
        if(!Testpaper::get($paperid)){
            return json_data(400,$this->codeMessage[400],'');
        }
        $dopaper = json_decode($this->data['dopaper']);
        $dopaper = (array)$dopaper;
//        $dopaper = [
//            64  =>  ["1"],
//            56  =>  ["0","1"],
//            57  =>  [1],
//            ];


//        Cache::set('dopaper',$dopaper,3600);

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
                default:
                    $not_now = true;
            }
            Db::name('testpaper_item_result')->insert($resultData);
        }
        //直接插入试卷结果总记录
        $paper_result_data = [
            'paperID'   =>  $paperid,
            'userid'    =>  $this->user->id,
            'score'     =>  $score,
            'status'    =>  3,
            'Flag'      =>  1,
            'subjectiveScore'   =>  $score,
            'endTime'       =>  date('Y-m-d H:i:s',time()),
            'checkedTime'   =>  date('Y-m-d H:i:s',time()),

        ];

        $data = [
            'score' =>  $score
        ];
        if($not_now){
            $paper_result_data['Flag'] = 0;
            if(Db::name('testpaper_result')->where(['paperID'=>$paperid,'userid'=>$this->user->id])->find()){
                Db::name('testpaper_result')->where(['paperID'=>$paperid,'userid'=>$this->user->id])->update($paper_result_data);
            }else{
                Db::name('testpaper_result')->insert($paper_result_data);
            }
            return json_data(0,$this->codeMessage[410],["score"=>410]);
        }else{
            $paper_result_data['Flag'] = 1;
            if(Db::name('testpaper_result')->where(['paperID'=>$paperid,'userid'=>$this->user->id])->find()){
                Db::name('testpaper_result')->where(['paperID'=>$paperid,'userid'=>$this->user->id])->update($paper_result_data);
            }else{
                Db::name('testpaper_result')->insert($paper_result_data);
            }
            return json_data(0,$this->codeMessage[0],$data);
        }

    }


    /**
     *      【v1.3 api】
     */

    //1.3版本的得到课程详情目录
    public function getcourselesson_v13(){
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);

        $courseid = $this->data['courseid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;

        if($this->user){
            //登陆了
            if($redis->exists('CourseLesson'.$courseid.'Page'.$page.'User'.$this->user->id)){
                //有redis
                $chapter = $redis->get('CourseLesson'.$courseid.'Page'.$page.'User'.$this->user->id);
                $chapter = json_decode($chapter,true);
            }else{
                //redis没存
                $chapter = Db::name('course_chapter')
                    ->where('courseid',$courseid)
                    ->where('flag',1)
                    ->field('id as chapterid,title')
                    ->order('seq asc')
                    ->page($page,10)
                    ->select();

                foreach ( $chapter as &$c ){
                    $c['task'] = Db::name('course_task')
                        ->where('courseId',$courseid)
                        ->where('chapterid',$c['chapterid'])
                        ->where('status',1)
                        ->field('id as taskid,title,type,paperid,mediaSource')
                        ->order('sort asc')
                        ->select();
                    $chapterTaskNum = count($c['task']);
                    $doneChapterNum = 0;
                    foreach ( $c['task'] as &$task ){
                        $thisTask = CourseTask::get($task['taskid']);
                        //赋值基底，没有登陆的时候进度为0
                        $task['plan'] = 0;
                        $task['is_test'] = false;
                        $task['score'] = 0;
                        $task['is_lock'] = true;
                        $task['is_incheck'] = false;
                        $task['plan_time'] = 0;
//                $task['is_lastUnit'] = false;
                        //如果不是考试或者测验的话进行拼域名
                        if(!in_array($task['type'],['text','exam','plan'])){
                            if($task['type']!='url'){
                                //如果不是外链的话，拼域名
                                $task['mediaSource'] = $this->request->domain()."/".$task['mediaSource'];
                            }
                        }
                        //判断是否为最后一小节
//                $lastUnit_condition = [
//                    'courseId'  =>  $thisTask['courseId'],
//                    'chapterid' =>  $thisTask['chapterid'],
//                    'sort'      =>  ['>',$thisTask['sort']],
//                    'status'    =>  1
//                ];
//                if(!Db::name('course_task')->where($lastUnit_condition)->find()){
//                    $task['is_lastUnit'] = true;
//                }
                        //当登陆时取记录
                        if(!empty($this->user)){
                            //判断这节课有没有上一节课
                            $laskTask_sql = 'select id from course_task where chapterid='.$thisTask['chapterid'].' and sort<'.$thisTask['sort'].'  ORDER BY sort desc limit 1';
                            if($laskTask = Db::query($laskTask_sql)){
                                //有上一节课
                                //判断上一节是否100，如果是的话，则解锁
                                $laskTaskLearn_sql = 'select * from study_result_v13 where taskid=(select id from course_task where chapterid='.$thisTask['chapterid'].' and sort<'.$thisTask['sort'].'  ORDER BY sort desc limit 1) and userid='.$this->user->id.' and ratio=100;';
                                if($laskTaskLearn = Db::query($laskTaskLearn_sql)){
                                    //找到了上一节并且为100
                                    $task['is_lock'] = false;
                                }
                            }else{
                                //没有上一节课，看看有没有上一章，找最后一节课
                                $lastChapterCourse_sql = 'select id from course_task where courseid='.$thisTask['courseId'].' and chapterid=(select id from course_chapter where courseid='.$thisTask['courseId'].' and seq<(select seq from course_chapter where id='.$thisTask['chapterid'].' ORDER BY seq desc limit 1) ORDER BY seq desc limit 1) order by sort desc limit 1';
                                if($lastChapterCourse = Db::query($lastChapterCourse_sql)){
                                    //找到了，看看他的进度是不是100
                                    if(Db::name('study_result_v13')->where(['taskid'=>$lastChapterCourse[0]['id'],'userid'=>$this->user->id,'ratio'=>100])->find()){
                                        //是100，解锁
                                        $task['is_lock'] = false;
                                    }
                                }
                                else{
                                    //没找到，说明是第一节课，解锁
                                    $task['is_lock'] = false;
                                }
                            }

                            //计算课程进度
                            $map = [
                                'userid'    =>  $this->user->id,
                                'taskid'    =>  $task['taskid'],
                                'is_del'    =>  0,
                            ];
                            if($user_studyResult = Db::name('study_result_v13')->where($map)->find()){
                                $task['plan_time'] = Db::name('study_result_v13_log')->where($map)->order('createTime desc')->value('watchTime');
                                $ratio = $user_studyResult['ratio'];
                                $task['plan'] = $ratio;
                                $doneChapterNum = $doneChapterNum+$ratio/100;
                            }else{
                                $task['plan'] = 0;
                            }
                            //如果是试卷的话，判断是否是做试卷了，如果做试卷了赋值分数
                            if(in_array($task['type'],['test','exam','plan'])){
                                //如果是测试
                                if($test_result = Db::name('testpaper_result')->where(['paperID'=>$task['paperid'],'userid'=>$this->user->id])->find()){
                                    $task['is_test'] = true;
                                    $task['score'] = $test_result['score'];
                                    if($test_result['Flag']==0){
                                        //==0时代表正在审核
                                        $task['is_incheck'] = true;
                                    }
                                }
                            }
                        }
                    }
                    if($doneChapterNum!=0){
                        $c['plan'] = round($doneChapterNum/$chapterTaskNum,2)*100;
                    }else{
                        $c['plan'] = 0;
                    }
                }
                $redis->setex('CourseLesson'.$courseid.'Page'.$page.'User'.$this->user->id, 30, json_encode($chapter));
            }
        }else{
            //没登陆
            if($redis->exists('CourseLesson'.$courseid.'Page'.$page)){
                //有redis
                $chapter = $redis->get('CourseLesson'.$courseid.'Page'.$page);
                $chapter = json_decode($chapter,true);
            }else{
                //redis没存
                $chapter = Db::name('course_chapter')
                    ->where('courseid',$courseid)
                    ->where('flag',1)
                    ->field('id as chapterid,title')
                    ->order('seq asc')
                    ->page($page,10)
                    ->select();

                foreach ( $chapter as &$c ){
                    $c['task'] = Db::name('course_task')
                        ->where('courseId',$courseid)
                        ->where('chapterid',$c['chapterid'])
                        ->where('status',1)
                        ->field('id as taskid,title,type,paperid,mediaSource')
                        ->order('sort asc')
                        ->select();
                    $chapterTaskNum = count($c['task']);
                    $doneChapterNum = 0;
                    foreach ( $c['task'] as &$task ){
                        //赋值基底，没有登陆的时候进度为0
                        $task['plan'] = 0;
                        $task['is_test'] = false;
                        $task['score'] = 0;
                        $task['is_lock'] = true;
                        $task['is_incheck'] = false;
                        $task['plan_time'] = 0;
                        //如果不是考试或者测验的话进行拼域名
                        if(!in_array($task['type'],['text','exam','plan'])){
                            if($task['type']!='url'){
                                //如果不是外链的话，拼域名
                                $task['mediaSource'] = $this->request->domain()."/".$task['mediaSource'];
                            }
                        }
                    }
                    if($doneChapterNum!=0){
                        $c['plan'] = round($doneChapterNum/$chapterTaskNum,2)*100;
                    }else{
                        $c['plan'] = 0;
                    }
                }
                $redis->setex('CourseLesson'.$courseid.'Page'.$page, 30,json_encode($chapter));
            }
        }
        return json_data(0,$this->codeMessage[0],$chapter);
    }


    //stupid structure always changing ，mf ceo assistant
    public function getcoursetop_v13()
    {
        //基底数据
        $doneNum  = 0;
        $plan     = 0;
        $learn_taskid = 0;
        $next_task = '还未有新课程';
        $next_task_type = 'none';
        $next_task_paper = 0;
        $is_evaluate = false;

        $courseid = $this->data['courseid'];
        $course = CourseModel::get($courseid);
//        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;

        //拿所有任务,把考试和测验算在内
        $map = [
            'courseId'  =>  $courseid,
            'status'    =>  1,
        ];
        $task = Db::name('course_task')
            ->where($map)
            ->column('id');

        $taskNum = count($task);

        if(!empty($this->user)){
            //对是否评价过进行定义
            if(Db::name('course_evaluate')->where(['courseId'=>$courseid,'userid'=>$this->user->id])->find()){
                $is_evaluate = true;
            }
            //拿完成的比例
            foreach ( $task as $t ){
                if($study_result = Db::name('study_result_v13')->where('userid',$this->user->id)->where('taskid',$t)->find()){
                    if($study_result['ratio']==100){
                        $doneNum++;
                    }else{
                        $doneNum = $doneNum + round($study_result['ratio']/100,2);
                    }
                }
            }
            if($doneNum!=0){
                $plan = round($doneNum/$taskNum,2)*100;
            }else{
                $plan = 0;
            }
            //拿下一任务名、任务id
            //拿看过的最高章节，最高小节的任务
            $sql = 'select id,title,courseId,type,chapterid,sort from course_task where courseId='.$courseid.' and chapterid=(select chapterid from study_result_v13 as sr left join ( select ct.*,cc.seq as chapterseq from course_task as ct LEFT JOIN course_chapter as cc on cc.id=ct.chapterid where ct.courseId='.$courseid.' and ct.status=1 ORDER BY cc.seq desc ) as ct on sr.taskid=ct.id where sr.userid='.$this->user->id.' ORDER BY chapterseq desc limit 1) and sort=(select sort from study_result_v13 as sr left join ( select ct.*,cc.seq as chapterseq from course_task as ct LEFT JOIN course_chapter as cc on cc.id=ct.chapterid where ct.courseId='.$courseid.' and ct.status=1 ORDER BY cc.seq desc ) as ct on sr.taskid=ct.id where sr.userid='.$this->user->id.' and chapterid=(select chapterid from study_result_v13 as sr left join ( select ct.*,cc.seq as chapterseq from course_task as ct LEFT JOIN course_chapter as cc on cc.id=ct.chapterid where ct.courseId='.$courseid.' and ct.status=1 ORDER BY cc.seq desc ) as ct on sr.taskid=ct.id where sr.userid='.$this->user->id.' ORDER BY chapterseq desc limit 1) ORDER BY sort desc limit 1) limit 1';
            $high_task = Db::query($sql);
            if($high_task){
                //找到了，判断这个学习记录是不是100，如果是的话，拿下一节（或下一章的下一节），不是的话，把本章传过去
                $high_task = $high_task[0];
                $condition = [
                    'userid'    =>$this->user->id,
                    'taskid'    =>$high_task['id']
                ];
                $watch_log = Db::name('study_result_v13')->where($condition)->find();

                if($watch_log['ratio']==100){
                    //拿下一节（如果没找到，拿下一章的下一节，如果还没找到，则为最后一节）
                    $planTask = CourseTask::get($watch_log['taskid']);
                    $where = [
                        'courseId'  =>  $courseid,
                        'chapterid' =>  $planTask['chapterid'],
                        'sort'      =>  ['>',$planTask['sort']]
                    ];
                    $find_next_task = Db::name('course_task')->where($where)->find();
                    if($find_next_task){
                        //找到了下一节
                        $learn_taskid = $find_next_task['id'];
                        $next_task    = $find_next_task['title'];
                        $next_task_type = $find_next_task['type'];
                        $next_task_paper = $find_next_task['paperid'];
                    }else{
                        //没找到，拿下一章的下一节
                        $next_chapter_task_sql = 'select * from course_task where courseId='.$courseid.' and chapterid=(select id from course_chapter where courseid='.$courseid.' and flag=1 and seq>(select seq from course_chapter where id='.$planTask['chapterid'].') limit 1) order by sort asc limit 1';
                        $next_chapter_task = Db::query($next_chapter_task_sql);
                        if($next_chapter_task){
                            //找到了
                            $next_chapter_task = $next_chapter_task[0];
                            $learn_taskid = $next_chapter_task['id'];
                            $next_task    = $next_chapter_task['title'];
                            $next_task_type = $next_chapter_task['type'];
                            $next_task_paper = $next_chapter_task['paperid'];
                        }
                    }
                }
                else{
                    //这一节还没看完，返回这一节的任务id
                    $learn_taskid = $watch_log['taskid'];
                    $thisCourse = CourseTask::get($learn_taskid);
                    $next_task = $thisCourse['title'];
                    $next_task_type = $thisCourse['type'];
                    $next_task_paper = $thisCourse['paperid'];
                }
            }else{
                //没找到，拿第一节课的内容
                $firse_task_sql = 'select * from course_task where courseId='.$courseid.' and chapterid=(select id from course_chapter where courseid='.$courseid.' order by seq limit 1) order by sort limit 1';
                $firse_task = Db::query($firse_task_sql);
                if($firse_task){
                    $firse_task = $firse_task[0];
                    $learn_taskid = $firse_task['id'];
                    $next_task    = $firse_task['title'];
                    $next_task_type = $firse_task['type'];
                    $next_task_paper = $firse_task['paperid'];
                }
            }
        }

        //notice
        if($this->user){
            $notice = Db::name('course_notice')
                ->where('courseid',$courseid)
                ->where('status',2)
                ->order('createdtime desc')
                ->limit(1)
                ->field('content,createdtime as endtime,title')
                ->select();
            if($notice){
                $notice = $notice[0];
                $notice['endtime'] = date('Y.m.d',strtotime($notice['endtime']));
            }else{
                $notice = null;
            }
        }else{
            $notice = null;
        }



        $data = [
            'categoryId'=>  $course['categoryId'],
            'title'     =>  $course['title'],
            'plan'      =>  $plan,
            'has_done'  =>  intval($doneNum),
            'taskNum'   =>  $taskNum,
            'next_task' =>  $next_task,
            'next_task_id'  =>  $learn_taskid,
            'next_task_type'=>  $next_task_type,
            'paperID'   =>  $next_task_paper,
            'is_evaluate'   =>  $is_evaluate,
            'notice'    =>  $notice
        ];
        return json_data(0,$this->codeMessage[0],$data);

    }

    public function getgrade()
    {
        $paperID = $this->data['paperID'];
        $paper_result = Db::name('testpaper_result')->where(['userid'=>$this->user->id,'paperID'=>$paperID,])->find();
        if($paper_result){
            if($paper_result['Flag']==1){
                return json_data(0,$this->codeMessage[0],['score'=>$paper_result['score']]);
            }else{
                return json_data(410,$this->codeMessage[410],[]);
            }
        }else{
            return json_data(420,$this->codeMessage[420],[]);
        }
    }



    /**
     * 通过试卷id获得试卷内容
     */
    public function getpaper_v13()
    {
        $paperid = $this->data['paperid'];

        $testpaper = Db::name('testpaper')->find($paperid);
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

    public function checkpaper()
    {
        $paperid = $this->data['paperid'];
        $testpaper = Db::name('testpaper')->find($paperid);
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
                ->field('q.id,q.type,q.stem,q.metas,q.answer,q.analysis')
                ->select();
        }

        foreach ( $paper_question as &$pq ){
            foreach ( $pq as &$q ){

                $userAnswerInfo = Db::name('testpaper_item_result')->where(['paperID'=>$paperid,'userid'=>$this->user->id,'questionId'=>$q['id']])->order('resultId desc')->find();
                $q['userAnswer'] = $userAnswerInfo['answer'];
                $q['teacherSay'] = $userAnswerInfo['teacherSay'];

                if(in_array($q['type'],['single_choice','choice','determine'])){
                    if($q['type']=='determine'){
                        $metas['choices'] = ["正确","错误"];
                    }else{
                        $metas = (array)json_decode($q['metas']);
                    }
                    $new_meta = array();
                    foreach ($metas['choices'] as $key=>$value){
                        $is_user_answer = false;
                        if(in_array($key,json_decode($q['userAnswer'],true))){
                            $is_user_answer = true;
                        }
                        if(in_array($key,json_decode($q['answer'],true))){
                            $is_right = true;
                        }else{
                            $is_right = false;
                        }
                        $new_meta[] = [
                            'stem'              =>  $value,
                            'is_right'          =>  $is_right,
                            'is_user_answer'    =>  $is_user_answer
                        ];
                    }
                    $q['metas'] = $new_meta;
                    $q['answer'] = implode(',',json_decode($q['answer'],true));
                    $q['userAnswer'] = implode(',',json_decode($q['userAnswer'],true));
                }else{
                    $q['metas'] = (array)json_decode($q['metas']);
                    $q['answer'] = implode('',json_decode($q['answer'],true));
                    $q['userAnswer'] = implode('',json_decode($q['userAnswer'],true));
                }

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







}
