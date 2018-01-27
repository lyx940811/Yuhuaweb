<?php
namespace app\api\controller;

use think\Controller;
use app\index\model\UserProfile;
use think\Loader;
use think\Config;
use app\index\model\UserProfile as UserProfileModel;
use app\index\model\User as UserModel;
use app\index\model\StudyResult;
use app\index\model\StudyResultLog;
use think\Db;
use think\Exception;
use think\Validate;
use app\index\model\Like;
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');
/**
 * Class User
 * @package app\index\controller
 * 用户资料（新建/修改）模块，对应网站首页-个人设置（或教师、学生某些重叠的功能）
 */
class User extends Controller
{
    protected $LogicUser;
    protected $LogicLogin;
    protected $user;
    protected $usertoken;
    protected $data;
    protected $codeMessage;
    protected $LogicLog;
    protected $LogicReview;
    public function __construct()
    {
        parent::__construct();

        //define ajax return message
        $this->codeMessage = Config::get('apicode_message');

        //controller from dir logic
        $this->LogicLog      = Loader::controller('Log','logic');
        $this->LogicUser     = Loader::controller('User','logic');
        $this->LogicLogin    = Loader::controller('Login','logic');
        $this->LogicReview   = Loader::controller('Review','logic');
        //unset the token which in the post data
        if($this->request->param()){
            $this->data = $this->request->param();
        }

        $user_token = $this->request->param('user_token');
        $this->verifyUserToken($user_token);
    }

    /**
     * 验证user，通过验证后赋值全局user信息
     * @param $user_token
     */
    protected function verifyUserToken($user_token){
        if(!$user_token){
            //没有token或token为空
            exit(json_encode(json_data(910,$this->codeMessage[910],[])));
        }

        if($user = UserModel::get(['user_token'=>$user_token])){
            //判断过期没
            if(time()>$user['expiretime']){
                //token过期
                exit(json_encode(json_data(910,$this->codeMessage[910],[])));
            }
            unset($this->data['user_token']);
            $this->user = $user;
        }
        else{
            //没有在数据库内找到对应token
            exit(json_encode(json_data(910,$this->codeMessage[910],[])));
        }
    }

    /**
     * 【用户相关的功能】
     */

    /**
     * 新增/修改资料
     */
    public function chprofile(){
        $this->data['userid'] = $this->user->id;
        $result = $this->LogicUser->chUserProfile($this->data);
        return $result;
    }

    /**
     * 修改头像
     */
    public function chheadicon(){
        $file = $_FILES;
        $res  = $this->LogicUser->upUserHeadImg($file,$this->user->id);
        return $res;
    }

