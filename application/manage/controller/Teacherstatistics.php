<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/2/6
 * Time: 14:10
 */
namespace app\manage\controller;

use think\Db;

class Teacherstatistics extends  Base{
    public function index(){

        $list = Db::table('teacher_info')
            ->paginate(20);


        $newarr = [];
        foreach ($list->items() as $k=>$v){
//            echo $v['id']."<br/>";
            $newarr[$k] = $v;
            $newarr[$k]['teachnum'] = Db::table('course')->where('teacherIds',$v['id'])->value('count(teacherIds) as num');
            $newarr[$k][''];
        }

        print_r($newarr);exit;

        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
}