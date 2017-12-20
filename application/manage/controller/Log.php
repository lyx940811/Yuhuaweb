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
        $list = Db::name('log')->paginate(20);

        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
}