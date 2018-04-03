<?php
namespace app\index\controller;

use app\index\logic\Question;
use app\index\model\AskAnswer;
use app\index\model\Asklist;
use app\index\model\CourseNote;
use app\index\model\CourseReview;
use app\index\model\CourseTask;
use app\index\model\StudyResult;
use think\Exception;
use think\Loader;
use think\Config;
use think\cache\driver\Redis;
use app\index\model\Course as CourseModel;
use app\index\model\User as UserModel;
use app\index\model\CourseFile;
use think\Db;
use think\Validate;



class Course extends Home
{

    public $course;
    public function __construct()
    {
        parent::__construct();
        if(!$this->request->param('course')){
            $this->redirect('index/index/allcourse');
        }
        $courseid = $this->request->param('course');
        $this->course = \app\index\model\Course::get($courseid);
        $people = Db::name('study_result')->where('courseid',$courseid)->group('userid')->select();
        $this->course['learnNum'] = count($people);
        $this->assign('course',$this->course);

        //头部信息
        $courseinfo = $this->getcourseinfo($courseid);
        $this->assign('coursedata',$courseinfo);

        //右侧学生信息
        $student = $this->newstudent($courseid);
        $this->assign('student',$student);


    }

    protected function newstudent($courseid)
    {
        $student = Db::name('study_result')
            ->alias('sr')
            ->join('user u','u.id=sr.userid')
            ->where('sr.courseid',$courseid)
            ->field('u.title,u.id,sr.courseid,sr.chapterid,u.username,u.nickname')
            ->group('sr.userid')
            ->limit(10)
            ->select();
        if($student){
            foreach ( $student as &$s ){
                $s['task'] = Db::name('course_task')->where(['courseId'=>$s['courseid'],'chapterid'=>$s['chapterid']])->value('title');
            }
        }
        return $student;
    }

    protected function getcourseinfo($courseid)
    {
        //基底数据
        $doneNum  = 0;
        $plan     = 0;
        $learn_taskid = 0;
        $next_task = '还未有新课程';
        $next_task_type = 'none';
        $next_task_paper = 0;
        $is_evaluate = false;

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
            if($task){
                foreach ( $task as $t ){
                    if($study_result = Db::name('study_result_v13')->where('userid',$this->user->id)->where('taskid',$t)->find()){
                        if($study_result['ratio']==100){
                            $doneNum++;
                        }else{
                            $doneNum = $doneNum + round($study_result['ratio']/100,2);
                        }
                    }
                }
                $plan = round($doneNum/$taskNum,2)*100;
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
        else{
            $next_task = '请先登陆';
        }

        return $data = [
            'categoryId'=>  $course['categoryId'],
            'title'     =>  $course['title'],
            'plan'      =>  $plan,
            'has_done'  =>  intval($doneNum),
            'task_num'  =>  $taskNum,
            'next_task' =>  $next_task,
            'next_task_id'  =>  $learn_taskid,
            'next_task_type'=>  $next_task_type,
            'paperID'   =>  $next_task_paper,
            'is_evaluate'   =>  $is_evaluate,
        ];

    }

//    public function catalogue()
//    {
//        $video_type = ['mp4','url'];
//        $courseid = $this->course['id'];
//        $examination=0;
//        if(!$course = CourseModel::get($courseid)){
//            return json_data(200,$this->codeMessage[200],'');
//        }
//        $fields = 'ct.id as taskid,ct.courseid,ct.length,ct.type,ct.chapterid,ct.title,cc.title as chapter,cc.seq,ct.mediaSource,ct.status';
//        $lesson = Db::name('course_task')
//            ->alias('ct')
//            ->join('course_chapter cc','ct.chapterid = cc.id')
//            ->field($fields)
//            ->order('cc.seq')
//            ->where('ct.courseid',$courseid)
//            ->where('ct.status',1)
//            ->select();
//
//
//        if(!empty($this->user)){
//            foreach ($lesson as &$l){
//                if($course['type']!='url'){
//                    $l['mediaSource'] = $this->request->domain()."/".$l['mediaSource'];
//                }
//                if($watch_log = Db::name('study_result')->where(['userid'=>$this->user->id,'courseid'=>$l['courseid'],'chapterid'=>$l['chapterid']])->find()){
//                    if($watch_log['status']==1){
//                        $l['plan'] = '100';
//                    }
//                    else{
//                        //这个是还没学完的课程
//                        if(!in_array($l['type'],$video_type)){
//                            $l['plan'] = '100';
//                        }
//                        else{
//                            $length = explode(':',$l['length']);
//                            $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;
//
//                            $watch_time = strtotime($watch_log['endtime'])-strtotime($watch_log['starttime']);
//                            $l['plan'] = (round($watch_time/$couse_time,2)*100);
//                        }
//                    }
//                }
//                else{
//                    $l['plan'] = '0';
//                }
//                unset($l['seq']);
//            }
//            $examination=$this->isNoExamination($courseid);
//        }
//        //查看是否已考试
//
////        $this->assign('count',$examination);
//        $this->assign('task',$lesson);
//        return $this->fetch();
//    }


    public function catalogue()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);

