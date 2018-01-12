<?php
/**
 * Created by PhpStorm.
 * User: M'S
 * Date: 2017/12/26
 * Time: 15:52
 */
namespace app\manage\controller;

use think\Db;

class StudentEnroll2 extends Base{
    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['admission'])){
            $where['admissionID'] = ['eq',$info['admission']];

        }
        if(!empty($info['category'])){
            $where['categoryID'] = ['eq' ,$info['category']];
        }
        if(!empty($info['starttime']) && !empty($info['endtime'])){
            $where['a.createTime'] = ['between time',[$info['starttime']." 00:00:00", $info['endtime']." 23:59:59"]];
        }

        $list = Db::table('student_enroll')
            ->alias('a')
            ->join('category b','a.categoryID=b.code','LEFT')
            ->join('admission c','a.admissionID=c.id','LEFT')
            ->where($where)
            ->field('a.*,b.name,c.title')->paginate(20,false,['query'=>request()->get()]);


        $category = Db::table('category')->field('code,name')->where('Flag','eq',1)->select();

        $admission = Db::table('admission')->field('id,title')->select();
        $this->assign('typename','报名管理');

        $this->assign('list',$list);
        $this->assign('admission',$admission);
        $this->assign('categorylist',$category);
        $this->assign('page',$list->render());

        return $this->fetch();
    }

    public function accept(){
        $id = $_GET['rid']+0;

        if($_GET['type']==1){
            Db::name('student_enroll')->field('status')->where('id',$id)->update(['status'=>1]);
            return ['info'=>'拒绝成功','code'=>'000'];
        }

        $info = input('post.');
        /*
         * 先添加用户表获取userid
         */
        $rand = rand(0,199);
        $data = [
            'username' => $info['telephone'],
            'nickname'=> $info['realname'],//'云工社0'.$rand
            'password' => password_hash('123456',PASSWORD_DEFAULT),
            'mobile'=> $info['telephone'],
            'type'=>3,
            'roles'=>5,//5为学员
            'createdIp'=>request()->ip(),
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'createUserID'=>session('admin_uid'),
            'status'=>1,
        ];

        $user = Db::table('user');
        $user->field('username,nickname,password,mobile,type,roles,createdIp,createdTime,createUserID,status')->insert($data);

        $userid = $user->getLastInsID();

        $day = date('Y',time());
        $data2 = [
            'userid' => $userid,
            'idcard'=> $info['cardsn'],
            'birthday'=>$day-$info['age'].':00:00 00:00:00',
            'mobile'=> $info['telephone'],
            'sex'=>$info['sex'],
            'age'=>$info['age'],
            'school'=>$info['school'],
            'address'=>$info['address'],
            'realname'=>$info['realname'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];

        $user_profile = Db::table('user_profile');
        $user_profile->field('userid,idcard,birthday,mobile,mobile,sex,age,school,address,realname,createdTime')->insert($data2);


        $s['status'] = 2;
        $s['userid'] = $userid;

        $ok = Db::name('student_enroll')->field('status,userid')->where('id',$id)->update($s);

        if($ok){
            return ['info'=>'授理成功','code'=>'000'];
        }else{
            return ['error'=>'授理失败','code'=>'200'];
        }

    }
}