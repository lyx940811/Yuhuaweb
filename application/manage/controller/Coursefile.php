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
        $info=input('get.');
        $data['name']='';
        $data['type']='';
        $where='';
        if(!empty($info['type'])){
            $data['type']=$info['type'];
            $where['a.type']=$info['type'];
        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['a.filename']=['like',"%{$info['name']}%"];//由于0的特殊性，页面搜索数据全部加1
        }
        $list = Db::table('course_file')
            ->alias('a')
            ->field('a.id,a.filename,a.filepath,a.filesize,a.type,a.courseid,a.lessonid,b.title')
            ->join('course b','a.courseid=b.id','LEFT')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $this->assign('info',$data);
        $this->assign('list',$list);
        $this->assign('typename','课程资料');
        $this->assign('page',$list->render());
        return $this->fetch();
    }
}