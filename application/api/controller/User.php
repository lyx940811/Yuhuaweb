<?php
namespace app\api\controller;

use think\Controller;
use app\index\model\UserProfile;
use think\Loader;
use think\Config;
use app\index\model\UserProfile as UserProfileModel;
use app\index\model\User as UserModel;
use app\index\model\Friend as FriendModel;
use app\index\model\Asklist;
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
            exit(json_encode(json_data(910,$this->codeMessage[910],'')));
        }

        if($user = UserModel::get(['user_token'=>$user_token])){
            //判断过期没
            if(time()>$user['expiretime']){
                //token过期
                exit(json_encode(json_data(910,$this->codeMessage[910],'')));
            }
            unset($this->data['user_token']);
            $this->user = $user;
        }
        else{
            //没有在数据库内找到对应token
            exit(json_encode(json_data(910,$this->codeMessage[910],'')));
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
//        $this->data['userid'] = $this->user->id;
        $res  = $this->LogicUser->upUserHeadImg($file,$this->user->id);
        return $res;
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
     * APP-得到个人信息
     */
    public function getmyinfo(){
        $data = [
            'username'  =>  $this->user->username,
            'avatar'    =>  $this->request->domain().DS.$this->user->title,
            'mobile'    =>  $this->user->mobile,
            'classname' =>  '电气化1702班',
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
        $type = ['ask','answer','commment'];
        $data = [
            'userid'        =>  $this->user->id,
            'type'          =>  $this->data['type'],
            'articleid'     =>  $this->data['articleid'],
            'createTime'    =>  date('Y-m-d H:i:s'),
        ];
        if(!in_array($data['type'],$type)){
            return json_data(180,$this->codeMessage[180],'');
        }
        Like::create($data);
        return json_data(0,$this->codeMessage[0],'');
    }

    /**
     * 取消点赞
     */
    public function canclelike(){
        $type = ['ask','answer','commment'];
        $retype = $this->data['type'];
        if(!in_array($retype,$type)){
            return json_data(180,$this->codeMessage[180],'');
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
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $field = 'cf.id,cf.courseid,cf.userid,c.title,c.smallPicture';
        $course = Db::name('course_favorite')
            ->alias('cf')
            ->join('course c','cf.courseid=c.id')
            ->field($field)
            ->where('cf.userid',$this->user->id)
            ->page($page,10)
            ->select();
        foreach ( $course as &$c ){
            $c['smallPicture'] = $this->request->domain().DS.$c['smallPicture'];
            $c['plan'] = '0%';
            $c['lastwatch'] = '2017-12-28 13:27:34';
        }
        return json_data(0,$this->codeMessage[0],$course);
    }
    /**
     * 得到【我的学习-学习中】列表
     * @return array
     */
    public function mystudy(){
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $field = 'c.id,c.title,c.smallPicture';
        $course = Db::name('course')
            ->alias('c')
            ->field($field)
            ->where('id','in',[5,8])
            ->page($page,10)
            ->select();
        foreach ( $course as &$c ){
            $c['smallPicture'] = $this->request->domain().DS.$c['smallPicture'];
            $c['plan'] = '10%';
            $c['lastwatch'] = '2017-12-28 13:27:34';
        }

        return json_data(0,$this->codeMessage[0],$course);
    }
    /**
     * 得到【我的学习-已学完】列表
     * @return array
     */
    public function donestudy(){
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $field = 'c.id,c.title,c.smallPicture';
        $course = Db::name('course')
            ->alias('c')
            ->field($field)
            ->where('id','in',[11,12,13])
            ->page($page,10)
            ->select();
        foreach ( $course as &$c ){
            $c['smallPicture'] = $this->request->domain().DS.$c['smallPicture'];
            $c['plan'] = '10%';
            $c['lastwatch'] = '2017-12-28 13:27:34';
        }
        return json_data(0,$this->codeMessage[0],$course);
    }











}
