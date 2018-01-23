<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2018/1/231
 * Time: 10:40
 */
namespace app\manage\controller;

use think\Db;

class Question extends Base{

    public function index(){

        $list = Db::table('question a')
            ->join('course b','a.courseid=b.id','LEFT')
            ->field('a.*,b.title as name')
            ->paginate(20);

        $course = Db::table('course')->select();

        $qtype = [
            ['id'=>1,'name'=>'单选题','type'=>'single_choice'],
            ['id'=>2,'name'=>'多选题','type'=>'choice'],
            ['id'=>3,'name'=>'判断题','type'=>'determine'],
            ['id'=>4,'name'=>'问答题','type'=>'essay'],
        ];


        $this->assign('list',$list);
        $this->assign('page',$list->render());
        $this->assign('course',$course);
        $this->assign('typename','题库管理');
        $this->assign('qtype',$qtype);
        $this->assign('uid',session('admin_uid'));

        return $this->fetch();
    }
}