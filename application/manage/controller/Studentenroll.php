<?php
/**
 * Created by PhpStorm.
 * User: M'S
 * Date: 2017/12/26
 * Time: 15:52
 */
namespace app\manage\controller;

use think\Db;

class StudentEnroll extends Base{
    public function index(){

        $info = input('get.');

        if(isset($info)){

        }
        $list = Db::table('student_enroll')
            ->alias('a')
            ->join('category as b','a.categoryID=b.code','LEFT')
            ->where("")
            ->field('a.id,a.realname,a.sex,a.phone,a.admissionID,b.name,a.createTime')->paginate(20,false,['query'=>request()->get()]);


        $category = Db::table('category')->field('code,name')->where('Flag','eq',1)->select();

        $this->assign('typename','专业报名数据查询');

        $this->assign('list',$list);
        $this->assign('categorylist',$category);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
}