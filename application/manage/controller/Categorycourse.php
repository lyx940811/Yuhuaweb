<?php
/**
 * Created by PhpStorm.
 * User: M'S
 * Date: 2017/12/25
 * Time: 17:42
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

/*
 * 四表联接  专业课程 专业  课程  教师
 */
class Categorycourse extends Base{

    public function index(){

        $info = input('get.');
        $data['category']='';
        $data['type']='';
        $data['name']='';
        $where = [];

        if(!empty($info['type'])){
            $data['type']=$info['type'];
            $where['c.type'] = ['eq',$info['type']-1];//由于0判断的特殊性，页面如果是0会默认选择，判断条件无效果。所以搜索时传过来的值比实际表里存的状态多加了1.
        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['c.title'] = ['like',"%{$info['name']}%"];
        }


//        $list = Db::table('categorycourse a')
//            ->field('a.id,c.id as cid,c.title,a.courseID,b.name,c.type,b.studyTimes,b.point,b.code,d.realname,a.Flag')
//            ->join('category b','a.categoryID = b.code','LEFT')
//            ->join('course c','a.courseID = c.id','LEFT')
//            ->join('teacher_info d','c.teacherIds = d.id','LEFT')
//            ->where($where)
//            ->paginate(20,false,['query'=>request()->get()]);
        $list=Db::table('course c')
                ->join('category cg','c.categoryId=cg.code','LEFT')
                ->join('teacher_info t','c.teacherIds = t.id','LEFT')
                ->field('c.id,c.categoryId,c.title,c.type,cg.studyTimes,cg.point,cg.code,t.realname')
                ->where($where);
        if(!empty($info['category'])){
            $data['category']=$info['category'];
            $majors=$info['category'];
            $majors1=','.$majors.',';
            $list=$list->where(function ($query)use($majors,$majors1) {
                $query->where('categoryId','like','%'.$majors1.'%')->whereor('categoryID',$majors);
            });
//            $where['c.categoryId'] = ['eq',$info['category']];
        }
        $list=$list ->paginate(20,false,['query'=>request()->get()]);
        $newlist=[];
        foreach($list  as $k=>$v){
            $newlist[$k]=$v;
            $categoryname=explode(',',ltrim(rtrim($v['categoryId'],",")));
            $category = Db::table('category')->where('code','in',$categoryname)->column('name');
            $newlist[$k]['categoryname']=implode($category,',');
        }
        $category = Db::table('category')->field('code,parentcode,name')->where('Flag','eq',1)->select();

        $categorylist = tree($category);


        $course = Db::table('course')->field('id,title')->where('status','eq',1)->select();

        $this->assign('typename','专业课程');
        $this->assign('list',$newlist);
        $this->assign('info',$data);
        $this->assign('categorylist',$categorylist);
        $this->assign('courselist',$course);
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    public function add(){

        /*
         * 这个表就2个关联的字段  categoryID 和课程id  所以只能修改这2个东西！！！！！！！！！！
         */
        $info = input('post.');

        $msg  =   [
            'code.require' => '课程名称不能为空',
            'code.number' => '课程名称必须为数字',
            'category.require' => '专业名称不能为空',
        ];

        $validate = new Validate([
            'code'   => 'require|number',
            'category'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('categorycourse');

        $data = [
            'categoryID'=>$info['category'],
            'courseID'=>$info['code'],
            'Flag'=>$info['flag'],
        ];
        /*
         * 这个表就2个关联的字段  categoryID 和课程id  所以只能修改这2个东西！！！！！！！！！！
         */
        $ok = $role_table->insert($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function edit(){

        /*
         * 这个表就2个关联的字段  categoryID 和课程id  所以只能修改这2个东西！！！！！！！！！！
         */
        $info = input('post.');

        $msg  =   [
            'rid.require' => '专业课程rid不能为空',
            'code.require' => '课程名称不能为空',
            'code.number' => '课程名称必须为数字',
            'category.require' => '专业名称不能为空',
        ];

        $validate = new Validate([
            'rid'    => 'require',
            'code'   => 'require|number',
            'category'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }
        $id = $info['rid']+0;

        $data = [
            'categoryId'=>$info['category'],
        ];
        $ok = Db::name('course')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }
}