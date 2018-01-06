<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/29
 * Time: 10:58
 */
namespace app\manage\controller;

use think\Db;

class Filetype extends Base{

    public function index(){

        $list = Db::table('filetype')->field('id,classname,code,Flag')->paginate(20);

        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('typename','课程类型');
        return $this->fetch();
    }
}