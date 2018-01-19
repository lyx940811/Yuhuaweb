<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/28
 * Time: 10:20
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Certificate extends Base{
    public function index(){

        $info = input('get.');
        $data['category']='';
        $data['name']='';
        $where = [];

        if(!empty($info['category'])){
            $data['category']=$info['category'];
            $where['d.id'] = ['eq',$info['category']];
        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['c.name'] = ['like',"%{$info['name']}%"];
        }

        $list = Db::table('certificate a')
            ->join('user_profile b','a.profileid=b.id','LEFT')
            ->join('categorycertificate c','a.certificateid=c.id','LEFT')
            ->join('category d','c.categoryID=d.code','LEFT')
            ->field('a.id,a.pic,b.realname,b.idcard,b.id as bid,d.name,c.sn,c.name as cname,c.level,c.unit,a.createtime,a.pic,d.id as did')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);

        $userprofile = Db::table('user_profile')->field("id,realname")->select();
        $category = Db::table('categorycertificate')->alias('a')->join('category b','a.categoryID=b.code','LEFT')->field("a.id,b.name")->select();

        $this->assign('typename','证书记录');
        $this->assign('list',$list);
        $this->assign('userprofile',$userprofile);
        $this->assign('info',$data);
        $this->assign('category',$category);
        $this->assign('page',$list->render());

        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'userprofile.require' => '姓名不能为空',
            'userprofile.number' => '姓名必须为数字',
            'category.require' => '请选择证书',
        ];
        $validate = new Validate([
            'userprofile'  => 'require|number',
            'category'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('certificate');

        $data = [
            'profileid' => $info['userprofile'],
            'certificateid' => $info['category'],
            'pic' => $info['pic'],
            'createtime'=>date('Y-m-d H:i:s',time()),
//            'Flag'=>1,
        ];

        $ok = $role_table->field('profileid,certificateid,pic,createtime,Flag')->insert($data);

        if($ok){
            manage_log('106','003','添加证书',serialize($data),0);
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    public function edit(){
        //前台先获取资料
        if(isset($_GET['do'])=='get'){
            $id = $_GET['rid']+0;

            $have = Db::name('certificate')->field('id,profileid,certificateid,pic')->where("id='$id'")->find();

            if(!$have){//如果这个code有
                return ['error'=>'没有此专业证书','code'=>'300'];
            }else{
                return ['info'=>$have,'code'=>'000'];
            }

        }

        $info = input('post.');

        $msg  =   [
            'userprofile.require' => '姓名不能为空',
            'userprofile.number' => '姓名必须为数字',
            'category.require' => '请选择证书',
        ];
        $validate = new Validate([
            'userprofile'  => 'require|number',
            'category'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('certificate');

        $id = $info['rid']+0;
        $have = $role_table->field('id,pic')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此证书记录','code'=>'300'];
        }

        if(!empty($info['pic'])){
            $pic =$info['pic'];
        }else{
            $pic = $have['pic'];
        }

        $data = [
            'profileid' => $info['userprofile'],
            'certificateid' => $info['category'],
            'pic' => $pic,
            'createtime'=>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->field('profileid,certificateid,pic,createtime')->where('id',$id)->update($data);

        if($ok){
            manage_log('106','004','修改证书',serialize($data),0);
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'400'];
        }
    }

//    public function upload(){
//
//        $id = $_GET['id'];
//
//        $file = upload('newfile'.$id,'certificate');
//        return $file;
//
//    }
    public function upload(){

        $id = $_GET['id']+0;
        $file = new Upload();
        $res = $file->uploadPic($_FILES,'teacherinfo');

        $res['path'] = $res['newfile'.$id]['path'];
        $res['code'] = $res['newfile'.$id]['code'];
        return $res;
    }
}