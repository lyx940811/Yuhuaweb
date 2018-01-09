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
 * 教师评价控制器
 */

class Teacherreview extends Base{

    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['content'])){
            $where['content'] = ['like',"%{$info['content']}%"];
        }

        $list = Db::name('teacher_review a')
            ->join('teacher_info b','a.teacherid=b.id','LEFT')
            ->join('user c','a.userid=b.id','LEFT')
            ->where($where)
            ->field('a.*,b.realname,c.nickname')
            ->paginate(20,false,['query'=>request()->get()]);


        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('typename','教师评价管理');

        return $this->fetch();
    }

    public function add(){
        $info = input('post.',NULL,'htmlspecialchars');

        $msg  =   [
            'teacherid.require' => '教师不能为空',
            'content.require' => '评价内容不能为空',
            'content.length' => '评价内容长度太短',
        ];
        $validate = new Validate([
            'teacherid'  => 'require',
            'content'  => 'require|length:2,20',

        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('teacher_review');

        $data = [
            'teacherid'=>$info['teacherid'],
            'userid' => session('admin_uid'),
            'content' => $info['content'],
            'createtime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->field('teacherid,userid,content,createtime')->insert($data);

        if($ok){

            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){

        $info = input('post.');
        $msg  =   [
            'teacherid.require' => '教师不能为空',
            'content.require' => '评价内容不能为空',
            'content.length' => '评价内容长度太短',
        ];
        $validate = new Validate([
            'teacherid'  => 'require',
            'content'  => 'require|length:2,20',

        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('teacher_review');

        $id = $info['rid']+0;

        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此教师评价','code'=>'300'];
        }

        $data = [
            'teacherid'=>$info['teacherid'],
            'userid' => session('admin_uid'),
            'content' => $info['content'],
            'createtime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->field('teacherid,userid,content,createtime')->where('id',$id)->update($data);

        if($ok){

            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('teacher_review')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }



}