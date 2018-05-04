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
//    public function index(){
//        $info=input('get.');
//        $data['name']='';
//        $data['type']='';
//        $where='';
//        if(!empty($info['type'])){
//            $data['type']=$info['type'];
//            $where['a.type']=$info['type'];
//        }
//        if(!empty($info['name'])){
//            $data['name']=$info['name'];
//            $where['a.filename']=['like',"%{$info['name']}%"];//由于0的特殊性，页面搜索数据全部加1
//        }
//        $list = Db::table('course_file')
//            ->alias('a')
//            ->field('a.id,a.filename,a.filepath,a.filesize,a.type,a.courseid,a.lessonid,b.title')
//            ->join('course b','a.courseid=b.id','LEFT')
//            ->where($where)
//            ->paginate(20,false,['query'=>request()->get()]);
//
//        $this->assign('info',$data);
//        $this->assign('list',$list);
//        $this->assign('typename','课程资料');
//        $this->assign('page',$list->render());
//        return $this->fetch();
//    }
    public function index(){
        $info=input('get.');
        $course='';
        $data['name']='';
        $data['type']='';
        $data['category']='';
        $data['course']='';
        $where='';
        if(!empty($info['type'])){
            $data['type']=$info['type'];
            $where['ct.type']=$info['type'];
        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['ct.filename']=['like',"%{$info['name']}%"];//由于0的特殊性，页面搜索数据全部加1
        }

        if(!empty($info['course'])){
            $data['course']=$info['course'];
            $where['c.id']=$info['course'];
        }
        $array=['exam','test','plan','url'];
        $list = Db::table('course_task ct')
            ->join('course c','ct.courseid=c.id','LEFT')
            ->field('ct.*,c.id,c.categoryId,c.title as ctitle')
            ->where('ct.type','not in',$array)
            ->where($where);
        if(!empty($info['category'])){
            $data['category']=$info['category'];
            $category=$info['category'];
            $category1=','.$info['category'].',';
            $list=$list->where(function ($query)use($category,$category1) {
                $query->where('categoryId','like','%'.$category1.'%')->whereor('categoryID',$category);
            });
            $course=DB::table('course')->where('status',1)->where('categoryId','like','%'.$info['category'].'%')->select();
        }
        $list=$list->paginate(20,false,['query'=>request()->get()]);
        $newlist=[];
        foreach($list as $k=>$v){
            $newlist[$k]=$v;
            $categoryids=explode(',',ltrim(rtrim($v['categoryId'],",")));
            $category=DB::table('category')->where('code','in',$categoryids)->where('Flag',1)->column('name');
            $newlist[$k]['categorysname']=implode(',',$category);
        }
        $category=DB::table('category')->where('Flag',1)->select();
        $this->assign('info',$data);
        $this->assign('course',$course);
        $this->assign('list',$newlist);
        $this->assign('category',$category);
        $this->assign('typename','课程资料');
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    //查询专业的课程
    public function coursecategoryid(){
        $categoryid=$this->request->param('categoryid');
        $data=DB::table('course')->where('status',1)->where('categoryId','like','%'.$categoryid.'%')->select();
        if(!empty($data)) {
            return $data;
        }else{
            return 0;
        }
    }
}