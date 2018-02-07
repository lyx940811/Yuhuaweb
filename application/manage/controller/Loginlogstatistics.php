<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/2/6
 * Time: 14:10
 */
namespace app\manage\controller;

use think\Db;

class Loginlogstatistics extends  Base{

    public function index(){

        $in = input('get.');

        $where = $where2 = [];
        if(!empty($in['role'])){
            $where['roles'] = ['eq',$in['role']];
        }
        if(!empty($in['start']) && !empty($in['end'])){
            $where['LoginTime'] = ['between time',[$in['start'],$in['end']]];
        }

        if(!empty($in['title'])){
            $where['username|nickname'] = ['like',"%{$in['title']}%"];
        }

        $list = Db::table('user a')
            ->join('role b','a.roles=b.id','LEFT')
            ->join('user_login_log c','a.id=c.userid','LEFT')
            ->field('a.id,a.username,a.nickname,a.roles,a.type,b.name as rolename')
            ->where($where)
            ->group('a.id')
            ->paginate(20);

        $newarr = [];
        foreach ($list as $k=>$v){
            $newarr[$k] = $v;
            if($v['type']==2){//为教师的时候取教师的表
                $newarr[$k]['realname'] = Db::table('teacher_info')->where('userid',$v['id'])->value('realname');
            }
            elseif($v['type']==3){//为教师的时候取教师的表
                $newarr[$k]['realname'] = Db::table('user_profile')->where('userid',$v['id'])->value('realname');
            }else{
                $newarr[$k]['realname'] = $v['nickname'];
            }

            $newarr[$k]['LoginTime'] = Db::table('user_login_log')->where('userid',$v['id'])->order('LoginTime desc')->value('LoginTime');
            $newarr[$k]['totalLoginNum'] = Db::table('user_login_log')->where('userid',$v['id'])->field('count(id) as num,sum(loginAllTime) as alltime')->find();
        }


        $role = Db::table('role')->field('id,name,code')->select();


        $this->assign('list',$newarr);
        $this->assign('page',$list->render());
        $this->assign('role',$role);
        $this->assign('typename','登陆日志统计');
        $this->assign('uid',session('admin_uid'));
        return $this->fetch();
    }

    public function show(){
        $id = request()->get('id')+0;

        $a = Db::table('user a')
            ->join('user_login_log b','a.id=b.userid','LEFT')
            ->join('role c','a.roles=c.id','LEFT')
            ->field('a.username,a.nickname,c.name')
            ->where('a.id',$id)->find();

        $list = Db::table('user_login_log')->where('userid',$id)->paginate(20);

        $newarr = [];
        foreach ($list as $k=>$v){
            $newarr[$k] = $v;
            $newarr[$k]['totalLoginNum'] = Db::table('user_login_log')->where('userid',$id)->field('count(id) as num,sum(loginAllTime) as alltime')->find();
        }

        $this->assign('a',$a);
        $this->assign('list',$newarr);
        $this->assign('page',$list->render());
        $this->assign('typename','登陆记录');
        $this->assign('uid',session('admin_uid'));
        return $this->fetch();
    }
}