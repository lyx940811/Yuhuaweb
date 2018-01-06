<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/29
 * Time: 12:01
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Coursereview extends Base{

    public function index(){

        $list = Db::table('course_review a')
            ->join('course b','a.courseId=b.id','LEFT')
            ->field('a.id,a.userid,a.courseId,a.content,a.private,a.rating,a.createdTime,b.title')->paginate(20);



        $this->assign('list',$list);
        $this->assign('typename','课程评价');
        $this->assign('page',$list->render());
        return $this->fetch();
    }


}