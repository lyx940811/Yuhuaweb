<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/27
 * Time: 11:38
 */
namespace app\manage\controller;


use think\Db;
use think\Validate;

class Categorycertificate extends Base{
    public function index(){

        $info = input('get.');
        $data['category']='';
        $data['name']='';

        $where = [];
        if(!empty($info['category'])){
            $data['category']=$info['category'];
            $where['a.categoryID'] = ['eq',$info['category']];
        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['a.name'] = ['like',"%{$info['name']}%"];
        }

        $list = Db::table('categorycertificate a')
            ->join('category b','a.categoryID=b.code')
            ->field('a.id,b.code,b.name as bname,a.name,a.unit,a.createtime,a.userid,a.categoryID')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);


        $category = Db::table('category')->field('code,name')->where('Flag','eq',1)->select();
        $this->assign('list',$list);
        $this->assign('info',$data);
        $this->assign('typename','专业证书');
        $this->assign('categorylist',$category);
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'category.require' => '专业名称不能为空',
            'category.length' => '专业名称长度太短',
            'category.number' => '学分必须为数字',
            'name.require' => '代码不能为空',
        ];
        $validate = new Validate([
            'category'  => 'require|length:2,20|number',
            'name'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }


        $role_table = Db::name('categorycertificate');


        $data = [
            'categoryID' => $info['category'],
            'name' => $info['name'],
            'unit'=> $info['unit'],
            'userid'=>session('admin_uid'),
            'createtime'=>date('Y-m-d H:i:s',time()),
//            'Flag'=>1,
        ];

        $ok = $role_table->field('categoryID,name,unit,userid,createtime,Flag')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    public function edit(){

        $info = input('post.');

        $msg  =   [
            'rid.require' => '专业名称rid不能为空',
            'category.require' => '专业名称不能为空',
            'category.length' => '专业名称长度太短',
            'category.number' => '学分必须为数字',
            'name.require' => '代码不能为空',
        ];

        $validate = new Validate([
            'rid'    => 'require',
            'category'  => 'require|length:2,20|number',
            'name'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('categorycertificate');

        $id = $info['rid']+0;
        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此专业证书','code'=>'300'];
        }

        $data = [
            'categoryID' => $info['category'],
            'name' => $info['name'],
            'unit'=> $info['unit'],
            'userid'=>session('admin_uid'),
            'createtime'=>date('Y-m-d H:i:s',time()),
            'Flag'=>1,
        ];

        $ok = $role_table->field('categoryID,name,unit,userid,createtime,Flag')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }
}