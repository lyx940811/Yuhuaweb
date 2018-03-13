<?php
/**
 * Created by PhpStorm.
 * User: M'S
 * Date: 2017/12/26
 * Time: 15:52
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;
use think\Requst;

class Notice extends Base{
    public function index(){
        $info = input('get.');
        $time=date('Y-m-d H:i:s');
        $where=[];
        $search=[
            'status'=>'',
            'title'=>'',
        ];
        if(!empty($info['status'])){
            $search['status']=$info['status'];
            if($info['status']==3){
                $where['endtime']=array('lt',$time);
            }else{
                $where['status']=$info['status'];
                $where['endtime']=array('gt',$time);
            }
        }
        if(!empty($info['title'])){
            $search['title']=$info['title'];
            $where['c.title']=['like',"%{$info['title']}%"];
        }
        $list=DB::table('course_notice cn')
            ->join('course c','cn.courseid=c.id')
            ->field('cn.*,c.title as ctitle')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);
        $course = Db::table('course')->where('teacherIds',session('admin_uid'))->select();

        $this->assign('time',$time);
        $this->assign('list',$list);
        $this->assign('course',$course);
        $this->assign('page',$list->render());
        $this->assign('search',$search);
        return $this->fetch();
    }
    public function addedit(){
        $info = input('get.');

        $msg  =   [
            'courseid.require' => '请选择发送公告的课程',
//            'title.require' => '请输入公告标题',
            'content.require' => '请输入公告内容',
            'endtime.require' => '请输入结束时间',
        ];
        $validate = new Validate([
            'courseid'  => 'require',
//            'title'   => 'require',
            'content'   => 'require',
            'endtime'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则
        $data=[
            'courseid'=>$info['courseid'],
//            'title'=>$info['title'],
            'content'=>$info['content'],
            'endtime'=>$info['endtime'],
            'status'=>$info['status'],
        ];
        if(!empty($info['rid'])){
            $type=DB::table('course_notice')->where('id',$info['rid'])->update($data);
        }else{
            $data['createdtime']=date('Y-m-d H:i:s');
            $data['starttime']=date('Y-m-d H:i:s');
            $type=DB::table('course_notice')->insert($data);
        }
        if(is_numeric($type)){
            return ['info'=>'编辑成功','code'=>'000'];
        }else{
            return ['error'=>'编辑失败','code'=>'200'];
        }
    }

    public function selectedit(){
        $id=$this->request->param('id');
        $list=DB::table('course_notice')->where('id',$id)->find();
        return $list;
    }

    public function delete(){
        $id=$this->request->param('rid');
        $type=DB::table('course_notice')->where('id',$id)->delete();
        if(is_numeric($type)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }

    public function release(){
        $id=$this->request->param('id');
        $type=$this->request->param('type');
        if($type==1){
            $info=DB::table('course_notice')->where('id',$id)->update(['status' => 1]);
        }else{
            $list=Db::table('student_notice')->where('id',$id)->find();
            if(empty($list)){
                $courseid=DB::table('course_notice')->where('id',$id)->find();
                $majorsid = Db::name('course')->where('id',$courseid['courseid'])->value('categoryId');
                $allstudent = Db::table('student_school')->where('majors', $majorsid)->column('userid');
                if(!empty($allstudent)){
                    $data=[
                        'coursenoticeID'=>$id,
                        'content'=>$courseid['content'],
                    ];
                    foreach($allstudent as $key=>$value){
                        $data['toUserid']=$value;
                        Db::table('student_notice')->insert($data);
                    }
                }
            }
            $info=DB::table('course_notice')->where('id',$id)->update(['status' => 2]);
        }
        if(is_numeric($info)){
            return ['info'=>'成功','code'=>'000'];
        }else{
            return ['error'=>'失败','code'=>'200'];
        }
    }
}