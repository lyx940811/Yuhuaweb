<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/31
 * Time: 10:05
 */
namespace app\manage\controller;
use app\manage\model\ChannelLevel as channellevelmodel;
use think\Loader;

class Channellevel extends Base{

    public function index(){


        $where = [];
        $list = channellevelmodel::where($where)->order('id desc')->paginate(20);


        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('uid',session('admin_uid'));
        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $data = [
            'name'=>$info['name'],
            'code'=>$info['code'],
        ];

        $validate = Loader::validate('Channellevel');

        if(!$validate->check($info)){
            return ['error'=>$validate->getError(),'code'=>200];
        }

        $ok = channellevelmodel::create($data);
        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'200'];
        }


    }

    public function edit(){
        $info = input('post.');

        $data = [
            'name'=>$info['name'],
            'code'=>$info['code'],
        ];

        $validate = Loader::validate('Channellevel');

        if(!$validate->check($info)){
            return ['error'=>$validate->getError(),'code'=>200];
        }

        $id = $info['id']+0;
        $ok = channellevelmodel::update($data,['id'=>$id]);
        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }


    }

    public function showedit(){
        $id = $this->request->get('id')+0;
        $a = channellevelmodel::get($id);

        $this->assign('a',$a);
        return $this->fetch();

    }

    public function delete(){

        $id = $_GET['rid']+0;
        $ok = channellevelmodel::destroy($id);
        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}