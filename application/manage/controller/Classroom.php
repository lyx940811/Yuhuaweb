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

        if(!empty($info['title'])){

            $where['a.title'] = ['like',"%{$info['title']}%"];
        }

        $list = Db::name('classroom a')
            ->join('category b','a.categoryId=b.code','LEFT')
            ->join('teacher_info c','a.teacherIds=c.userid','LEFT')
            ->field('a.id,a.title,a.status,a.categoryId,a.teacherIds,a.hitNum,a.studentNum,a.createdTime,c.realname,b.name')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $category = Db::table('category')->field('code,name')->select();
        $teacher = Db::table('teacher_info')->field('id,realname')->select();

        $this->assign('list',$list);
        $this->assign('page',$list->render());

        $this->assign('teacher',$teacher);
        $this->assign('category',$category);
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
            'teacherIds'=> $info['teacherIds'],
            'about'=>$info['about'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'status'=>1,
        ];

        $ok = $role_table->field('title,categoryId,teacherIds,about,createdTime,status')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){
        //前台先获取资料
        if(isset($_GET['do'])=='get'){
            $id = $_GET['rid']+0;

            $have = Db::name('classroom')->field('id,title,categoryId,teacherIds,about')->where("id='$id'")->find();

            if(!$have){//如果这个code有
                return ['error'=>'没有此班级','code'=>'300'];
            }else{
                return ['info'=>$have,'code'=>'000'];
            }

        }

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
            'teacherIds'=> $info['teacherIds'],
            'about'=>$info['about'],
//            'createdTime'=>date('Y-m-d H:i:s',time()),
            'status'=>1,
        ];

        $ok = $role_table->field('title,categoryId,teacherIds,about,status')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('classroom')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }



}