<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/6
 * Time: 16:41
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Rewardpointflow extends Base{

    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['userid'])){

            $where['title'] = ['like',"%{$info['title']}%"];
        }

        $list = Db::table('reward_point_flow a')
            ->field('a.*')
            ->where($where)
            ->paginate(20,['query'=>$info]);

        $course = Db::table('course')->field('id,title')->select();
        $teacher = Db::table('teacher_info')->field('id')->select();

        $this->assign('list',$list);
        $this->assign('course',$course);
        $this->assign('typename','积分记录');
        $this->assign('page',$list->render());
        return $this->fetch();
    }
}