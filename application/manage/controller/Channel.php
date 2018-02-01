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

        $list = channelmodel::where($where)->paginate(20);


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
}