    public function chheadiconbase(){
        $uploads_dir = "uploads/pictures"."/".date('Y',time())."/".date('m',time())."/".date('d',time());
        $date_dir    = ROOT_PATH."public"."/".$uploads_dir;
        if(!file_exists($date_dir)){
            mkdir($date_dir,0775,true);
        }
        $base64_img = $this->data['head_icon'];
        $type = 'jpg';

        if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
            $new_file = $uploads_dir."/".date('YmdHis_').'.'.$type;
            if(file_put_contents($new_file, base64_decode($base64_img))){
                $img_path = str_replace('../../..', '', $new_file);
                $user = \app\index\model\User::get($this->user->id);
                $user->title = $img_path;
                $user->save();
                return json_data(0,$this->codeMessage[0],$this->request->domain()."/".$img_path);
            }else{
                return json_data(720,'文件上传错误','');
            }
        }else{
            //文件类型错误
            return json_data(700,$this->codeMessage[700],'');
        }

    }
    /**
     * 实名认证
     */
    public function attestation(){
        $file = $_FILES;
        $data = $this->data;
        $data['userid'] = $this->user->id;
        if(!UserModel::get($data['userid'])){
            return json_data(110,$this->codeMessage[110],'');
        }
        $res = $this->LogicUser->userAttestation($file,$data);
        return $res;
    }

    /**
     * 修改密码
     */
    public function chpwd(){
        $data = $this->data;
        $userid = $this->user->id;
        if($data['newpwd']!=$data['renewpwd']){
            return json_data(130,'两次输入密码不一致！','');
        }
        if( $user = UserModel::get($userid) ){
            if(password_verify($data['pwd'],$user->password)){
                $user->password = password_hash($data['newpwd'],PASSWORD_DEFAULT);
                $user->save();
                //add log
                $this->LogicLog->createLog($userid,2,'update','更新密码',serialize($data),0);
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
     * 修改用户名
     */
/*    public function chusername(){
        $result = $this->LogicUser->chUsername($this->data);
        return $result;
    }*/

    /**
     * 获得个人设置内信息（type传入不同的选项来获得下方不同的内容）
     */
    public function getuserinfo(){
        $data = $this->data;
        $userid = $this->user->id;
        if(!UserModel::get($userid)){
            return json_data(110,$this->codeMessage[110],'');
        }
        switch ($data['type']){
            case 'base':
                $user_profile = $this->LogicUser->getUserInfo($userid);
                return json_data(0,$this->codeMessage[0],$user_profile);
                break;
            case 'avatar':
                $user_profile = $this->LogicUser->getUserAvatar($userid);
                return json_data(0,$this->codeMessage[0],$user_profile);
                break;
            case 'attestation':
                $user_profile = $this->LogicUser->getUserAttestation($userid);
                return json_data(0,$this->codeMessage[0],$user_profile);
                break;
            default:
                return json_data(1000,$this->codeMessage[1000],'');
                break;
        }
    }


    /**
     * 获得个人主页内容（type传入不同的选项来获得下方不同的内容）
     */
    public function getuserpage(){
        $data['type'] = 'following';
        $data['userid'] = 3;//登陆的uid
        $data['touserid'] = 2;//查看用户的uid

        $base_profile   =   $this->LogicUser->getBaseUserinfo($data);
        $contain        =   $this->LogicUser->getUserTypeInfo($data);
        $is_follow      =   $this->LogicUser->isFollow($data);

        $user_profile = [
            'base_profile'  =>  $base_profile,
            'is_follow'     =>  $is_follow,
            'contain'       =>  $contain
        ];

        var_dump($user_profile);
//        return $user_profile;

    }

    /**
     * 【关注功能】
     */

    /**
     * 关注某个用户
     */
    public function followuser(){
        $data['userid'] = 2;//登陆的uid
        $data['touserid'] = 3;//查看用户的uid

        $res = $this->LogicUser->followUser($data);

        return $res;
    }
    /**
     * 取消关注
     */
    public function disfollowuser(){
        $data['userid'] = 2;//登陆的uid
        $data['touserid'] = 3;//查看用户的uid

        $res = $this->LogicUser->disFollowUser($data);

        return $res;
    }

    public function coursereview(){

        $res = $this->LogicReview->review(1);
        return $res;
    }

    /**
     * 【部分APP功能】
     */
    /**
     * APP-得到个人信息(新版，增加了专业属性)
     */
    public function getmyinfo_new(){
        if(!empty($this->user->stuclass->classname->title)){
            $class = $this->user->stuclass->classname->title;
        }
        else{
            $class = '还未分配班级';
        }
        !empty($this->user->stuclass->major->name)?$major=$this->user->stuclass->major->name:$major='还未分配专业';
        $data = [
            'username'  =>  $this->user->username,
            'avatar'    =>  $this->request->domain()."/".$this->user->title,
            'mobile'    =>  $this->user->mobile,
            'classname' =>  $class,
            'major'  =>  $major
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }

    /**
     * APP-得到个人信息（旧版）
     */
    public function getmyinfo(){
        if(!empty($this->user->stuclass->classname->title)){
            $class = $this->user->stuclass->classname->title;
        }
        else{
            $class = '还未分配班级';
        }
        $data = [
            'username'  =>  $this->user->username,
            'avatar'    =>  $this->request->domain()."/".$this->user->title,
            'mobile'    =>  $this->user->mobile,
            'classname' =>  $class,
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }

    /**
     * app内得到个人设置的内容
     */
    public function getuserprofile(){
        $userprofile = UserProfileModel::get(['userid'=>$this->user->id]);
        $data = [
            'realname'      =>  $userprofile->realname,
            'mobile'        =>  $this->user->mobile,
            'email'         =>  $this->user->email,
            'education'     =>  $userprofile->education,
            'school'        =>  $userprofile->school,
            'address'       =>  $userprofile->address,
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }

    /**
     * app内更新个人资料
     */
    public function saveprofile(){
//        $user_data = [
//            'mobile'    =>  $this->data['mobile'],
//            'email'     =>  $this->data['email']
//        ];
        $user_key = ['mobile'=>'','email'=>''];
        $user_data = array_intersect_key($this->data,$user_key);

        $userprofile_key = ['realname'=>'','education'=>'','school'=>'','address'=>''];
        $userprofile_data = array_intersect_key($this->data,$userprofile_key);
//        $userprofile_data = [
//            'realname'  =>  $this->data['mobile'],
//            'education' =>  $this->data['education'],
//            'school'    =>  $this->data['school'],
//            'address'   =>  $this->data['address'],
//        ];

        Db::startTrans();
        try{
            Db::table('user')->where('id',$this->user->id)->update($user_data);
            Db::table('user_profile')->where('userid',$this->user->id)->update($userprofile_data);
            // 提交事务
            Db::commit();
            return json_data(0,$this->codeMessage[0],'');
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return json_data($e->getCode(),$e->getMessage(),'');
        }
    }



    /**
     * 【点赞部分】
     */
    /**
     * 给某个问答、回答、评论点赞
     */
    public function like(){
        $type = ['ask','answer','comment'];
        $data = [
            'userid'        =>  $this->user->id,
            'type'          =>  $this->data['type'],
            'articleid'     =>  $this->data['articleid'],
            'createTime'    =>  date('Y-m-d H:i:s'),
        ];
        if(!in_array($data['type'],$type)){
            return json_data(180,$this->codeMessage[180],'');
        }
        if(Like::get(['userid'=>$data['userid'],'type'=>$data['type'],'articleid'=>$data['articleid']])){
            return json_data(182,$this->codeMessage[182],'');
        }

        Like::create($data);
        return json_data(0,$this->codeMessage[0],'');
    }

    /**
     * 取消点赞
     */
    public function canclelike(){
        $type = ['ask','answer','comment'];
        $retype = $this->data['type'];
        if(!in_array($retype,$type)){
            return json_data(180,$this->codeMessage[180],'');
        }
        if(!Like::get(['userid'=>$this->user->id,'type'=>$this->data['type'],'articleid'=>$this->data['articleid']])){
            return json_data(183,$this->codeMessage[183],'');
        }
        $delete = Like::destroy([
            'userid'    =>  $this->user->id,
            'type'      =>  $this->data['type'],
            'articleid' =>  $this->data['articleid']
        ]);

        if($delete){
            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            return json_data(181,$this->codeMessage[181],'');
        }
    }

    /**
     * 得到【我的收藏】列表
     * @return array
     */
    public function getcollect(){
        $video_type = ['mp4','url'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $field = 'cf.id,cf.courseid,cf.userid,c.title,c.smallPicture';
        $course = Db::name('course_favorite')
            ->alias('cf')
            ->join('course c','cf.courseid=c.id')
            ->field($field)
            ->where('cf.userid',$this->user->id)
            ->page($page,10)
            ->select();

        //这里以课程为基底，查出对应数据
        if($course){
            $done_course = array();
            foreach ( $course as &$c ){
                //拼上域名
                $c['smallPicture'] = $this->request->domain()."/".$c['smallPicture'];
                //算课程总数，算法为完成的课程数/课程总数，pdf和ppt直接为整个的课程数1，视频的话按看过的时间/该课程总时间，得到一个完成的比例，当作小数用
                $all_task_num = Db::name('course_task')->where('courseId',$c['courseid'])->where('status',1)->count();
                
                //一共学的时间
                //先赋值一个基底防止没有数据
                $c['lastwatch'] = '';
                $c['plan'] = "0";
                if($all_task_num!=0){
                    //选出对应课程id的学习记录,结果是学过了这节课的哪些task
                $learn_task = Db::name('study_result')
                    ->where('courseid',$c['courseid'])
                    ->where('userid',$this->user->id)
                    ->order('endtime desc')
                    ->select();
                //完成的课程数目从0开始计算
                $has_learn_time = 0;
                //如果找到该课程的学习记录了（原则上碰不到找不到的情况）
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
                    //拿倒序排列的第一个作为最后观看时间
                    $c['lastwatch'] = date('Y-m-d',strtotime($learn_task[0]['endtime']));
                }
                //计算这个课程的完成比，换算成百分比
                $c['plan'] = (round($has_learn_time/$all_task_num,2)*100);
                }
                
                $done_course[] = $c;
            }
            return json_data(0,$this->codeMessage[0],$done_course);
        }
        return json_data(0,$this->codeMessage[0],[]);
    }
    /**
     * 得到【我的学习-学习中】列表(abandoned,use the rebuild version)
     * @return array
     */
    public function mystudyabandoned(){
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $course = Db::name('study_result')
            ->alias('sr')
            ->join('course c','sr.courseid=c.id')
            ->field('sr.courseid as id,c.title,c.smallPicture')
            ->group('sr.courseid')
            ->select();

        if($course){
            $done_course = array();
            foreach ( $course as &$c ){
                $c['smallPicture'] = $this->request->domain()."/".$c['smallPicture'];
                //算总时间
                $course_all_time = 0;
                $all_task = Db::name('course_task')->where('courseId',$c['id'])->field('length')->select();
                foreach ( $all_task as $at ){
                    $length = explode(':',$at['length']);
                    $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;
                    $course_all_time = $course_all_time+$couse_time;
                }
                //一共学的时间
                $c['lastwatch'] = '';
                $c['plan'] = "0%";
                $learn_task = Db::name('study_result')
                    ->where('courseid',$c['id'])
                    ->where('userid',$this->user->id)
                    ->order('endtime desc')
                    ->select();
                $has_learn_time = 0;
                if($learn_task){
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
                    $c['lastwatch'] = $learn_task[0]['endtime'];
                }
                if($course_all_time!=0){
                    $c['plan'] = (round($has_learn_time/$course_all_time,2)*100)."%";
                }
                if($c['plan']!='100%'){
                    $done_course[] = $c;
                }
            }
            return json_data(0,$this->codeMessage[0],$done_course);
        }
        else{
            return json_data(0,$this->codeMessage[0],array());
        }
    }
    //this is rebuild version,use this
    public function mystudy(){
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $video_type = ['mp4','url'];
        //先选择学习记录并group掉重复的课程
        $course = Db::name('study_result')
            ->alias('sr')
            ->join('course c','sr.courseid=c.id')
            ->field('sr.courseid as id,c.title,c.smallPicture')
            ->where('sr.userid',$this->user->id)
            ->group('sr.courseid')
            ->page($page,10)
            ->select();
        //这里以课程为基底，查出对应数据
        if($course){
            $done_course = [];
            foreach ( $course as &$c ){
                //拼上域名
                $c['smallPicture'] = $this->request->domain()."/".$c['smallPicture'];
                //算课程总数，算法为完成的课程数/课程总数，pdf和ppt直接为整个的课程数1，视频的话按看过的时间/该课程总时间，得到一个完成的比例，当作小数用
                $all_task_num = Db::name('course_task')->where('courseId',$c['id'])->where('status',1)->count();
                //一共学的时间
                //先赋值一个基底防止没有数据
                $c['lastwatch'] = '';
                $c['plan'] = "0";
                //选出对应课程id的学习记录,结果是学过了这节课的哪些task
                $learn_task = Db::name('study_result')
                    ->where('courseid',$c['id'])
                    ->where('userid',$this->user->id)
                    ->order('endtime desc')
                    ->select();
                //完成的课程数目从0开始计算
                $has_learn_time = 0;
                //如果找到该课程的学习记录了（原则上碰不到找不到的情况）
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
                    //拿倒序排列的第一个作为最后观看时间
                    $c['lastwatch'] = $learn_task[0]['endtime'];
                }
                //计算这个课程的完成比，换算成百分比
                $c['plan'] = (round($has_learn_time/$all_task_num,2)*100);
                //如果不是100%的话，放入正在学习的数组
                if($c['plan']!='100'){
                    $done_course[] = $c;
                }
            }
            return json_data(0,$this->codeMessage[0],$done_course);
        }
        else{
            return json_data(0,$this->codeMessage[0],[]);
        }
    }

    /**
     * 得到【我的学习-已学完】列表(rebuild version)
     * @return array
     */
    public function donestudy(){
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $video_type = ['mp4','url'];
        //先选择学习记录并group掉重复的课程
        $course = Db::name('study_result')
            ->alias('sr')
            ->join('course c','sr.courseid=c.id')
            ->field('sr.courseid as id,c.title,c.smallPicture')
            ->where('sr.userid',$this->user->id)
            ->group('sr.courseid')
            ->page($page,10)
            ->select();
        //这里以课程为基底，查出对应数据
        if($course){
            $done_course = array();
            foreach ( $course as &$c ){
                //拼上域名
                $c['smallPicture'] = $this->request->domain()."/".$c['smallPicture'];
                //算课程总数，算法为完成的课程数/课程总数，pdf和ppt直接为整个的课程数1，视频的话按看过的时间/该课程总时间，得到一个完成的比例，当作小数用
                $all_task_num = Db::name('course_task')->where('courseId',$c['id'])->where('status',1)->count();
                //一共学的时间
                //先赋值一个基底防止没有数据
                $c['lastwatch'] = '';
                $c['plan'] = "0";
                //选出对应课程id的学习记录,结果是学过了这节课的哪些task
                $learn_task = Db::name('study_result')
                    ->where('courseid',$c['id'])
                    ->where('userid',$this->user->id)
                    ->order('endtime desc')
                    ->select();
                //完成的课程数目从0开始计算
                $has_learn_time = 0;
                //如果找到该课程的学习记录了（原则上碰不到找不到的情况）
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
                    //拿倒序排列的第一个作为最后观看时间
                    $c['lastwatch'] = $learn_task[0]['endtime'];
                }
                //计算这个课程的完成比，换算成百分比
                $c['plan'] = (round($has_learn_time/$all_task_num,2)*100);
                //如果不是100%的话，放入正在学习的数组
                if($c['plan']=='100'){
                    $done_course[] = $c;
                }
            }
            return json_data(0,$this->codeMessage[0],$done_course);
        }
        else{
            return json_data(0,$this->codeMessage[0],array());
        }
    }


    /**
     * 开始观看
     * @return array
     */
    public function startwatch(){
        $courseid  = $this->data['courseid'];
        $chapterid = $this->data['chapterid'];
        $time = date('Y-m-d H:i:s',time());

        $log_data = [
            'userid'    =>  $this->user->id,
            'starttime' => $time,
            'endtime'   => $time,
            'courseid'  => $courseid,
            'chapterid' => $chapterid
        ];
        StudyResultLog::create($log_data);

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

        $courseid  = $this->data['courseid'];
        $chapterid = $this->data['chapterid'];

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
            if($watch_time>=$couse_time){
                $data['status'] = 1;
            }
            StudyResult::update($data,['id'=>$watch['id']]);

            //对log进行判断
            $watch_log = Db::name('study_result_log')->where(['courseId'=>$courseid,'chapterid'=>$chapterid])->order('starttime desc')->find();
            if($watch_log){

                $length = explode(':',$course['length']);
                $couse_time  = $length[2]+$length[1]*60+$length[0]*3600;

                $watch_time = $time-strtotime($watch_log['starttime']);

                $data = ['endtime' => date('Y-m-d H:i:s',$time)];
                if($watch_time>=$couse_time){
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
                StudyResultLog::update($data,['id'=>$watch_log['id']]);
            }

            return json_data(0,$this->codeMessage[0],'');
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
        $taskid = $this->data['taskid'];
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
            'taskid'    =>  $this->data['taskid'],
            'createTime'=>  time(),
        ];
        if(Db::name('checkin')->insert($data)){
            return json_data(0,$this->codeMessage[0],'');
        } else{
            return json_data(187,$this->codeMessage[187],'');
        }
    }












}
