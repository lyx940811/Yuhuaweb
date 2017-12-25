<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/20
 * Time: 17:25
 */
namespace app\manage\controller;

use think\Db;

class Log extends Base{

    public function index(){

        $info = input('get.');


        if(request()->get()){//搜索

            $wheredata = [];

            if(isset($info['user_id'])){
                $wheredata['userid'] = ['=',$info['user_id']];
            }
            if(isset($info['user_module'])){
                $classname = Db::name('classmodule')->field('id')->where('classname','=',$info['user_module'])->find();
                $wheredata['module'] = ['=',$classname['id']];
            }
            if(isset($info['user_action'])){
                $wheredata['action'] = ['like',"%{$info['user_action']}%"];
            }
            if(isset($info['user_mes'])){
                $wheredata['message'] = ['like',"%{$info['user_mes']}%"];
            }

            $list = Db::name('log')
                ->where($wheredata)
                ->paginate(20,false,['query' => request()->get()]);


        }else{//普通显示

            $list = Db::name('log')->paginate(20,false,['query' => request()->get()]);


        }

        $newlist = [];
        foreach ($list as $k=>$v){

            if($v['module']){
                $newlist[$k] = $v;
                $mname = Db::name('classmodule')->field('classname')->where("id={$v['module']}")->find();
                $newlist[$k]['mname'] =$mname['classname'];

            }

        }

        $this->assign('list',$newlist);
        $this->assign('page',$list->render());
        return $this->fetch();
    }


}