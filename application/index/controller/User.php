<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
use think\Db;
use app\index\model\User as UserModel;
use app\index\model\StudyResult;
use app\index\model\StudyResultLog;
use app\index\model\UserProfile;
use app\index\model\TeacherInfo;
class User extends Home
{
    public $LogicUser;
    public function __construct()
    {
        parent::__construct();
        if(!session('userid')){
            $this->error('您没有该权限');
        }
        $this->LogicUser     = Loader::controller('User','logic');
    }

    public function setting(){
        if($this->user->type==2){
            $profile = TeacherInfo::get(['userid'=>UID]);
        }elseif($this->user->type==3){
            $profile = UserProfile::get(['userid'=>UID]);
        }
        $this->assign('profile',$profile);
        return $this->fetch();
    }

    public function email(){
        return $this->fetch();
    }
    public function password(){
        return $this->fetch();
    }
    public function portrait(){
        return $this->fetch();
    }
    public function security(){
        return $this->fetch();
    }
    public function submit(){
        if($this->user->type==2){
            $profile = TeacherInfo::get(['userid'=>UID]);
        }elseif($this->user->type==3){
            $profile = UserProfile::get(['userid'=>UID]);
        }

        if(!empty($profile['cardpic'])){
            $pic = unserialize($profile['cardpic']);
            $this->assign('pic',$pic);
        }
//        $profile['idcard'] = substr_replace($profile['idcard'],'*****',-5);
        $this->assign('profile',$profile);
        return $this->fetch();
    }

    public function attention(){
        $data = $this->request->param();
        $profile = [
            'userid'    =>  $this->user->id,
            'realname'  =>  $data['truename'],
            'idcard'    =>  $data['idcard'],
        ];
        $file = $_FILES;
        $res = $this->LogicUser->userAttestation($file,$profile);

        if($res['code']==0){
            $this->redirect('index/user/submit');
        }
        else{
            $this->error($res['message']);
        }
    }

    /**
     * 修改头像
     */
    public function chheadicon(){
        $file = $_FILES;
        $res  = $this->LogicUser->upUserHeadImg($file,$this->user->id);
        return $res;
    }

    public function userlayout(){
        return $this->fetch();
    }

    /**
     * 个人设置页面的ajax
     * @return array
     */
    public function settingajax(){
        $data = $this->request->param();
        $user_key = ["nickname"=>""];
        $user_data = array_intersect_key($data,$user_key);
        $profile_data = array_diff_key($data,$user_key);
        $user = UserModel::update($user_data,['id'=>UID]);
        if($this->user->type==2){
            $profile = TeacherInfo::update($profile_data,['userid'=>UID]);
        }elseif($this->user->type==3){
            $profile = UserProfile::update($profile_data,['userid'=>UID]);
        }

        if($user&&$profile){
            return json_data(0,$this->codeMessage[0],'');
        }
    }

    /**
     * 修改密码ajax
     * @return array
     */
    public function chpwd(){
        $data = $this->request->param();
        $userid = UID;
        if($data['newpwd']!=$data['renewpwd']){
            return json_data(130,'两次输入密码不一致！','');
        }
        if( $user = UserModel::get($userid) ){
            if(password_verify($data['pwd'],$user->password)){
                $user->password = password_hash($data['newpwd'],PASSWORD_DEFAULT);
                $user->save();

                return json_data(0,$this->codeMessage[0],'');
            }
            else{
                return json_data(130,'原密码输入错误！','');
            }
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }
    }



    /**
     * 开始观看
     * @return array
     */
    public function startwatch(){
        $courseid  = $this->request->param('courseid');
        $chapterid = $this->request->param('chapterid');
        $taskid  = $this->request->param('taskid');
        $time = date('Y-m-d H:i:s');
        $data = [
            'userid'    =>  $this->user->id,
            'starttime' => $time,
            'endtime'   => $time,
            'courseid'=>$courseid,
            'chapterid'=>$chapterid
        ];
        if($watch = StudyResult::get(['userid'=>$this->user->id,'courseid'=>$courseid,'chapterid'=>$chapterid])){
            if($watch['status']!=1){
                $data1 = [
                    'starttime' => $time,
                    'endtime'   => $time
                ];
                StudyResult::update($data1,['id'=>$watch['id']]);
            }

        }
        else{
            $task = Db::name('course_task')->where(['courseId'=>$courseid,'chapterid'=>$chapterid])->find();
            $video_type = ['mp4','url'];

            if(!in_array($task['type'],$video_type)){
                $data['status'] = 1;
            }
            StudyResult::create($data);
        }
        $this->studyresultv13($taskid,0);//存入study-result-v13
        return json_data(0,$this->codeMessage[0],'');
    }

    /**
     * 结束观看
     */
    public function endwatch(){
        $courseid  = $this->request->param('courseid');
        $chapterid = $this->request->param('chapterid');
        $taskid  = $this->request->param('taskid');
        $res=Db::name('course_task')->where('id',$taskid)->find();
        $this->getendwatch($taskid);
        if($watch = StudyResult::get(['userid'=>$this->user->id,'courseid'=>$courseid,'chapterid'=>$chapterid])){
            $time = time();
            $course = Db::name('course_task')
                ->where('courseId',$courseid)
                ->where('chapterid',$chapterid)
                ->find();

            $length = explode(':',$course['length']);
            $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;

            $watch_time = $time-strtotime($watch['starttime']);

            $data = ['endtime' => date('Y-m-d H:i:s',$time)];
            if($watch_time>$couse_time){
                $data['status'] = 1;
                $pointData = [
                    'userid'        =>  $this->user->id,
                    'taskid'        =>  $course['id'],
                    'point'         =>  $course['point'],
                ];
                if(!Db::name('get_point_log')->where($pointData)->find()){
                    $pointData['createTime']    =  time();
                    Db::name('get_point_log')->insert($pointData);
                }
            }
            $data1['userid']=$watch['userid'];
            $data1['courseid']=$watch['courseid'];
            $data1['starttime']=$watch['starttime'];
            $data1['endtime']=$data['endtime'];
            $data1['status']=$data['status'];
            StudyResultLog::insert($data1);
            StudyResult::update($data,['id'=>$watch['id']]);
            return json_data( 0,$this->codeMessage[0],'');
        }
        else{
            return json_data(184,$this->codeMessage[184],'');
        }
    }

