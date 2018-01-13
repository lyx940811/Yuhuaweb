<?php
namespace app\index\controller;

use think\Controller;
use think\Config;
use think\Loader;
use think\Db;
use app\index\model\User as UserModel;
use app\index\model\StudyResult;
use app\index\model\UserProfile;
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
        $profile = UserProfile::get(['userid'=>UID]);

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
        $profile = $this->user->profile;
        if(!empty($profile['cardpic'])){
            $pic = unserialize($profile['cardpic']);
            $this->assign('pic',$pic);
        }
        $profile['idcard'] = substr_replace($profile['idcard'],'*****',-5);
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
        $profile = UserProfile::update($profile_data,['userid'=>UID]);
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
        $time = date('Y-m-d H:i:s',time());
        if($watch = StudyResult::get(['userid'=>$this->user->id,'courseid'=>$courseid,'chapterid'=>$chapterid])){
            if($watch['status']!=1){
                $data = [
                    'starttime' => $time,
                    'endtime'   => $time
                ];
                StudyResult::update($data,['id'=>$watch['id']]);
            }
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            $task = Db::name('course_task')->where(['courseId'=>$courseid,'chapterid'=>$chapterid])->find();
            $video_type = ['mp4','url'];
            $data = [
                'userid'    =>  $this->user->id,
                'starttime' => $time,
                'endtime'   => $time,
                'courseid'=>$courseid,
                'chapterid'=>$chapterid
            ];
            if(!in_array($task['type'],$video_type)){
                $data['status'] = 1;
            }
            StudyResult::create($data);
            return json_data(0,$this->codeMessage[0],'');
        }
    }

    /**
     * 结束观看
     */
    public function endwatch(){
        $courseid  = $this->request->param('courseid');
        $chapterid = $this->request->param('chapterid');
        if(!$res = Db::name('course_task')->where(['courseId'=>$courseid,'chapterid'=>$chapterid])->find()){
            return json_data(200,$this->codeMessage[200],'');
        }
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
            }
            StudyResult::update($data,['id'=>$watch['id']]);
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            return json_data(184,$this->codeMessage[184],'');
        }
    }



}
