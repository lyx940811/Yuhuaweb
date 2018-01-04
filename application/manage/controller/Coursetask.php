<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/2
 * Time: 14:34
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;
/*
 * 课程任务管理
 */
class Coursetask extends Base{

    public function index(){

        $list = Db::table('course_task a')
            ->field('a.id,a.title,a.courseId,a.mediaSource,b.title btit')
            ->join('course b','a.courseId=b.id','LEFT')
            ->paginate(20);

        $course = Db::table('course')->field('id,title')->select();

        $this->assign('list',$list);
        $this->assign('course',$course);
        $this->assign('typename','课程任务');
        $this->assign('page',$list->render());
        return $this->fetch();
    }


    public function add(){
        $info = input('post.');


        print_r($info);exit;
        $msg  =   [
            'name.require' => '专业名称不能为空',
            'name.length' => '专业名称长度太短',
            'code.require' => '代码不能为空',
            'point.number' => '学分必须为数字',
        ];
        $validate = new Validate([
            'name'  => 'require|length:2,20',
            'code'   => 'require',
            'point'  => 'number'
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }


        $role_table = Db::name('category');

        $is_have = $role_table->field('id')->where(['code'=>['eq',$info['code']]])->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'name' => $info['name'],
            'code' => $info['code'],
            'point'=> $info['point'],
            'studyTimes'=>$info['studyTimes'],
            'description'=>$info['description'],
            'createtime'=>date('Y-m-d H:i:s',time()),
            'Flag'=>1,
        ];

        $ok = $role_table->field('name,code,point,studyTimes,description,createtime,Flag')->insert($data);

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

            $have = Db::name('category')->field('id,name,code,point,studyTimes,description,Flag')->where("id='$id'")->find();

            if(!$have){//如果这个code有
                return ['error'=>'没有此专业','code'=>'300'];
            }else{
                return ['info'=>$have,'code'=>'000'];
            }

        }

        $info = input('post.');

        $msg  =   [
            'rid.require' => '专业名称rid不能为空',
            'name.require' => '专业名称不能为空',
            'name.length' => '专业名称长度太短',
            'code.require' => '代码不能为空',
            'code.number' => '代码必须为数字',
            'point.number' => '学分必须为数字',
        ];

        $validate = new Validate([
            'rid'    => 'require',
            'name'   => 'require|length:2,20',
            'code'   => 'require|number',
            'point'  => 'number'
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('category');

        $id = $info['rid']+0;
        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此专业','code'=>'300'];
        }

        $have = $role_table->field('id,code')->where("id <> $id AND code={$info['code']}")->find();

        if($have){
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data = [
            'name'=>$info['name'],
            'code'=>$info['code'],
            'point'=>$info['point'],
            'studyTimes'=>$info['studyTimes'],
            'description'=>$info['description'],
//            'createtime'=>date('Y-m-d H:i:s',time())
        ];

        $ok = $role_table->field('name,code,point,studyTimes,description,createtime')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }


}