    /**
     * 判断用户是否签到
     */
    public function ischeckin()
    {
        $taskid = $this->request->param('taskid');

        $sql = "select *,from_unixtime(createTime)  from checkin WHERE userid=".$this->user->id." and taskid=".$taskid." and from_unixtime(createTime) BETWEEN '".date('Y-m-d',time())."' and '".date('Y-m-d',time())." 23:59:59'";
        $res = Db::query($sql);

        if ($res){
            //has checkin
            return json_data(185,$this->codeMessage[185],'');
        }
        else{
            //haven't checkin
            return json_data(186,$this->codeMessage[186],'');
        }
    }
    /**
     * 签到动作
     */
    public function checkin()
    {
        $data = [
            'userid'    =>  $this->user->id,
            'taskid'    =>  $this->request->param('taskid'),
            'createTime'=>  time(),
        ];
        if(Db::name('checkin')->insert($data)){
            return json_data(0,$this->codeMessage[0],'');
        } else{
            return json_data(187,$this->codeMessage[187],'');
        }
    }

    //课程如果是文档时，下载后修改数据
    public function filedown(){
        $courseid  = $this->request->param('courseid');
        $chapterid = $this->request->param('chapterid');
        $taskid  = $this->request->param('taskid');
        $time = time();
        $data = [
            'userid'    =>  $this->user->id,
            'starttime' => $time,
            'endtime'   => $time,
            'courseid'=>$courseid,
            'chapterid'=>$chapterid,
            'status'=>1,
        ];
        StudyResultLog::create($data);
        if($watch = StudyResult::get(['userid'=>$this->user->id,'courseid'=>$courseid,'chapterid'=>$chapterid])){
            if($watch['status']!=1){
                $data1 = [
                    'starttime' => $time,
                    'endtime'   => $time,
                    'status'=>1,
                ];
                StudyResult::update($data1,['id'=>$watch['id']]);
            }
//            return json_data(0,$this->codeMessage[0],'');
        }else{
            $task = Db::name('course_task')->where(['courseId'=>$courseid,'chapterid'=>$chapterid])->find();
            $video_type = ['mp4','url'];

            if(!in_array($task['type'],$video_type)){
                $data['status'] = 1;
            }
            StudyResult::create($data);
        }
        $this->studyresultv13($taskid,100);
        return json_data(0,$this->codeMessage[0],'');

    }

    public function studyresultv13($taskid,$ratio){
        $info=Db::name('study_result_v13')
                ->where('taskid',$taskid)
                ->where('userid',$this->user->id)
                ->find();
        $time = time();
        $data = [
            'userid'    =>  $this->user->id,
            'ratio' => $ratio,
            'createTime'=>$time,
            'taskid' => $taskid,
        ];
        if(!empty($info)){
            if($info['ratio']<100){
                $save1=DB::name('study_result_v13')
                    ->where('taskid',$taskid)
                    ->where('userid',$this->user->id)
                    ->update($data);
                if($ratio==100){
                    $save=DB::name('study_result_v13_log')->insert($data);
                }

            }

        }else{
            if($ratio==100) {
                $save = DB::name('study_result_v13_log')->insert($data);
            }
            $save1=DB::name('study_result_v13')->insert($data);
        }
        return 1;
    }

    //结束观看
    public function getendwatch($taskid){
        $list=Db::name('course_task')
            ->where('id',$taskid)
            ->find();
        if(!empty($list)){
            $info=Db::name('study_result_v13')
                ->where('taskid',$taskid)
                ->where('userid',$this->user->id)
                ->find();
            if(!empty($info)) {
                if($info['ratio']<100){
                    $time = time();
                    $length = explode(':', $list['length']);
                    $couse_time = $length[2] + $length[1] * 60 + $length[0] * 3600;

                    $watch_time = $time - $info['createTime'];
                    if ($watch_time >= $couse_time) {
                        $ratio = 100;
                    } else {
                        $ratio = round($watch_time / $couse_time * 100);
                    }
                    $data = [
                        'ratio' => $ratio,
                    ];
                    $save = DB::name('study_result_v13')
                        ->where('taskid', $taskid)
                        ->where('userid', $this->user->id)
                        ->update($data);
                    if (is_numeric($save)) {
                        $data= [
                            'userid' => $this->user->id,
                            'ratio' => $ratio,
                            'createTime' => $time,
                            'taskid' => $taskid,
                        ];
                        $save1 = DB::name('study_result_v13_log')->insert($data);
                    } else {
                        return json_data(0, $this->codeMessage[0], '');
                    }
                }

            }else{
                return json_data(0,$this->codeMessage[0],'');
            }

        }else{
            return json_data(0,$this->codeMessage[0],'');
        }
        return 1;
    }
}