        $courseid = $this->course['id'];
        !empty($this->request->param('page'))?$page = $this->request->param('page'):$page = 1;
        $this->assign('page',$page);
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

//        var_dump($chapter[0]);die;
        $this->assign('chapter',$chapter);
        return $this->fetch();
    }



//    public function catalogue(){
//        $data=[];
//        $type='';
//        $courseid = $this->course['id'];
//        $list=Db::name('course_chapter')
//            ->where('courseid',$courseid)
//            ->where('flag',1)
//            ->order('seq')
//            ->select();
//        $a=1;
//        foreach($list as $key=>$value){
//            $info=DB::name('course_task')
//                ->where('courseId',$value['courseid'])
//                ->where('chapterid',$value['id'])
//                ->where('status',1)
//                ->order('sort')
//                ->select();
//            $data[$key]['chaptername']=$value['title'];
//            if(!empty($this->user)) {
//                foreach ($info as $k => $v) {
//                    $data[$key]['section'][$k] = $v;
//                    if ($v['type'] == 'test' || $v['type'] == 'exam' || $v['type'] == 'plan') {
//                        $papercount = DB::name('testpaper_result')
//                            ->where('paperid', $v['paperid'])
//                            ->where('userid', $this->user->id)
//                            ->count();//查看用户是否已经考试
//                        if ($papercount > 0) {
//                            $data[$key]['section'][$k]['papertype'] = 1;
//                        } else {
//                            $data[$key]['section'][$k]['papertype'] = 2;
//                        }
//                    }
//                    $progress = DB::name('study_result_v13')->where('userid', $this->user->id)
//                        ->where('taskid', $v['id'])->find();
//                    $data[$key]['section'][$k]['see'] = 0;
//                    if ($a == 1) {
//                        if(!empty($progress)){
//                            $data[$key]['section'][$k]['see'] = 1;
//                        }else {
//                            $data[$key]['section'][$k]['see'] = 1;
//                            $type = $v['type'];
//                            $a = 2;
//                        }
//
//                    }
//
//                }
//            }else{
//                $data[$key]['section']=$info;
//            }
//
//        }
//        $this->assign('courseid',$courseid);
//        $this->assign('type',$type);
//        $this->assign('data',$data);
//
//        return $this->fetch();
//    }
    //查看是否已经考试
    public function isNoExamination($courseid){
        $list=Db::name('testpaper')->where('courseid',$courseid)->order('createTime desc')->find();
        $where['userid']=$this->user->id;
        $where['paperID']=$list['id'];
        $count=Db::name('testpaper_item_result')->where($where)->count();
        return $count;
    }
    public function discussion()
    {
        $asklist = Asklist::where('courseid',$this->course['id'])->order('addtime desc')->paginate(10);
        $page = $asklist->render();
        $this->assign('page',$page);
        $this->assign('asklist',$asklist);
        return $this->fetch();
    }
    public function evaluate()
    {
        if($this->user){
            $evaluateDetail = Db::name('course_evaluate')
                ->alias('ce')
                ->join('course c','ce.courseId=c.id')
                ->where(['ce.userid'=>$this->user->id,'ce.courseid'=>$this->course['id']])
                ->field('ce.*,c.title,c.teacherIds')
                ->find();
            if($evaluateDetail){
                $evaluateDetail['tag'] = json_decode($evaluateDetail['tag']);
                $evaluateDetail['selfTag'] = json_decode($evaluateDetail['selfTag']);
                $evaluateDetail['createTime'] = date('Y-m-d',$evaluateDetail['createTime']);
                $evaluateDetail['teacherName'] = Db::name('teacher_info')->where('userid',$evaluateDetail['teacherIds'])->value('realname');
//            $evaluateDetail['teacherAvator'] = $this->request->domain()."/".Db::name('user')->where('id',$evaluateDetail['teacherIds'])->value('title');
                unset($evaluateDetail['teacherIds'],$evaluateDetail['userid'],$evaluateDetail['id'],$evaluateDetail['courseId']);
                $this->assign('evaluate',$evaluateDetail);
            }
        }
        return $this->fetch();
    }
    public function note()
    {
        if($this->user){
            $note = $this->course->note()->where('userid',$this->user->id)->paginate(10);
            $this->assign('note',$note);
            $this->assign('page',$note->render());
        }
        return $this->fetch();
    }
    public function material()
    {
        $file = $this->course->file()->order('createTime desc')->paginate(10);
        $page = $file->render();
        $this->assign('file',$file);
        $this->assign('page',$page);

        return $this->fetch();
    }

    public function down()
    {
        $fileid = $this->request->param('fileid');
        $file = CourseFile::get($fileid);
        $file_path = ".\\".str_replace("/","\\",$file['filepath']);;
        $file_path = iconv("utf-8","gb2312",$file_path);

        if(file_exists($file_path)){
            $fp=fopen($file_path,"r");
            $file_size=filesize($file_path);
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
    public function summary()
    {
        return $this->fetch();
    }
    public function askdetail()
    {
        $askid = $this->request->param('askid');
        $ask = Asklist::get($askid);
        $this->assign('ask',$ask);
        $answer = $ask->answer()->order('addtime desc')->paginate(10);
        $this->assign('answer',$answer);
        $this->assign('page',$answer->render());

        $data['hit'] = $ask['hit']+1;
        Asklist::update($data,['id'=>$ask['id']]);
        return $this->fetch();
    }
    public function createask()
    {
        if($this->request->isAjax()){
            $data = $this->request->param();
            $course = CourseModel::get($data['course']);
            $data['category_id'] = $course->category->code;
            $data['courseid'] = $data['course'];
            $data['addtime'] = date('Y-m-d H:i:s',time());
            unset($data['course']);
            if(Asklist::create($data)){
                return 1;
            }
            else{
                return 0;
            }

        }
        return $this->fetch();
    }

    public function taskdetail()
    {
        $taskid = $this->request->param('taskid');
        $task = CourseTask::get($taskid);
        empty($this->request->param('type'))?$type="course":$type=$this->request->param('type');
        $this->assign('type',$type);
//        $media='';
        if($type != 'course' && $task['type']=='mp4'){
            $array=['pdf','doc','docx','ppt','pptx','xls','xlsx'];
            $courseorteach=explode('.',$task[$type]);
            if(in_array($courseorteach[1],$array)){
                $task['mediaSource']=$courseorteach[0].'.pdf';
            }
        }
//        $this->assign('media',$media);

        if($task['type']!='url'&&!in_array($task['type'],['pdf','doc','docx','ppt','pptx','xls','xlsx'])){
            $task['mediaSource'] = $this->request->domain()."/".$task['mediaSource'];
        }
        if(in_array($task['type'],['doc','docx','ppt','pptx','xls','xlsx'])){
            $mediaSource = explode('.',$task['mediaSource']);
            if(!empty($mediaSource)){
                $task['mediaSource'] = $this->request->domain()."/".$mediaSource[0].".pdf";
            }
        }

        $this->assign('task',$task);


        if($question = \app\index\model\Question::get($task['questionID'])){
            $question['metas'] = json_decode($question['metas'],true);
            $question['metas'] = $question['metas']['choices'];
            $question['answer'] = json_decode($question['answer'],true);
            $question['answer'] = $question['answer'][0];
            $this->assign('question',$question);
        }

        //课程目录
        $video_type = ['mp4','url'];
        $courseid = $this->course['id'];
        if(!$course = CourseModel::get($courseid)){
            return json_data(200,$this->codeMessage[200],'');
        }

        $lesson = Db::name('course_chapter')
            ->where('courseid',$courseid)
            ->where('flag',1)
            ->field('id as chapterid,title')
            ->order('seq asc')
            ->select();

        foreach ( $lesson as &$c ){
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

        if($this->user){
            //查询是否学完这节课
            if(Db::name('study_result_v13')->where(['taskid'=>$taskid,'userid'=>$this->user->id,'ratio'=>100])->find()){
                //是100，赋值
                $this->assign('is_done',true);
            }else{
                if(in_array($task['type'],['pdf','doc','docx','ppt','pptx','xls','xlsx'])){
                    $data['createTime'] = time();
                    $data['is_done'] = 1;
                    $data['ratio']  = 100;
                    $data['taskid'] = $taskid;
                    $data['userid'] = $this->user->id;

                    Db::name('study_result_v13')->insert($data);
                }
            }
        }
        $map1['userid']=$this->user->id;
        $map1['taskid']=$taskid;
        $map1['is_del']=0;
        $watchtime=0;
        if($time = Db::name('study_result_v13_log')->where($map1)->order('createTime desc')->find()){
            $watchtime=$time['watchTime'];
        }
        $this->assign('watchtime',$watchtime);
        $this->assign('tasklist',$lesson);
        $this->assign('domain',$this->request->domain());
        return $this->fetch();
    }

    public function downtaskfile()
    {
        $taskid = $this->request->param('taskid');
        $type = $this->request->param('type');
        $task = CourseTask::get($taskid);
        switch ($type){
            case 'teachingPlan':
                //教案
                $file_path = ".\\".str_replace("/","\\",$task['teachingplan']);
                break;
            case 'courseWare':
                //课件
                $file_path = ".\\".str_replace("/","\\",$task['courseware']);
                break;
            default:
                $file_path = ".\\".str_replace("/","\\",$task['mediaSource']);
        }

        $file_path_forname = $file_path;
        $file_path = iconv("utf-8","gb2312",$file_path);

        if(!file_exists($file_path)){
            $this->error('文件不存在');
        }
        if($this->user&&$taskid){
            $data = [
                'userid'    =>  $this->user->id,
                'taskid'  =>  $taskid,
            ];
            if(!Db::name('study_result_v13')->where(['userid'=>$this->user->id,'taskid'=>$taskid,])->find()){
                $data['createTime'] = time();
                $data['is_done'] = 1;
                $data['ratio'] = 100;
                Db::name('study_result_v13')->insert($data);
            }
        }

        $fp=fopen($file_path,"r");
        $file_size=filesize($file_path);

//        $filelastname = explode('.',$task['mediaSource']);
//        $filename = time().".".$filelastname[1];
        $filename = preg_replace('/^.+[\\\\\\/]/', '', $file_path_forname);

        //下载文件需要用到的头
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".$file_size);
        Header("Content-Disposition: attachment; filename=".iconv("utf-8","gb2312",$filename));
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

    public function savenote()
    {
        $data = $this->request->param();
        $data['courseId'] = $data['course'];
        $data['createdTime'] = date('Y-m-d H:i:s',time());
        unset($data['course']);
        if(CourseNote::create($data)){
            return 1;
        }
        else{
            return 0;
        }

    }

    public function saveanswer()
    {

        $data = $this->request->param();
        $data['addtime'] = date('Y-m-d H:i:s',time());
        unset($data['course']);
        if(AskAnswer::create($data)){
            return 1;
        }
        else{
            return 0;
        }
    }

    public function savereview()
    {
        $data = $this->request->param();
        $data['createdTime'] = date('Y-m-d H:i:s',time());
        $data['courseid'] = $data['course'];
        unset($data['course']);
        if(CourseReview::create($data)){
            return 1;
        }
        else{
            return 0;
        }
    }

    public function notice()
    {
        $notice = $this->course->notice()->where('status',2)->order('createdtime desc')->limit(3)->select();
        $this->assign('notice',$notice);
        return $this->fetch();
    }

}
