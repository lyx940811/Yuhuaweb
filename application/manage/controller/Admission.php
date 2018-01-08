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
 * 招生控制器
 */

class Admission extends Base{

    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['title'])){

            $where['title'] = ['like',"%{$info['title']}%"];
        }

        $list = Db::name('admission')->where($where)->paginate(20,false,['query'=>request()->get()]);


        $this->assign('list',$list);
        $this->assign('page',$list->render());

        $this->assign('typename','招生管理');

        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'title.require' => '报名名称不能为空',
            'title.length' => '报名名称长度太短',
            'price.require' => '价格不能为空',
            'content.number' => '简章不能为空',
        ];
        $validate = new Validate([
            'title'  => 'require|length:2,20',
            'price'   => 'require',
            'content'  => 'require'
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('admission');

        $data = [
            'title' => $info['title'],
            'userid'=> session('admin_uid'),
            'price' => $info['price'],
            'num'=> $info['num'],
            'content'=>$info['content'],
            'linker'=>$info['linker'],
            'telephone'=>$info['telephone'],
            'endtime'=>$info['endtime'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'status'=>1,
        ];

        $ok = $role_table->field('title,userid,price,num,content,linker,telephone,endtime,createdTime,status')->insert($data);

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

            $have = Db::name('admission')->field('title,price,num,content,linker,telephone,endtime')->where("id='$id'")->find();

            if(!$have){//如果这个code有
                return ['error'=>'没有此招生','code'=>'300'];
            }else{
                return ['info'=>$have,'code'=>'000'];
            }

        }

        $info = input('post.');

        $msg  =   [
            'title.require' => '报名名称不能为空',
            'title.length' => '报名名称长度太短',
            'price.require' => '价格不能为空',
            'content.number' => '简章不能为空',
        ];
        $validate = new Validate([
            'title'  => 'require|length:2,20',
            'price'   => 'require',
            'content'  => 'require'
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('admission');

        $id = $info['rid']+0;

        $data = [
            'title' => $info['title'],
            'userid'=> session('admin_uid'),
            'price' => $info['price'],
            'num'=> $info['num'],
            'content'=>$info['content'],
            'linker'=>$info['linker'],
            'telephone'=>$info['telephone'],
            'endtime'=>$info['endtime'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->field('title,userid,price,num,content,linker,telephone,endtime,createdTime')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('admission')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }



}