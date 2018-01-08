<?php
/**
 * Created by PhpStorm.
 * User: M'S
 * Date: 2017/12/26
 * Time: 15:52
 */
namespace app\manage\controller;

use think\Db;

class StudentEnroll2 extends Base{
    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['admission'])){
            $where['admissionID'] = ['eq',$info['admission']];

        }
        if(!empty($info['category'])){
            $where['categoryID'] = ['eq' ,$info['category']];
        }
        if(!empty($info['starttime']) && !empty($info['endtime'])){
            $where['a.createTime'] = ['between time',[$info['starttime']." 00:00:00", $info['endtime']." 23:59:59"]];
        }

        $list = Db::table('student_enroll')
            ->alias('a')
            ->join('category b','a.categoryID=b.code','LEFT')
            ->join('admission c','a.admissionID=c.id','LEFT')
            ->where($where)
            ->field('a.id,a.realname,a.sex,a.telephone as phone,b.name,a.createTime,a.age,a.promotMan,a.admissionID,a.school,a.address,c.title')->paginate(20,false,['query'=>request()->get()]);

//        echo Db::table('student_enroll')->getLastSql();exit;

        $category = Db::table('category')->field('code,name')->where('Flag','eq',1)->select();

        $admission = Db::table('admission')->field('id,title')->select();
        $this->assign('typename','报名管理');

        $this->assign('list',$list);
        $this->assign('admission',$admission);
        $this->assign('categorylist',$category);
        $this->assign('page',$list->render());

        return $this->fetch();
    }

    public function accept(){
        $id = $_GET['rid']+0;

        if($_GET['type']==2){
            $s['status'] = 2;
        }else{
            $s['status'] = 1;
        }

        $ok = Db::name('student_enroll')->field('status')->where('id',$id)->update($s);

        if($ok){
            return ['info'=>'授理成功','code'=>'000'];
        }else{
            return ['error'=>'授理失败','code'=>'200'];
        }

    }
}