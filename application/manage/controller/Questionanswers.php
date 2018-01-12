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

class Questionanswers extends Base{

	//问卷 问列表
	public function question(){
        $info = input('get.');
        $search='';
        $where = [];
        if(!empty($info['name'])){
            $search=$info['name'];
            $where['a.title'] = ['like',"%{$info['name']}%"];
        }
        $list = DB::table('asklist')
        	->alias('a')
        	->join('user u','a.userid=u.id')
        	->field('a.*,u.username')
        	->order('addtime')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('page',$list->render());
        return $this->fetch();
	}

	//禁用
	public function disableQuestion(){
		$id=$this->request->param('id');
		$type=$this->request->param('type');
		$list = DB::table('asklist')->where('id',$id)->update(['status'=>$type]);
		if($list){
			echo 1;
		}

	}

	// 问卷 答列表
	public function answers(){
        $info = input('get.');
        $search='';
        $where=[];
        if(!empty($info['name'])){
            $search=$info['name'];
            $where['u.username'] = ['like',"%{$info['name']}%"];
        }
		$id=$this->request->param('id');
		$where['aa.askid']=$id;
		$list = DB::table('ask_answer')
			->alias('aa')
			->join('user u','aa.answerUserID=u.id','LEFT')
			->join('asklist a','aa.askid=a.id')
			->field('aa.*,u.username,a.title,a.content as askcontent')
			->where($where)
			->order('addtime')
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('id',$id);
        $this->assign('page',$list->render());
        return $this->fetch();
		
	}

	//禁用
	public function disableAnswers(){
		$id=$this->request->param('id');
		$type=$this->request->param('type');
		$list = DB::table('ask_answer')->where('id',$id)->update(['status'=>$type]);
		if($list){
			echo 1;
		}
	}

	//设置是否精华
	public function essenceAnswers(){
		$id=$this->request->param('id');
		$type=$this->request->param('type');
		$list = DB::table('ask_answer')->where('id',$id)->update(['goodFlag'=>$type]);
		if($list){
			echo 1;
		}
	}

}
