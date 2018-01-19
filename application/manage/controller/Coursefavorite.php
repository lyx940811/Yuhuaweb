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
        $list = Db::table('course_favorite')
            ->alias('a')
            ->field('a.id,a.url,a.createTime,a.userid,b.title,u.username')
            ->join('course b','a.courseid=b.id','LEFT')
            ->join('user u','a.userid=u.id')
            ->order('a.id desc')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $this->assign('info',$data);

        $this->assign('list',$list);
        $this->assign('typename','课程收藏记录');
        $this->assign('page',$list->render());
        return $this->fetch();
    }
}