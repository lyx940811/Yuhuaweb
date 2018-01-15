<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/2
 * Time: 14:34
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Coursefavorite extends Base{
    public function index(){

        $list = Db::table('course_favorite')
            ->alias('a')
            ->field('a.id,a.url,a.createTime,a.userid,b.title,u.username')
            ->join('course b','a.courseid=b.id','LEFT')
            ->join('user u','a.userid=u.id')
            ->order('a.id desc')
            ->paginate(20);

        $this->assign('list',$list);
        $this->assign('typename','课程收藏记录');
        $this->assign('page',$list->render());
        return $this->fetch();
    }
}