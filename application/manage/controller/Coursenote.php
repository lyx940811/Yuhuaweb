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

class Coursenote extends Base{

    public function index(){
        $info=input('get.');
        $data['username']='';
        $data['classname']='';
        $where='';
        if(!empty($info['classname'])){
            $data['classname']=$info['classname'];
            $where['b.title']=['like',"%{$info['classname']}%"];//由于0的特殊性，页面搜索数据全部加1
        }
        if(!empty($info['username'])){
            $data['username']=$info['username'];
            $where['u.username']=['like',"%{$info['username']}%"];//由于0的特殊性，页面搜索数据全部加1
        }
        $list = Db::table('course_note a')
            ->join('course b','a.courseId=b.id','LEFT')
            ->join('user u','a.userid=u.id','LEFT')
            ->field('a.id,a.userid,a.courseId,a.content,a.likeNum,a.createdTime,b.title,u.username')
            ->order('createdTime desc')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $this->assign('info',$data);
        $this->assign('list',$list);
        $this->assign('typename','笔记管理');
        $this->assign('page',$list->render());
        return $this->fetch();
    }


}