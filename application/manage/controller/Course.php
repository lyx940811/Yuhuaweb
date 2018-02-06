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
        $info=input('get.');
        $data['status']='';
        $data['serializeMode']='';
        $data['name']='';
        $where='';
        if(!empty($info['status'])){
            $data['status']=$info['status'];
            $where['c.status']=$info['status']-1;//由于0的特殊性，页面搜索数据全部加1
        }
        if(!empty($info['serializeMode'])){
            $data['serializeMode']=$info['serializeMode'];
            $where['c.serializeMode']=$info['serializeMode'];
        }
        if(!empty($info['name'])){
            $data['name']=$info['name'];
            $where['c.title']=['like',"%{$info['name']}%"];//由于0的特殊性，页面搜索数据全部加1
        }
        $list = Db::table('course c')
            ->field('c.*,tf.realname')
            ->join('teacher_info tf','c.teacherIds=tf.userid','LEFT')
            ->where($where)
            ->order('c.id desc')
            ->paginate(20,false,['query'=>request()->get()]);


        $category=$this->subtree(0);

        $tags = Db::table('tag')->select();
        $teacher = Db::table('teacher_info')->field('userid,realname')->select();

        $newlist = [];
        foreach ($list as $k=>$v){
            $newlist[$k] = $v;
            $newlist[$k]['num'] = Db::table('study_result')->where('courseid='.$v['id'])->group('userid')->count();
            $total=Db::table('course_task')
                ->where('courseid',$v['id'])
                ->field('sum(point) as point')
                ->find();
            $newlist[$k]['point'] = $total['point'];
        }
//        print_r($newlist);exit;

        $this->assign('info',$data);
        $this->assign('list',$newlist);
        $this->assign('typename','课程列表');
        $this->assign('category',$category);
        $this->assign('teacher',$teacher);
        $this->assign('tags',$tags);
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    //递归查找无线分类
    public function subtree($id=0) {
        $arr = Db::table('category')->field('code,name,parentcode,grade')->where('parentcode',$id)->select();
        $subs = array(); // 子孙数组
        foreach($arr as $v) {
            if($v['parentcode'] == $id) {
//                $v['lev'] = $lev;
                $subs[] = $v; // 举例说找到array('id'=>1,'name'=>'安徽','parent'=>0),
//                dump($v['parentcode']);
                $subs = array_merge($subs,$this->subtree($v['code']));
            }
        }
        return $subs;
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
            'status'=>$info['status'],
            'smallPicture'  => $info['pic'],
            'userid'        => session('admin_uid'),
            'about'        => $info['about'],
            'createdTime'   =>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->insert($data);

        if($ok){
            manage_log('108','003','添加课程',serialize($data),0);
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

        if(isset($info['pic'])){
            $pic=$info['pic'];
        }else{
            $pic = $have['smallPicture'];
        }

        $data = [
            'title'         => $info['title'],
            'subtitle'      => $info['subtitle'],
            'tags'          => $info['tags'],
            'categoryId'    => $info['categoryId'],
            'serializeMode' => $info['serializeMode'],
            'userid'        => session('admin_uid'),
            'about'        => $info['about'],
            'status'       =>$info['status'],
            'smallPicture'=>$pic,
//            'createdTime'   =>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->where('id',$id)->update($data);

        if(is_numeric($ok)){
            manage_log('108','004','修改课程',serialize($data),0);
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('course')->where("id='$id'")->delete();

        if($ok){
            manage_log('108','005','删除课程',serialize(['id'=>$id]),0);
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

    public function upload(){

        $id = $_GET['id']+0;

        $upload = new Upload();

        $file = $upload->uploadPic($_FILES,'course');

        $file['path'] = $file['newfile'.$id]['path'];
        $file['code'] = $file['newfile'.$id]['code'];
        unset($file['newfile'.$id]);
//        $file = upload('newfile'.$id,'course');
        return $file;

    }

    public function select(){
       $info = input('get.');


        $ok = Db::name('course')->where("id='{$info['id']}'")->update(['teacherIds'=>$info['teacherIds']]);

        if($ok){
//            manage_log('108','005','删除课程',serialize(['id'=>$id]),0);
            return ['info'=>'选择成功','code'=>'000'];
        }else{
            return ['error'=>'选择失败','code'=>'200'];
        }
    }
}