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

class Coursefile extends Base{
    public function index(){

        $list = Db::table('course_file')
            ->alias('a')
            ->field('a.id,a.filename,a.filepath,a.filesize,a.type,a.courseid,a.lessonid,b.title')
            ->join('course b','a.courseid=b.id','LEFT')
            ->paginate(20);

        $this->assign('list',$list);
        $this->assign('typename','课程资料');
        $this->assign('page',$list->render());
        return $this->fetch();
    }
}