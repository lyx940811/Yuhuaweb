<?php
/**
 * Created by PhpStorm.
 * User: M'S
 * Date: 2017/12/26
 * Time: 15:52
 */
namespace app\manage\controller;

use think\Db;
use think\query;

class Coursestatistics extends Base{

    public function index(){
        $search=input('get.');
        $search1=[
            'teacher'=>'',
            'name'=>'',
        ];
        $where=[];
        if(!empty($search['teacher'])){
            $search1['teacher']=$search['teacher'];
            $where['tf.id']=$search['teacher'];
        }
        if(!empty($search['name'])){
            $search1['name']=$search['name'];
            $where['c.title']=['like',"%{$search['name']}%"];
        }
        $where['status']=1;
        $data=DB::table('course c')
            ->join('teacher_info tf','c.teacherIds=tf.userid','LEFT')
            ->field('c.id,c.title,tf.realname,tf.sn')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);
        $title=$this->getTitle();//列表头部总和统计
        $info=$this->studentstatistics($data);
        $teacher=DB::table('teacher_info')->field('id,realname')->select();
        $this->assign('title',$title);
        $this->assign('teacher',$teacher);
        $this->assign('info',$info);
        $this->assign('search',$search1);
        $this->assign('page',$data->render());
        return $this->fetch();
    }

    //计算所有的是统计数据
    public function studentstatistics($data){
        $info=[];
        foreach($data as $key=>$value){
            $info[$key]=$value;
            $alltask=Db::table('course_task')->where('courseid',$value['id'])->column('id');
            $info[$key]['coursechapter']=count($alltask);//课程章节数量
            $coursetask=DB::table('course_task')
                  ->where('type',['=','mp4'],['=','flv'],'or')
                  ->where('courseid',$value['id'])->column('id');//视频资源数量

            $info[$key]['videonum']=count($coursetask);
            $array=['mp4','flv','test','exam','plan'];
            $info[$key]['filenum']=Db::table('course_task')
                  ->where('type','not in',$array)
                  ->where('courseid',$value['id'])->count();//文档资源数量
            $coursetime=DB::table('study_result_v13_log srl')//学习时长
                  ->where('taskid','in',$coursetask)
                  ->sum('watchTime');
            $info[$key]['coursetime']=round($coursetime/60/60,1);
            $postnum=Db::table('asklist')->where('courseid',$value['id'])->column('id');//所有发帖的id
            $info[$key]['postnum']=count($postnum);//发帖数量
            $info[$key]['replies']=Db::table('ask_answer')->where('askid','in',$postnum)->count();

            //学习进度
            $majorsid = Db::name('course')->where('id',$value['id'])->find();
            $allstudent=0;
            $courseporgress ='0%';
            $where=[];
            if($majorsid) {
                $where['majors'] =array('in',explode(',',ltrim(rtrim($majorsid['categoryId'],","))));
                if (!empty($majorsid['school_system'])) {
                    $aa = explode(',', $majorsid['school_system']);
                    $where['academic'] = array('in', $majorsid['school_system']);
                }
                $allstudent = Db::table('student_school')->where($where)->count();
                if ($allstudent) {
                    $lastchapter = DB::table('course_chapter')->where('courseid', $value['id'])->order('seq desc')->value('id');
                    if ($lastchapter) {
                        $lasttask = DB::table('course_task')->where('courseid', $value['id'])->where('chapterid', $lastchapter)->order('sort desc')->value('id');
                        $mystudy = Db::table('study_result_v13')->where('taskid', $lasttask)->where('ratio', 100)->count();
                        if ($mystudy > 0) {
                            $courseporgress = round($mystudy / $allstudent * 100, 2) . '%';
                        }
                    }
                }
            }
            $info[$key]['courseporgress']=$courseporgress;
            //作业数量
            $info[$key]['testpaper']=$info[$key]['coursechapter']-$info[$key]['filenum']-$info[$key]['videonum'];//所有任务减去文档和视频的就是作业（考试）

            //签到次数
            $info[$key]['checkin']=DB::table('checkin')->where('taskid','in',$alltask)->count();
            //学员数量
            $info[$key]['studentnum']=$allstudent;

        }
        return $info;
    }
    //课程表导出
    public function excel(){
        $where['status']=1;
        $data=DB::table('course c')
            ->join('teacher_info tf','c.teacherIds=tf.id')
            ->field('c.id,c.title,tf.realname,tf.sn')
            ->where($where)
            ->select();
        $name='课程统计';
        $excelname="数据统计-课程统计";
        $info=$this->studentstatistics($data);
        $title=[
            'coursenmae'=>'课程名称',
            'teacher'=>'任课教师',
            'coursechapter'=>'课程章节数量',
            'video'=>'视频资源数量',
            'file'=>'文档资源数量',
            'studytime'=>'课程学习时长',
            'postnum'=>'发帖数量',
            'replies'=>'回帖数量',
            'study'=>'学习进度',
            'papernum'=>'作业数量',
            'coursechike'=>'课程签到次数',
            'studentnum'=>'学员数量',
        ];

        $excel = new Excel();
        $info = $excel->excelExport($name,$title,$info,$excelname,1);
    }
    //列表头部总和统计
    public function getTitle(){
        $all=Db::table('course')->column('id');//所有课程的userid
        $data['allcourse']=count($all);//课程总数
        $data['alltask']=Db::table('course_task')->count();//章节总数
        //视频资源总数
        $data['allvedio']=Db::table('course_task')->where('type',['=','mp4'],['=','flv'],'or')->count();
        //文档资源总数
        $array=['mp4','flv','test','exam','plan'];
        $data['allfile']=Db::table('course_task') ->where('type','not in',$array)->count();
        //任课教师总数
        $data['teacherall']=Db::table('teacher_info')->count();
        //回帖总数
        $data['postnumall']=Db::table('asklist')->count();
        //发帖总数
        $data['repliesall']=Db::table('ask_answer')->count();
        return $data;
    }

    //=====================课程详情=================
    public function coursedetail(){
        $courseid=$this->request->param('courseid');
        $search=input('get.');
        $coursename=Db::table('course')->where('id',$courseid)->value('title');
        //查询所有学习这门课程的学生的userid，根据专业查询
        $categoryId = Db::name('course')->where('id',$courseid)->value('categoryId');//在正式环境上categorycourse没有数据，换了一种方式拿categoryid
        $categorysid=explode(',',ltrim(rtrim($categoryId,",")));
        $alluserid=DB::table('student_school')->where('majors','in',$categorysid)->column('userid');
        $search1=[
            'class'=>'',
            'name'=>'',
        ];
        $where=[];
        if(!empty($search['class'])){
            $search1['class']=$search['class'];
            $where['cr.id']=$search['class'];
        }
        if(!empty($search['name'])){
            $search1['name']=$search['name'];
            $where['cr.title']=['like',"%{$search['name']}%"];
        }
        $where['up.userid']=array('in',$alluserid);
        //查询学生信息
        $data=DB::table('user_profile up')
            ->join('student_school ss','up.userid=ss.userid')
            ->join('classroom cr','ss.class=cr.id','LEFT')
            ->field('up.userid,up.sn,up.realname,cr.title')
            ->where($where)
            ->order('up.sn')
            ->paginate(20,false,['query'=>request()->get()]);
        $title=$this->courseDetailTitle($courseid,$alluserid);
        $info=$this->getCourseDetail($data,$courseid);
        $class=Db::table('classroom')->field('id,title')->select();
        $this->assign('courseid',$courseid);
        $this->assign('coursename',$coursename);
        $this->assign('class',$class);
        $this->assign('title',$title);
        $this->assign('info',$info);
        $this->assign('search',$search1);
        $this->assign('page',$data->render());
        return $this->fetch();
    }
    //课程详情导出
    public function courseexcel(){
        $courseid=$this->request->param('courseid');
        $coursename=Db::table('course')->where('id',$courseid)->value('title');
        $categoryId = Db::name('course')->where('id',$courseid)->value('categoryId');//在正式环境上categorycourse没有数据，换了一种方式拿categoryid
        $categorysid=explode(',',ltrim(rtrim($categoryId,",")));
        $alluserid=DB::table('student_school')->where('majors','in',$categorysid)->column('userid');

        $where['up.userid']=array('in',$alluserid);
        $data=DB::table('user_profile up')
            ->join('student_school ss','up.userid=ss.userid')
            ->join('classroom cr','ss.class=cr.id','LEFT')
            ->field('up.userid,up.sn,up.userid,up.realname,cr.title')
            ->where($where)
            ->order('up.sn')
            ->select();
        $info=$this->getCourseDetail($data,$courseid);
        $name=$coursename;
        $excelname="数据统计-课程统计-课程详情";
        $title=[
            'sn'=>'学员',
            'realname'=>'学员姓名',
            'class'=>'班级',
            'coursenum'=>'课程登录次数',
            'studytime'=>'课程学习时长',
            'courseporgress'=>'课程学习进度',
            'coursestatus'=>'课程完成状态',
            'papernum'=>'完成考试次数',
            'paperavg'=>'考试平均分',
            'postnum'=>'发帖数量',
            'replies'=>'回帖数量',
            'checkin'=>'课程签到次数',
        ];

        $excel = new Excel();
        $info = $excel->excelExport($name,$title,$info,$excelname,2);


    }

    //课程详情详细数据查询
    public function getCourseDetail($data,$courseid){
        $info=[];
        foreach($data as $key=>$value){
            $info[$key]=$value;
            $taskid=DB::table('course_task')->where('courseid',$courseid)->column('id');
            //课程登录次数
            $info[$key]['coursenum']=DB::table('study_result_v13_log')->where('taskid','in',$taskid)->where('userid',$value['userid'])->count();
            //课程学习时长
            $studytime=DB::table('study_result_v13_log srl')
                ->where('userid',$value['userid'])
                ->where('taskid','in',$taskid)
                ->sum('watchTime');
            $info[$key]['studytime']=round($studytime/60/60,2);
            //课程学习进度
            $mystudynum=DB::table('study_result_v13')->where('taskid','in',$taskid)->where('userid',$value['userid'])->where('ratio',100)->count();
            $courseporgress='0%';
            if($mystudynum>0 && count($taskid)>0){
                $courseporgress=round($mystudynum/count($taskid)*100,2).'%';
            }
            //课程完成状态
            $coursestatus='学习中';
            $info[$key]['courseporgress']=$courseporgress;
            if(count($taskid)==$mystudynum){
                $coursestatus='已完成';
            }
            $info[$key]['coursestatus']=$coursestatus;
            //完成考试次数
            $paperid=DB::table('course_task')->where('courseid',$courseid)->column('paperid');
            $info[$key]['papernum']=DB::table('testpaper_result')->where('paperid','in',$paperid)->where('userid',$value['userid'])->count();
            //考试平均分
            $info[$key]['paperavg']=DB::table('testpaper_result')->where('paperid','in',$paperid)->where('userid',$value['userid'])->avg('score');
            //发帖数量
            $postnum=DB::table('asklist')->where('userID',$value['userid'])->where('courseid',$courseid)->column('id');
            $info[$key]['postnum']=count($postnum);
            //回帖数量
            $info[$key]['replies']=DB::table('ask_answer')->where('askid','in',$postnum)->where('answerUserID',$value['userid'])->count();
            //签到次数
            $info[$key]['checkin']=DB::table('checkin')->where('taskid','in',$taskid)->where('userid',$value['userid'])->count();
        }
        return $info;
    }

    //课程详情头部显示信息
    public function courseDetailTitle($courseid,$alluserid)
    {
        $data = [];
        //学员数量
        $data['usernum'] = count($alluserid);
        //学习总时长
        $studytime = DB::table('study_result_v13_log')
            ->where('taskid', 'in', function ($query) use ($courseid) {
                $query->table('course_task')->where('courseid', $courseid)->field('id');
            })->field('avg(watchTime) as avgtime,sum(watchTime) as sumtime')
            ->find();
        $data['studytime'] = round($studytime['sumtime'] / 60 / 60, 2);
        //学习平均时长
        $data['avgtime'] = round($studytime['avgtime']/60/60, 2);
        //学习完成比例
        $courseporgress = '0%';
        $lastchapter = DB::table('course_chapter')->where('courseid', $courseid)->order('seq desc')->value('id');
        if ($lastchapter) {
            $lasttask = DB::table('course_task')->where('courseid', $courseid)->where('chapterid', $lastchapter)->order('sort desc')->value('id');
            $mystudy = Db::table('study_result_v13')->where('userid','in',$alluserid)->where('taskid', $lasttask)->where('ratio', 100)->count();
            if ($mystudy > 0 && $data['usernum']>0) {
                $courseporgress = round($mystudy / $data['usernum'] * 100, 2) . '%';
            }
        }
        $data['courseporgress']=$courseporgress;
        //发帖数量
        $postnum=DB::table('asklist')->where('userID','in',$alluserid)->where('courseid',$courseid)->column('id');
        $data['postnum']=count($postnum);
        //回帖数量
        $data['replies']=DB::table('ask_answer')->where('askid','in',$postnum)->count();
        return $data;
    }

    //====================学习记录详情=================
    public function studydetail(){
        $userid=$this->request->param('userid');
        $courseid=$this->request->param('courseid');
        $data=DB::table('course_task ct')
            ->join('course_chapter cc','ct.chapterid=cc.id')
            ->field('ct.id,ct.type,ct.title,cc.title as cpttitle,ct.paperid')
            ->where('ct.courseid',$courseid)
            ->order('seq,sort')
            ->paginate(20,false,['query'=>request()->get()]);
        $title=$this->getStudyDetailTitle($courseid,$userid);
        $info=$this->getStudyDetail($data,$userid);
        $this->assign('page',$data->render());
        $this->assign('courseid',$courseid);
        $this->assign('userid',$userid);
        $this->assign('info',$info);
        $this->assign('title',$title);
        return $this->fetch();
    }

    public function studentexcel(){
        $courseid=$this->request->param('courseid');
        $userid=$this->request->param('userid');
        $username=DB::table('user_profile')->where('userid',$userid)->value('realname');
        $data=DB::table('course_task ct')
            ->join('course_chapter cc','ct.chapterid=cc.id')
            ->field('ct.id,ct.type,cc.title as cpttitle,ct.title,ct.paperid')
            ->where('ct.courseid',$courseid)
            ->order('seq,sort')
            ->select();
        $info=$this->getStudyDetail($data,$userid);
        $name=$username;
        $excelname="数据统计-课程统计-课程详情";
        $title=[
            'cpttitle'=>'章名称',
            'title'=>'节名称',
            'ctype'=>'节类型',
            'taskporgress'=>'章节学习进度',
            'sumtime'=>'章节学习时长',
            'avgtime'=>'章节平均学习时长',
            'notenum'=>'笔记数量',
            'papernum'=>'考试成绩',
            'postnum'=>'发帖数量',
            'replies'=>'回帖数量',
        ];

        $excel = new Excel();
        $info = $excel->excelExport($name,$title,$info,$excelname,2);
    }

    public function getStudyDetail($data,$userid){
        $info=[];
        foreach($data as $key=>$value){
            $info[$key]=$value;
            $array=['mp4'=>'视频','flv'=>'视频','test'=>'测验','exam'=>'考试','plan'=>'作业'];
            $info[$key]['ctype']='文档';
            if(!empty($array[$value['type']])){
                $info[$key]['ctype']=$array[$value['type']];
            }
            //章节进度
            $taskporgress=DB::table('study_result_v13')->where('taskid',$value['id'])->where('userid',$userid)->value('ratio');
            if($taskporgress){
                $info[$key]['taskporgress']=$taskporgress.'%';
            }else{
                $info[$key]['taskporgress']='0%';
            }
            //章节学习时长与平均时长
            $tasksumavg=DB::table('study_result_v13_log')->field('avg(watchTime) as avgtime,sum(watchTime) as sumtime')->where('taskid',$value['id'])->where('userid',$userid)->find();
            $info[$key]['sumtime']=round($tasksumavg['sumtime']/60/60,2);
            $info[$key]['avgtime']=round($tasksumavg['avgtime']/60/60,2);
            $info[$key]['notenums']='--';
            //考试成绩
            if($value['type']=='test' || $value['type']=='exam' || $value=='plan'){
                $info[$key]['paperscore']='--';
                if($value['paperid']){
                    $info[$key]['paperscore']=0;
                    $testpaper=DB::table('testpaper_result')->where('paperID',$value['paperid'])->where('userid',$userid)->find();
                    if(!empty($testpaper)){
                        $info[$key]['paperscore']=$testpaper['score'];
                    }
                }
            }else{
                $info[$key]['paperscore']='--';
            }
            //发帖
            $info[$key]['postnum']='--';
            $info[$key]['replies']='--';
        }
        return $info;
    }
    public function getStudyDetailTitle($courseid,$userid){
        //课程名称
        $data['course']=DB::table('course')->where('id',$courseid)->value('title');
        $data['username']=DB::table('user_profile')->where('userid',$userid)->value('realname');
        //章节学习时长
        $taskid=DB::table('course_task')->where('courseid',$courseid)->column('id');
        $studytime=DB::table('study_result_v13_log srl')
            ->field('avg(watchTime) as avgtime,sum(watchTime) as sumtime')
            ->where('userid',$userid)
            ->where('taskid','in',$taskid)
            ->find();
        $data['avgtime']=round($studytime['avgtime']/60/60,2);
        $data['studytime']=round($studytime['sumtime']/60/60,2);
        //课程学习进度
        $mystudynum=DB::table('study_result_v13')->where('taskid','in',$taskid)->where('userid',$userid)->where('ratio',100)->count();
        $courseporgress='0%';
        if($mystudynum>0 && count($taskid)>0){
            $courseporgress=round($mystudynum/count($taskid)*100,2).'%';
        }
        $data['courseporgress']=$courseporgress;
        //发帖数量
        $postnum=DB::table('asklist')->where('userID',$userid)->where('courseid',$courseid)->column('id');
        $data['postnum']=count($postnum);
        //回帖数量
        $data['replies']=DB::table('ask_answer')->where('askid','in',$postnum)->where('answerUserID',$userid)->count();

        return $data;
    }
}