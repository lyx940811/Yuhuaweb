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

        $wheredata = [];

        if(!empty($info['user_id'])){
            $wheredata['userid'] = ['=',$info['user_id']];
        }
        if(!empty($info['user_module'])){
            $wheredata['module'] = ['=',$info['user_module']];
        }
        if(!empty($info['user_action'])){
            $wheredata['action'] = ['eq',$info['user_action']];
        }
        if(!empty($info['user_mes'])){
            $wheredata['message'] = ['eq',"%{$info['user_mes']}%"];
        }

        $list = Db::name('log a')
            ->join('classmodule b','a.module=b.id','LEFT')
            ->join('classaction c','a.action=c.code','LEFT')
            ->field('a.id,a.userid,b.classname bname,c.classname cname,a.message,a.createdTime,a.data')
            ->where($wheredata)
            ->paginate(20,false,['query' => request()->get()]);


//        echo Db::name('log a')->getLastSql();
//        print_r($wheredata);
//        exit;
        $module = Db::table('classmodule')->field('id,classname')->select();
        $action = Db::table('classaction')->field('classname,code')->select();

        $this->assign('list',$list);

        $this->assign('module',$module);
        $this->assign('action',$action);
        $this->assign('page',$list->render());
        $this->assign('typename','日志列表');
        return $this->fetch();
    }


}