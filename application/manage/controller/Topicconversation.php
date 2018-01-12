<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/6
 * Time: 16:41
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;
use think\Request;

class Topicconversation extends Base{

	//问卷 问列表
	public function index(){
		
        $list = DB::table('article')
        	->alias('a')
        	->join('user u','a.createUserID=u.id')
        	->field('a.*,u.username')
        	->order('createtime')
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $this->assign('list',$list);
        // $this->assign('search',$search);
        $this->assign('page',$list->render());
        return $this->fetch();
	}

	//禁用
	public function disableTopic(){
		$id=$this->request->param('id');
		$type=$this->request->param('type');
		$list = DB::table('article')->where('id',$id)->update(['status'=>$type]);
		if($list){
			echo 1;
		}

	}



}
