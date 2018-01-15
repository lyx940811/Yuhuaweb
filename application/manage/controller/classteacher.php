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
class Classteacher extends Base{
    public function delete(){

        $id = $_GET['rid']+0;
        $ok = Db::name('classteacher')->where("id='$id'")->delete();
        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

    //添加班级教师
    public function addteacher(){
        $id=$this->request->param('id')+0;
        if($id){
            $where['a.id']=$id;
            $where['ct.classid']=$id;
        }

        $list = Db::name('classroom a')
            ->join('category b','a.categoryId=b.code','LEFT')
            ->join('classteacher ct','a.id=ct.classid','LEFT')
            ->join('teacher_info c','ct.teacherid=c.id','LEFT')
            ->field('ct.id,a.about,a.title,a.status,a.categoryId,a.teacherIds,a.hitNum,a.studentNum,a.createdTime,c.id as cid,c.realname,b.name,b.code,ct.type')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $teacher = Db::table('teacher_info')->field('id,realname')->select();
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('id',$id);
        $this->assign('teacher',$teacher);
        $this->assign('typename','班级管理');

        return $this->fetch();
    }

    //教师添加
    public function add(){
        $info = input('get.');

        $msg  =   [
            'teacherIds.require' => '请选择教师',
        ];

        $validate = new Validate([
            'teacherIds'  => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

//        $role_table = Db::name('classroom');
        if(!$info['type']){
            $info['type']=0;
        }
        $data = [
            'classid' => $info['classid'],
            'teacherid'=> $info['teacherIds'],
            'type'=>$info['type'],
            'createdtime'=>date('Y-m-d H:i:s',time()),
            'userid'=>session('admin_uid'),
        ];

        $ok = DB::table('classteacher')->insert($data);
        if($ok){
            manage_log('107','003','添加班级',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    //教师添加
    public function edit(){
        $info = input('get.');

        $msg  =   [
            'teacherIds.require' => '请选择教师',
        ];

        $validate = new Validate([
            'teacherIds'  => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

//        $role_table = Db::name('classroom');
        if(!$info['type']){
            $info['type']=0;
        }
        $data = [
            'classid' => $info['classid'],
            'teacherid'=> $info['teacherIds'],
            'type'=>$info['type'],
            'userid'=>session('admin_uid'),
        ];

        $ok = DB::table('classteacher')->where('id', $info['rid'])->update($data);
        if($ok){
            manage_log('107','003','添加班级',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

}