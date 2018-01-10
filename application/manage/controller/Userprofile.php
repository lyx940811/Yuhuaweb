<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/29
 * Time: 12:01
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Userprofile extends Base{

    public function index(){

        $info = input('get.');

        $where = [];

        if(!empty($info['username'])){
            $where['a.realname'] = ['like',"%{$info['username']}%"];
        }

        $list = Db::table('user_profile a')
            ->join('student_school b','a.id=b.userid','LEFT')
            ->join('student_class c','a.id=c.userid','LEFT')
            ->join('classroom d','c.classid=d.id','LEFT')
            ->field('a.*,b.grade,b.starttime,b.depart,b.majors,b.class,b.style,b.studentstatus,d.title')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $newlist = [];
        foreach ($list as $k=>$v){
            $newlist[$k] = $v;
            $newlist[$k]['home'] = Db::table('student_home')->where('userid='.$v['id'])->select();
        }


        $classroom = Db::table('classroom')->field('id,title')->select();

        $this->assign('list',$newlist);
        $this->assign('typename','学生列表');
        $this->assign('classroom',$classroom);
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    public function add(){
        $info = input('post.');


        $msg  =   [
            'sn.require' => '请输入学号',
            'realname.require' => '真实性别不能为空',
            'nation.require' => '请输入民族',
            'birthday.require' => '请输入生日',
            'mobile.require' => '手机号必须填写',
        ];
        $validate = new Validate([
            'sn'  => 'require',
            'realname'   => 'require',
            'nation'   => 'require',
            'birthday'   => 'require',
            'mobile'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('user_profile');

        $data = [
            'sn' => $info['sn'],
            'userid'=>session('admin_uid'),
            'realname' => $info['realname'],
            'sex'=>$info['sex'],
            'nation'=>$info['nation'],
            'cardpic'=>serialize(["front_pic"=>$info['cardpic']]),
            'birthday'=>$info['birthday'],
            'idcard'=>$info['idcard'],
            'policy'=>$info['policy'],
            'mobile'=>$info['mobile'],
            'city'=>$info['city'],
            'household'=>$info['household'],
            'address'=>$info['address'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->insert($data);

        if($ok){
            //添加学生在校信息

            $sdata = [
                'userid'=>$role_table->getLastInsID(),
                'grade'=>$info['grade'],
                'depart'=>$info['depart'],
                'majors'=>$info['majors'],
                'class'=>$info['class'],
                'culture'=>$info['culture'],
                'style'=>$info['style'],
                'academic'=>$info['academic'],
                'starttime'=>$info['starttime'],
                'quarter'=>$info['quarter'],
                'studentstatus'=>$info['studentstatus'],
                'level'=>$info['level'],
                'createTime'=>date('Y-m-d H:i:s',time()),
                'createuserid'=>session('admin_uid'),

            ];
            Db::table('student_school')->insert($sdata);


            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){

        $info = input('post.');

        $msg  =   [
            'sn.require' => '请输入学号',
            'realname.require' => '真实性别不能为空',
            'nation.require' => '请输入民族',
            'birthday.require' => '请输入生日',
            'mobile.require' => '手机号必须填写',
        ];
        $validate = new Validate([
            'sn'  => 'require',
            'realname'   => 'require',
            'nation'   => 'require',
            'birthday'   => 'require',
            'mobile'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('user_profile');

        $id = $info['rid']+0;

        $data = [
            'sn' => $info['sn'],
            'userid'=>session('admin_uid'),
            'realname' => $info['realname'],
            'sex'=>$info['sex'],
            'nation'=>$info['nation'],
            'cardpic'=>serialize(["front_pic"=>$info['cardpic'],'behind_pic'=>'']),
            'birthday'=>$info['birthday'],
            'idcard'=>$info['idcard'],
            'policy'=>$info['policy'],
            'mobile'=>$info['mobile'],
            'city'=>$info['city'],
            'household'=>$info['household'],
            'address'=>$info['address'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->where('id',$id)->update($data);

        if($ok){

            //修改学员在校信息
            $sdata = [
                'grade'=>$info['grade'],
                'depart'=>$info['depart'],
                'majors'=>$info['majors'],
                'class'=>$info['class'],
                'culture'=>$info['culture'],
                'style'=>$info['style'],
                'academic'=>$info['academic'],
                'starttime'=>$info['starttime'],
                'quarter'=>$info['quarter'],
                'studentstatus'=>$info['studentstatus'],
                'level'=>$info['level'],
//                'createTime'=>date('Y-m-d H:i:s',time()),
//                'createuserid'=>session('admin_uid'),

            ];
            Db::table('student_school')->where('userid='.$id)->update($sdata);


            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }
    public function upload(){

        $id = $_GET['id']+0;
        $file = upload('newfile'.$id,'teacherinfo');

        return $file;
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('user_profile')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

    public function addhome(){

        if(request()->post('add')=='home'){

            $info = input('post.');

            $data = [
                'userid'=>$info['userid'],
                'name'=>$info['name'],
                'phone'=>$info['phone'],
                'relation'=>$info['relation'],
                'createuserid'=>session('admin_uid'),
                'createTime'=>date('Y-m-d H:i:s',time())
            ];

            $ok = Db::table('student_home')->insert($data);
            if($ok){

                return ['info'=>'添加成功','code'=>'000'];
            }else{
                return ['error'=>'添加失败','code'=>'400'];
            }

        }else{
            $tid = $_GET['tid']+0;

            $list = Db::table('student_home a')
                ->join('user_profile b','a.userid=b.id','LEFT')
                ->field('a.*')
                ->where('b.id='.$tid)->paginate(20);

            $this->assign('list',$list);
            $this->assign('page',$list->render());
            $this->assign('typename','添加家庭信息');
            return $this->fetch();
        }


    }

}