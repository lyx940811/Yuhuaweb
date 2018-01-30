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
        $data['realname']='';
        $data['name']='';
        $data['status']='';
        $where = [];
        if(!empty($info['realname'])){
            $data['realname']=$info['realname'];
            $where['a.realname'] = ['like',"%{$info['realname']}%"];

        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['a.categoryID'] = ['eq' ,$info['name']];
        }
        if(!empty($info['status'])){
            $data['status']=$info['status'];
            $where['a.status'] = ['eq',$info['status']-1];
        }

        $list = Db::table('student_enroll')
            ->alias('a')
            ->join('category b','a.categoryID=b.code','LEFT')
            ->join('admission c','a.admissionID=c.id','LEFT')
            ->where($where)
            ->order('id desc')
            ->field('a.*,b.name,c.title')->paginate(20,false,['query'=>request()->get()]);


        $category = Db::table('category')->field('code,name')->where('Flag','eq',1)->select();

        $admission = Db::table('admission')->field('id,title')->select();
        $this->assign('typename','报名管理');
        $this->assign('info',$data);
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

//        $rand = rand(0,199);
        $data = [
            'username' => $info['telephone'],
            'nickname'=> $info['realname'],//'云工社0'.$rand
            'password' => password_hash('123456',PASSWORD_DEFAULT),
            'mobile'=> $info['telephone'],
            'type'=>3,
            'roles'=>5,//5为学员
            'title'=>'static/index/images/avatar.png',
            'createdIp'=>request()->ip(),
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'createUserID'=>session('admin_uid'),
            'status'=>1,
        ];

        $user = Db::table('user');
        Db::startTrans();//开启事务
        $ok = $user->insert($data);

        if($ok){
            $userid = $user->getLastInsID();//添加的user表里的id

            //再插入学生表
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
            $user_profile->insert($data2);

            //再插入学生在校表
            $data3 = [
                'userid' => $userid,
                'createuserid'=>session('admin_uid'),
                'createTime'=>date('Y-m-d H:i:s',time()),
                'majors'  =>$info['categoryID'],
            ];
            Db::table('student_school')->insert($data3);

            $s['status'] = 2;
            $s['userid'] = $userid;

            Db::name('student_enroll')->where('id',$id)->update($s);

            Db::commit();
            return ['info'=>'授理成功','code'=>'000'];
        }else{
            Db::rollback();
            return ['error'=>'授理失败','code'=>'200'];
        }

    }
}