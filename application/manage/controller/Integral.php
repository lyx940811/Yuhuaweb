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
use think\Requst;

class Integral extends Base{

    //积分规则
    public function index(){
        $info = input('get.');
        $search='';
        $where = [];
        if(!empty($info['name'])){
            $search=$info['name'];
            $where['title'] = ['like',"%{$info['name']}%"];
        }
        $where['flag']=0;
        $list = DB::table('reward_point_rule')
            ->where($where)
            ->order('createdTime desc')
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
    //积分规则添加
    public function add(){
        $info = input('post.');
        $msg  =   [
            'title.require' => '请输入动作名称',
            'daymax.require' => '请输入每日上限',
        ];
        $validate = new Validate([
            'title'  => 'require',
            'daymax'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }
        $data['title']=$info['title'];
        $data['point']=$info['point'];
       
        $data['daymax']=$info['daymax'];
        $data['maxflag']=$info['maxflag'];
        if(!empty($info['id'])){
            $type=Db::table('reward_point_rule')->where('id', $info['id'])->update($data);
        }else{
            $data['createdTime']=date('Y-m-d H:i:s');
            $type=DB::table('reward_point_rule')->insert($data);
        }
        if(is_numeric($type)){
            return ['info'=>'编辑成功','code'=>'000'];
        }else{
            return ['error'=>'编辑失败','code'=>'200'];
        }

    }

    //积分规则修改时显示默认数据
    public function indexEdit(){
        $info = input('get.');
        $list = DB::table('reward_point_rule')->where('id',$info['id'])->find();
        return ['list'=>$list];
    }
    //积分规则删除
    public function indexDel(){
        $id=$this->request->param('id');
        $type=Db::table('reward_point_rule')->where('id', $id)->update(['flag' => 1]);
        if($type){
            echo 1;
        }else{
            echo 2;
        }
    }
    //积分等级方法
    public function integralGrade(){

        $info = input('get.');
        $search='';
        $where = [];
        if(!empty($info['name'])){
            $search=$info['name'];
            $where['name'] = ['like',"%{$info['name']}%"];
        }
        $list = DB::table('reward_point_level')
            ->where($where)
            ->order('createTime desc')
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据

        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
    //添加/修改积分等级w
    public function gradeAdd(){
        $info = input('post.');
        $msg  =   [
            'name.require' => '请输入等级名称',
            'point.require' => '请输入积分下限',
            'maxpoint.require' => '请输入积分上限',
            'icon.require' => '请上传图片',
        ];
        $validate = new Validate([
            'name'  => 'require',
            'point'   => 'require',
            'maxpoint'  => 'require',
            'icon'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }
        $data['name']=$info['name'];
        $data['point']=$info['point'];
        $data['maxpoint']=$info['maxpoint'];
        $data['icon']=$info['icon'];
        $data['flag']=$info['flag'];
        $data['code']='1111';
        if(!empty($info['id'])){
            $type=Db::table('reward_point_level')->where('id', $info['id'])->update($data);
        }else{
            $data['createTime']=date('YmdHis');
            $type=DB::table('reward_point_level')->insert($data);
        }
        if(is_numeric($type)){
            return ['info'=>'编辑成功','code'=>'000'];
        }else{
            return ['error'=>'编辑失败','code'=>'200'];
        }
    }

    //查询积分等级修改是显示数据
    public function gradeEdit(){
        $info = input('get.');
        $list = DB::table('reward_point_level')->where('id',$info['id'])->find();
        return ['list'=>$list];
    }

    //积分等级图片上传
    public function img(){
        $file =$_FILES;
        $upload = new Intergralupload();
        $info = $upload->uploadPic($file);
        if($info){
            echo($info['upload_file0']);
        }
         
    }

    //商品图片上传
    public function shopImg(){
        $file =$_FILES;
        $upload = new Intergralupload();
        $info = $upload->uploadPic($file);
        if($info){
            echo($info['upload_file0']);
        }
    }
    //积分列表
    public function integralList(){
        //接收从学生列表传过来的userid
        $userid=$this->request->param('id')+0;
        $info = input('get.');
        $search='';
        $where = [];
        if(!empty($info['name'])){
            $search=$info['name'];
            $where['u.username'] = ['like',"%{$info['name']}%"];
        }
        if($userid){
            $where['u.id']=$userid;
        }
        $where['rpf.type'] = 'outflow';
        $list = DB::table('user')
            ->alias('u')
            ->join('user_profile up','u.id=up.userid')
            ->join('reward_point_flow rpf','u.id=rpf.userid')
            ->join('reward_point rp','u.id=rp.userid')
            ->field('rp.*,u.username,up.sn,sum(rpf.point) as point')
            ->group('rpf.userid')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
    //积分列表详情
    public function integralDtail(){
        $userid=$this->request->param('id');
        $where=[];
        $where['rpf.userid']=$userid;
        $list = DB::table('user')
            ->alias('u')
            ->join('user_profile up','u.id=up.userid')
            ->join('reward_point_flow rpf','u.id=rpf.userid')
            ->field('rpf.*,u.username,up.sn')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();

    }
    //积分商城
    public function integralShoppM(){
         $info = input('get.');
        $search='';
        $where = [];
        if(!empty($info['name'])){
            $search=$info['name'];
            $where['title'] = ['like',"%{$info['name']}%"];
        }
        $list = DB::table('reward_point_product')
            ->where($where)
            ->order('createdTime desc')
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $type=DB::table('product_type')->select();
        $this->assign('type',$type);
        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
    //积分商品添加
    public function shopAdd(){
        $info = input('get.');
        $msg  =   [
            'sn.require' => '请输入商品编号',
            'title.require' => '请输入商品名称',
            'price.require' => '请输入商品积分',
            'img.require' => '请上传图片',
        ];
        $validate = new Validate([
            'sn'  => 'require',
            'title'   => 'require',
            'price'  => 'require',
            'img'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }
        $data['userid']=session('admin_uid');
        $data['sn']=$info['sn'];
        $data['title']=$info['title'];
        $data['img']=$info['icon'];
        $data['type']=$info['type'];
        $data['price']=$info['price'];
        $data['status']=$info['status'];
         if(!empty($info['id'])){
            $type=Db::table('reward_point_product')->where('id', $info['id'])->update($data);
        }else{
            $data['createdTime']=date('YmdHis');
             $type=DB::table('reward_point_product')->insert($data);
        }
        if(is_numeric($type)){
            return ['info'=>'编辑成功','code'=>'000'];
        }else{
            return ['error'=>'编辑失败','code'=>'200'];
        }
    }
    //积分商品修改显示默认值查询
    public function shoppMEdit(){
        $info = input('get.');
        $list = DB::table('reward_point_product')->where('id',$info['id'])->find();
        return ['list'=>$list];
    }
    //兑换记录
    public function integralExchange(){
        $info = input('get.');
        $search='';
        $where = [];
        if(!empty($info['name'])){
            $search=$info['name'];
            $where['u.username'] = ['like',"%{$info['name']}%"];
        }
        $where['rpf.type'] = 'outflow';
        $list = DB::table('user')
            ->alias('u')
            ->join('user_profile up','u.id=up.userid')
            ->join('reward_point_flow rpf','u.id=rpf.userid')
            ->field('rpf.userid,rpf.title,u.username,up.sn,sum(rpf.point) as pointsum,count(rpf.title) as num,rpf.createTime')
            ->group('rpf.userid,rpf.title')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
    //兑换记录详情
    public function exchangeDetail(){
        $userid=$this->request->param('id');
        $title=$this->request->param('title');
        $where['rpf.type'] = 'outflow';
        $where['rpf.title'] = $title;
        $where['rpf.userid'] = $userid;
        $list = DB::table('user')
            ->alias('u')
            ->join('user_profile up','u.id=up.userid')
            ->join('reward_point_flow rpf','u.id=rpf.userid')
            ->field('rpf.*,u.username,up.sn,rpf.createTime')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $this->assign('list',$list);
        $this->assign('page',$list->render());
        return $this->fetch();
    }
  
}