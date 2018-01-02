<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2017/12/18
 * Time: 11:31
 */
namespace app\manage\controller;
use think\Db;
use think\paginator\driver\Bootstrap;
use think\Validate;

class Functions extends Base{

    public function index(){

        $lists = Db::name('function')->field('id,name,code,parentcode,url,Flag')->where('flag=1')->order('id asc')->select();

        $treeL = tree($lists);


        $curpage = input('page') ? input('page') : 1;//当前第x页，有效值为：1,2,3,4,5...

        $listRow = 20;//每页2行记录

        $showdata = array_chunk($treeL, $listRow, true);

        $p = Bootstrap::make($showdata, $listRow, $curpage, count($treeL), false, [
            'var_page' => 'page',
            'path'     => url(),//这里根据需要修改url
            'query'    => [],
            'fragment' => '',
        ]);

        $p->appends($_GET);
        $this->assign('typename','栏目功能列表');
        $this->assign('list', $p[$curpage-1]);
        $this->assign('page', $p->render());
        return $this->fetch('index');
    }


    //添加功能栏目和url
    public function add(){

        $info = input('post.');

        //错误信息提示
        $msg  =   [
            'name.require' => '栏目名称不能为空',
            'name.length' => '栏目名称长度太短',
            'code.require' => '代码不能为空',
            'url.require' => '栏目url不能为空',
        ];

        $validate = new Validate([
            'name'  => 'require|length:2,20', //我这里的token是令牌验证
            'code'   => 'require',
            'url'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('function');
//
        $is_have = $role_table->where([
            'code'=>['eq',$info['code']]
        ])->whereOr([
            'url' =>['eq',$info['url']]
        ])->field('id')->find();

        if($is_have){//如果这个code有
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $data['name'] = $info['name'];
        $data['code'] = $info['code'];
        $data['parentcode'] = empty($info['parentcode'])?0:$info['parentcode'];
        $data['grade'] = 1;
        $data['flag'] = 1;
        $data['url'] = $info['url'];

        $ok = $role_table->field('name,code,flag,parentcode,grade,url')->insert($data);

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

            $have = Db::name('function')->field('id')->where("id='$id'")->find();

            if(!$have){//如果这个code有
                return ['error'=>'没有此角色','code'=>'300'];
            }else{
                return ['info'=>$have,'code'=>'000'];
            }

        }
        //前台获取资料结束



        $info = input('post.');

        if($info['parentcode']){

            return ['error'=>'功能栏目不能修改父类','code'=>'200'];
        }

        $msg  =   [
            'rid.require' => '功能栏目rid不能为空',
            'name.require' => '栏目名称不能为空',
            'name.length' => '栏目名称长度太短',
            'code.require' => '代码不能为空',
            'code.number' => '代码必须为数字',
            'url.require' => '栏目url必须填写',
        ];

        $validate = new Validate([
            'rid'  => 'require',
            'name'  => 'require|length:2,20',
            'code'   => 'require|number',
            'url'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('function');

        $id = $info['rid']+0;
        $have = $role_table->field('id,code')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此角色','code'=>'300'];
        }

        $have = $role_table->field('id,code')
            ->where("id <> $id AND code={$info['code']}")
            ->whereOr("id <> $id AND url='{$info['url']}'")->find();

        if($have){
            return ['error'=>'已经有此代码','code'=>'300'];
        }

        $ok = $role_table->field('name,code,url')->where('id',$id)->update(['name' => $info['name'],'code'=>$info['code'],'url'=>$info['url']]);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }
    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('function')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

}