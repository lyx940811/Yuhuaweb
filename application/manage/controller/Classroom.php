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
 * 班级管理
 */
class Classroom extends Base{

    public function index(){

        $info = input('get.');
        $where = [];
        $data['status']='';
        $data['categoryId']='';
        $data['title']='';
        if(!empty($info['title'])){
            $data['title']=$info['title'];
            $where['a.title'] = ['like',"%{$info['title']}%"];
        }
        if(!empty($info['categoryId'])){
            $data['categoryId']=$info['categoryId'];
            $where['a.categoryId']=$info['categoryId'];//由于0的特殊性，页面搜索数据全部加1
        }
        if(!empty($info['status'])){
            $data['status']=$info['status'];
            $where['a.status']=$info['status']-1;
        }

        $list = Db::name('classroom a')
            ->join('category b','a.categoryId=b.code','LEFT')
            ->join('teacher_info c','a.teacherIds=c.userid','LEFT')
            ->field('a.id,a.graduation,a.about,a.title,a.status,a.categoryId,a.teacherIds,a.hitNum,a.studentNum,a.createdTime,c.id as cid,c.realname,b.name,b.code')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $category = Db::table('category')->field('code,parentcode,name')->select();
        $categorylist = tree($category);

        $teacher = Db::table('teacher_info')->field('id,realname')->select();

        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('info',$data);
        $this->assign('teacher',$teacher);
        $this->assign('category',$categorylist);
        $this->assign('typename','班级管理');

        return $this->fetch();
    }

    public function add(){
        $info = input('post.');
        $msg  =   [
            'title.require' => '名称不能为空',
            'title.length' => '名称长度太短',
        ];

        $validate = new Validate([
            'title'  => 'require|length:2,20',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('classroom');

        $data = [
            'title' => $info['title'],
            'categoryId' => $info['categoryId'],
            'teacherIds'=> 0,
            'about'=>$info['about'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'status'=>$info['status'],
        ];

        $ok = $role_table->field('title,categoryId,teacherIds,about,createdTime,status')->insert($data);

        if($ok){
            manage_log('107','003','添加班级',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){

        $info = input('post.');

        $msg  =   [
            'title.require' => '名称不能为空',
            'title.length' => '名称长度太短',
        ];

        $validate = new Validate([
            'title'  => 'require|length:2,20',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('classroom');

        $id = $info['rid']+0;

//        $have = $role_table->field('id')->where("id='$id'")->find();
//
//        if(!$have){//如果没这个code
//            return ['error'=>'没有此班级','code'=>'300'];
//        }

        $data = [
            'title' => $info['title'],
            'categoryId' => $info['categoryId'],
            'teacherIds'=> 0,
            'about'=>$info['about'],
//            'createdTime'=>date('Y-m-d H:i:s',time()),
            'status'=>$info['status'],
        ];

        $ok = $role_table->field('title,categoryId,teacherIds,about,status')->where('id',$id)->update($data);

        if($ok){
            manage_log('107','004','修改班级',serialize($data),0);
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;
        $ok = Db::name('classroom')->where("id='$id'")->delete();
        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

    //班级毕业
    public function graduation(){
        $id=$this->request->param('rid')+0;
        $type=$this->request->param('type')+0;
        $type==1?$status=0:$status=1;
        Db::startTrans();
        $ok=Db::table('classroom')->where('id',$id)->update(['graduation'=>$type,'status'=>$status]);
        if($ok){
            DB::table('student_school')->where('class',$id)->update(['studentstatus'=>$type]);
            $usersid=DB::table('student_school')->where('class',$id)->column('userid');
            Db::table('user')->where('id','in',$usersid)->update(['status'=>$status]);
            Db::commit();
            return ['info'=>'设置毕业成功','code'=>'000'];
        }else{
            Db::rollback();
            return ['info'=>'设置毕业失败','code'=>'200'];
        }
    }

    //添加班级教师
//    public function addteacher(){
//        $id=$this->request->param('id')+0;
//        if($id){
//            $where['a.id']=$id;
//            $where['b.classid']=$id;
//        }
//
//        $list = Db::name('classroom a')
//            ->join('category b','a.categoryId=b.code','LEFT')
//            ->join('teacher_info c','a.teacherIds=c.id','LEFT')
//            ->join('classTeacher ct','a.id=ct.classid','LEFT')
//            ->field('a.id,a.about,a.title,a.status,a.categoryId,a.teacherIds,a.hitNum,a.studentNum,a.createdTime,c.id as cid,c.realname,b.name,b.code')
//            ->where($where)
//            ->paginate(20,false,['query'=>request()->get()]);
//
//        $teacher = Db::table('teacher_info')->field('id,realname')->select();
//    }


}