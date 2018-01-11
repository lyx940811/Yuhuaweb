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

class Course extends Base{

    public function index(){

        $list = Db::table('course')
            ->field('id,title,subtitle,categoryId,tags,about,smallPicture,price,serializeMode,studentNum,status,userid')
            ->paginate(20);


        $category = Db::table('category')->field('code,name')->order('grade desc')->select();
        $tags = Db::table('tag')->select();

        $this->assign('list',$list);
        $this->assign('typename','课程列表');
        $this->assign('category',$category);
        $this->assign('tags',$tags);
        $this->assign('page',$list->render());
        return $this->fetch();
    }


    public function add(){
        $info = input('post.');

        $msg  =   [
            'title.require' => '课程名称不能为空',
            'title.length' => '课程名称长度太短',
            'categoryId.require' => '专业不能为空',
        ];
        $validate = new Validate([
            'title'  => 'require|length:2,20',
            'categoryId'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('course');

        $data = [
            'title'         => $info['title'],
            'subtitle'      => $info['subtitle'],
            'tags'          => $info['tags'],
            'categoryId'    => $info['categoryId'],
            'serializeMode' => $info['serializeMode'],
            'status'=> 1,
            'smallPicture'  => $info['pic'],
            'userid'        => session('admin_uid'),
            'about'        => $info['about'],
            'createdTime'   =>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->field('title,subtitle,tags,categoryId,serializeMode,status,smallPicture,userid,about,createdTime')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    public function edit(){

        $info = input('post.');

        $msg  =   [
            'title.require' => '课程名称不能为空',
            'title.length' => '课程名称长度太短',
            'categoryId.require' => '专业不能为空',
        ];
        $validate = new Validate([
            'title'  => 'require|length:2,20',
            'categoryId'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('course');

        $id = $info['rid']+0;
        $have = $role_table->field('id,smallPicture')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此课程','code'=>'300'];
        }

        if(!empty($info['pic'])){
            $data['smallPicture'] =$info['pic'];
        }else{
            $data['smallPicture'] = $have['smallPicture'];
        }

        $data = [
            'title'         => $info['title'],
            'subtitle'      => $info['subtitle'],
            'tags'          => $info['tags'],
            'categoryId'    => $info['categoryId'],
            'serializeMode' => $info['serializeMode'],
            'userid'        => session('admin_uid'),
            'about'        => $info['about'],
//            'createdTime'   =>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->field('title,subtitle,tags,categoryId,serializeMode,smallPicture,userid,about')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('course')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

    public function upload(){

        $id = $_GET['id']+0;

        $file = upload('newfile'.$id,'course');
        return $file;

    }
}