<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/29
 * Time: 10:30
 */
namespace app\manage\controller;

use think\Db;

class Companynature extends Base{

    public function index(){

        $list = Db::table('companynature')->field('id,classname,code,Flag')->paginate(20);

        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('typename','企业性质');
        return $this->fetch();
    }
}