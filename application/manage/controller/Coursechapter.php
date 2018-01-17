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

class Coursechapter extends Base{

    public function index(){
        $info = input('get.',NULL,'htmlspecialchars');

        $id = $this->request->param('cid');
        $where = [];
        $where['a.courseid'] = $id;
        if(!empty($info['title'])){

            $where['a.title'] = ['like',"%{$info['title']}%"];
        }

        $list = Db::table('course_chapter a')
            ->field('a.*')
//            ->join('course b','a.courseid=b.id','LEFT')
            ->where($where)
            ->order('createTime desc')
            ->paginate(20);
//echo Db::table('course_chapter a')->getLastSql();exit;
        $course = Db::table('course')->field('id,title')->where('id',$id)->find();
        $this->assign('list',$list);
        $this->assign('tit',$course['title']);
        $this->assign('typename',$course['title'].'-课程章节管理');
        $this->assign('page',$list->render());
        return $this->fetch();
    }


    public function add(){
        $info = input('post.',NULL,'htmlspecialchars');

        $msg  =   [
            'title.require' => '章节名称不能为空',
            'title.length' => '章节名称长度太短',
            'number.require' => '编号不能为空',
            'seq.require' => '序号不能为空',
        ];
        $validate = new Validate([
            'title'  => 'require|length:2,20',
            'number'   => 'require',
            'seq'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('course_chapter');

        $data = [
            'courseid'      => $info['courseid'],
            'type'          => 'chapter',
            'title'         => $info['title'],
            'number'        => $info['number'],
            'seq'           => $info['seq'],
            'flag'          => $info['flag'],
            'userid'        => session('admin_uid'),
            'createTime'   =>date('Y-m-d H:i:s',time()),
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
            'title.require' => '章节名称不能为空',
            'title.length' => '章节名称长度太短',
            'number.require' => '编号不能为空',
            'seq.require' => '序号不能为空',
        ];
        $validate = new Validate([
            'title'  => 'require|length:2,20',
            'number'   => 'require',
            'seq'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('course_chapter');

        $id = $info['rid']+0;

        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此课程章节','code'=>'300'];
        }

        $data = [
            'courseid'      => $info['courseid'],
            'title'         => $info['title'],
            'number'        => $info['number'],
            'seq'           => $info['seq'],
            'flag'          => $info['flag'],
//            'userid'        => session('admin_uid'),
//            'createTime'   =>date('Y-m-d H:i:s',time()),
        ];

        $ok = $role_table->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function delete(){

        $id = $_GET['rid']+0;

        $ok = Db::name('course_chapter')->where("id='$id'")->delete();

        if($ok){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

}