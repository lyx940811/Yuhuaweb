<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/25
 * Time: 11:43
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

/*
 * 教师控制器
 */

class Teacherinfo extends Base{

    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['realname'])){
            $where['realname'] = ['like',"%{$info['realname']}%"];
        }

        $list = Db::name('teacher_info a')
            ->join('teacher_level b','a.id=b.teacherid','LEFT')
            ->where($where)
            ->field('a.*,b.education,b.degree,b.topeducation,b.topdegree')
            ->paginate(20,false,['query'=>request()->get()]);

        $newlist = [];
        foreach ($list as $k=>$v){
            $newlist[$k] = $v;
            $newlist[$k]['work'] = Db::table('teacher_work')->where('teacherid='.$v['id'])->select();
        }


//        print_r($newlist[1]['work']);
//        exit;


        $this->assign('list',$newlist);
        $this->assign('page',$list->render());
        $this->assign('typename','教师管理');

        return $this->fetch();
    }

    public function add(){
        $info = input('post.',NULL,'htmlspecialchars');

        $msg  =   [
            'sn.require' => '专业名称不能为空',
            'sn.length' => '专业名称长度太短',
            'realname.require' => '代码不能为空',
            'sex.require' => '性别不能为空',
            'nation.require' => '民族不能为空',
            'birthday.require' => '生日不能为空',
            'card.require' => '身份证号必须填写',
            'card.length' => '身份证号太短',
            'policy.require' => '政治面貌不能为空',
        ];
        $validate = new Validate([
            'sn'  => 'require|length:2,20',
            'realname'   => 'require',
            'sex'   => 'require',
            'nation'   => 'require',
            'birthday'   => 'require',
            'card'  => 'require|length:18,21',
            'policy'   => 'require',

        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('teacher_info');

        $is_have = $role_table->field('id')->where(['card'=>['eq',$info['card']]])->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此身份证号','code'=>'300'];
        }


        $data = [
            'userid'=>session('admin_uid'),
            'sn' => $info['sn'],
            'realname' => $info['realname'],
            'sex'=> $info['sex'],
            'nation'=>$info['nation'],
            'birthday'=>$info['birthday'],
            'card'=>$info['card'],
            'policy'=>$info['policy'],
            'phone'=>$info['phone'],
            'province'=>$info['province'],
            'household'=>$info['household'],
            'address'=>$info['address'],
            'maritalstatus'=>$info['maritalstatus'],
            'cardpic'=>$info['cardpic'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->insert($data);

        if($ok){
            //成功了添加学历信息表
            $leveldata = [
                'teacherid'=>$role_table->getLastInsID(),
                'education'=>$info['education'],
                'degree'=>$info['degree'],
                'topeducation'=>$info['topeducation'],
                'topdegree'=>$info['topdegree'],
                'createTime'=>date('Y-m-d H:i:s',time()),
                'createuserid'=>session('admin_uid'),
            ];
            Db::table('teacher_level')->insert($leveldata);

            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){

        $info = input('post.');
        $msg  =   [
            'sn.require' => '专业名称不能为空',
            'sn.length' => '专业名称长度太短',
            'realname.require' => '代码不能为空',
            'sex.require' => '性别不能为空',
            'nation.require' => '民族不能为空',
            'birthday.require' => '生日不能为空',
            'card.require' => '身份证号必须填写',
            'card.length' => '身份证号太短',
            'policy.require' => '政治面貌不能为空',
        ];
        $validate = new Validate([
            'sn'  => 'require|length:2,20',
            'realname'   => 'require',
            'sex'   => 'require',
            'nation'   => 'require',
            'birthday'   => 'require',
            'card'  => 'require|length:18,21',
            'policy'   => 'require',

        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('teacher_info');

        $id = $info['rid']+0;

        $have = $role_table->field('id,cardpic')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此教师','code'=>'300'];
        }

        $data = [
            'userid'=>session('admin_uid'),
            'sn' => $info['sn'],
            'realname' => $info['realname'],
            'sex'=> $info['sex'],
            'nation'=>$info['nation'],
            'birthday'=>$info['birthday'],
            'card'=>$info['card'],
            'policy'=>$info['policy'],
            'phone'=>$info['phone'],
            'province'=>$info['province'],
            'household'=>$info['household'],
            'address'=>$info['address'],
            'maritalstatus'=>$info['maritalstatus'],
            'cardpic'=>!empty($info['cardpic'])?$info['cardpic']:$have['cardpic'],
//            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->where('id',$id)->update($data);

        if($ok){

            //成功了修改学历信息表
            $leveldata = [
                'teacherid'=>$id,
                'education'=>$info['education'],
                'degree'=>$info['degree'],
                'topeducation'=>$info['topeducation'],
                'topdegree'=>$info['topdegree'],
//                'createTime'=>date('Y-m-d H:i:s',time()),
//                'createuserid'=>session('admin_uid'),
            ];
            Db::table('teacher_level')->where('teacherid='.$id)->update($leveldata);

            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function enable(){

        $id = $_GET['rid']+0;

        $data = ['Flag'=>0,'createtime'=>date('Y-m-d H:i:s',time())];
        $ok = Db::name('category')->field('Flag,createtime')->where("id='$id'")->update($data);

        if($ok){
            return ['info'=>'禁用成功','code'=>'000'];
        }else{
            return ['error'=>'禁用失败','code'=>'200'];
        }
    }

    public function upload(){

        $id = $_GET['id']+0;
        $file = upload('newfile'.$id,'teacherinfo');

        return $file;
    }

    public function addunit(){

        if(request()->post('add')=='unit'){

            $info = input('post.');

            $data = [
                'unit'=>$info['unit'],
                'depart'=>$info['depart'],
                'position'=>$info['position'],
                'title'=>$info['title'],
                'starttime'=>$info['starttime'],
                'endtime'=>$info['endtime'],
                'teacherid' => $info['teacherid'],
                'createuserid'=>session('admin_uid'),
                'createTime'=>date('Y-m-d H:i:s',time())
            ];

            $ok = Db::table('teacher_work')->insert($data);
            if($ok){

                return ['info'=>'添加成功','code'=>'000'];
            }else{
                return ['error'=>'添加失败','code'=>'400'];
            }

        }else{
            $tid = $_GET['tid']+0;

            $list = Db::table('teacher_work a')
                ->join('teacher_info b','a.teacherid=b.id','LEFT')
                ->field('a.id,a.teacherid,a.unit,a.depart,b.realname')
                ->where('teacherid='.$tid)->paginate(20);

            $this->assign('list',$list);
            $this->assign('page',$list->render());
            $this->assign('typename','添加教师工作信息');
            return $this->fetch();
        }


    }



}