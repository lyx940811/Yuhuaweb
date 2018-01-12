<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/25
 * Time: 11:43
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;
/*
 * 广告管理1
 */
class Ad extends Base{

    public function index(){

        $info = input('get.');

        $where = [];

        if(!empty($info['title'])){

            $where['a.title'] = ['like',"%{$info['title']}%"];
        }

        $list = Db::name('ad a')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);


        $type = [['id'=>1,'name'=>'mobile'],['id'=>2,'name'=>'pc']];

//        print_r($type);exit;
        $this->assign('list',$list);
        $this->assign('page',$list->render());

        $this->assign('type',$type);
        $this->assign('typename','广告管理');

        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'title.require' => '名称不能为空',
            'title.length' => '名称长度太短',
            'img.require' => '图片不能为空',
        ];

        $validate = new Validate([
            'title'  => 'require|length:2,100',
            'img'  => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('ad');

        $data = [
            'title' => $info['title'],
            'url' => $info['url'],
            'img'=> $info['img'],
            'content'=>$info['content'],
            'type'=> $info['type'],
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'userid'=>session('admin_uid'),
//            'flag'=>1,
        ];

        $ok = $role_table->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){

        $info = input('post.');

        $msg  =   [
            'title.require' => '名称不能为空',
            'title.length' => '名称长度太短',
            'img.require' => '图片不能为空',
        ];

        $validate = new Validate([
            'title'  => 'require|length:2,100',
            'img'  => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('ad');

        $id = $info['rid']+0;

        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此班级','code'=>'300'];
        }

        $data = [
            'title' => $info['title'],
            'url' => $info['url'],
            'img'=> $info['img'],
            'content'=>$info['content'],
            'type'=> $info['type'],
//            'createdTime'=>date('Y-m-d H:i:s',time()),
//            'userid'=>session('admin_uid'),
        ];

        $ok = $role_table->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }


    public function upload(){

        $id = $_GET['id'];
//
        $upload = new Upload($_FILES);
        $file = $upload->uploadPic($_FILES);
//        print_r($ok);exit;

//        $file = upload('newfile'.$id,'ad');
        /*
         * mes	bf712a24e928905940bbb2f2b05c6d7f.jpg
           mes2	20180112\bf712a24e928905940bbb2f2b05c6d7f.jpg
           path	\uploads\ad\20180112\bf712a24e928905940bbb2f2b05c6d7f.jpg
           code	0
         */
        $file['path'] = "/".$file['newfile'.$id]['path'];
        $file['code'] = $file['newfile'.$id]['code'];
        unset($file['newfile'.$id]);//删除键，暂时不用多图上传
        return $file;

    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('ad')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }



}