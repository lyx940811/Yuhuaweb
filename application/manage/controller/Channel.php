<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/31
 * Time: 10:05
 */
namespace app\manage\controller;
use app\manage\model\Channel as channelmodel;
use think\Db;
use think\Loader;
use think\Validate;

class Channel extends Base{

    public function index(){

        $info = input('get.');
        $where = [];

        if(!empty($info['salary'])){
            $where['salary'] = ['eq',$info['salary']];
        }
        if(!empty($info['type'])){
            $where['type'] = ['eq',$info['type']];
        }
        if(!empty($info['title'])){
            $where['title'] = ['like',"%{$info['title']}%"];
        }

        $list = channelmodel::where($where)->order('id desc')->paginate(20);


        $type = Db::table('channel_type')->select();
        $salary = Db::table('channel_salary')->select();
        $level = Db::table('channel_level')->select();

        $this->assign('list',$list);
        $this->assign('type',$type);
        $this->assign('salary',$salary);
        $this->assign('level',$level);
        $this->assign('page',$list->render());
        $this->assign('uid',session('admin_uid'));
        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $data = [
            'userid'=>session('admin_uid'),
            'title'=>$info['title'],
            'type'=>$info['type'],
            'level'=>$info['level'],
            'salary'=>$info['salary'],
            'linker'=>$info['linker'],
            'phone'=>$info['phone'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'area'=>$info['area'],
        ];

        $validate = Loader::validate('Channel');

        if(!$validate->check($info)){
            return ['error'=>$validate->getError(),'code'=>200];
        }

        $ok = channelmodel::create($data);
        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'200'];
        }


    }

    public function edit(){
        $info = input('post.');

        $data = [
            'title'=>$info['title'],
            'type'=>$info['type'],
            'level'=>$info['level'],
            'salary'=>$info['salary'],
            'linker'=>$info['linker'],
            'phone'=>$info['phone'],
            'area'=>$info['area'],
        ];

        $validate = Loader::validate('Channel');

        if(!$validate->check($info)){
            return ['error'=>$validate->getError(),'code'=>200];
        }

        $id = $info['id']+0;
        $ok = channelmodel::update($data,['id'=>$id]);
        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }


    }

    public function showedit(){
        $id = $this->request->get('id')+0;
        $a = channelmodel::get($id);
        $type = Db::table('channel_type')->select();
        $salary = Db::table('channel_salary')->select();
        $level = Db::table('channel_level')->select();

        $this->assign('type',$type);
        $this->assign('salary',$salary);
        $this->assign('level',$level);
        $this->assign('a',$a);
        return $this->fetch();

    }

    public function delete(){

        $id = $_GET['rid']+0;
        $ok = channelmodel::destroy($id);
        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}