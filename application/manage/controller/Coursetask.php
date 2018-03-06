<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/2
 * Time: 14:34
 */
namespace app\manage\controller;

use think\Db;
use request;
use think\Validate;

/*
 * 课程任务管理
 */
class Coursetask extends Base{

    public function index(){

        $info = input('get.');
        $where = [];
        $id=$this->request->param('cid');
        $where['a.courseid']= $id;
        if(!empty($info['title'])){

            $where['a.title'] = ['like',"%{$info['title']}%"];
        }

        $list = Db::table('course_task a')
            ->field('a.id,a.title,a.mode,a.point,a.maxPoint,a.isOptional,a.isFree,a.maxOnlineNum,a.courseId,a.chapterid,a.type,a.mediaSource,a.length,a.mediaSource,a.status,a.startTime,a.endTime,cc.title as ctitle')
            ->join('course_chapter cc','a.chapterid=cc.id','LEFT')
            ->where($where)
            ->order('a.id desc')
            ->paginate(20,false,['query'=>request()->get()]);

        $course = Db::table('course')->field('id,title')->where('id',$id)->find();

        $chapter = Db::table('course_chapter')->field('id,title')->where('courseid',$id)->select();
        $taskmode = Db::table('task_mode')->field('id,name')->select();
        $coursetask=Db::table('course_task')
            ->where(function ($query) {
                $query->where('type','test')->whereor('type','exam')->whereor('type','plan');
            })->where('paperid','<>','')->column('paperid');//查询已经用过的试卷的id
        $testpaper = Db::table('testpaper')->field('id,name')->where('courseid',$id)->where('id','not in',$coursetask)->select();

        $this->assign('testpaper',$testpaper);

        $this->assign('list',$list);
        $this->assign('chapter',$chapter);
        $this->assign('taskmode',$taskmode);
        $this->assign('tit',$course['title']);
        $this->assign('typename',$course['title'].'-课程任务');
        $this->assign('courseId',$id);
        $this->assign('page',$list->render());
        return $this->fetch();
    }


    public function add(){
        $info = input('post.');
        $msg  =   [
            'title.require'     => '任务名称不能为空',
            'title.length'      => '任务名称长度太短',
            'paperid.require'   =>'试卷不能为空',
            'courseId.require'  => '课程不能为空',
            'mode.require'  => '任务模式不能为空',
            'chapterid.require'  => '课程章必须选择',
//            'mediaSource.require'  => '媒体资源必须填写',
        ];
        $validate = new Validate([
            'title'     => 'require|length:2,20',
            'paperid'   =>'require',
            'courseId'  => 'require',
            'mode'  => 'require',
            'chapterid'  => 'require',
//            'mediaSource'  => 'require'

        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $type = isset($info['type'])?$info['type']:'url';
        $paperid = isset($info['paperid'])?$info['paperid']:0;
        if($type=='test' && !$paperid || $type=='exam' && !$paperid ){

            return ['error'=>'类型为测验或考试时，必须选择试卷','code'=>'200'];
        }

        $role_table = Db::name('course_task');

        $data = [
            'title' => $info['title'],
            'chapterid'=> $info['chapterid'],
            'isFree'=>isset($info['isFree'])?$info['isFree']:0,
            'point'=>$info['point'],
            'sort'=>$info['sort'],
            'paperid'=>$paperid,
            'mode'=>$info['mode'],
            'type'=>$type,
            'length'=>isset($info['length'])?$info['length']:0,
            'mediaSource'=>isset($info['mediaSource'])?$info['mediaSource']:'',
            'courseId'=>$info['courseId']+0,
            'createdUserId'=>session('admin_uid'),
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'status'=>isset($info['status'])?$info['status']:0,
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
            'rid'               =>'任务id不能为空',
            'title.require'     => '任务名称不能为空',
            'title.length'      => '任务名称长度太短',
            'paperid.require'   =>'试卷不能为空',
            'courseId.require'  => '课程不能为空',
            'chapterid.require'  => '课程章必须选择',
//            'mediaSource.require'  => '媒体资源必须填写',
        ];
        $validate = new Validate([
            'rid'       => 'require',
            'title'     => 'require|length:2,20',
            'paperid'   =>'require',
            'courseId'  => 'require',
            'chapterid'  => 'require',
//            'mediaSource'  => 'require'
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $type = isset($info['type'])?$info['type']:'url';
        $paperid = isset($info['paperid'])?$info['paperid']:0;
        if($type=='test' && !$paperid || $type=='exam' && !$paperid ){

            return ['error'=>'类型为测验或考试时，必须选择试卷','code'=>'200'];
        }

        $role_table = Db::name('course_task');

        $id = $info['rid']+0;
        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此任务','code'=>'300'];
        }

        $data = [
            'title' => $info['title'],
            'chapterid'=> $info['chapterid'],
            'isFree'=>isset($info['isFree'])?$info['isFree']:0,
            'mode'=>$info['mode'],
            'point'=>$info['point'],
            'sort'=>$info['sort'],
            'paperid'=>$paperid,
            'type'=>$type,
            'length'=>isset($info['length'])?$info['length']:0,
            'mediaSource'=>isset($info['mediaSource'])?$info['mediaSource']:'',
            'courseId'=>$info['courseId']+0,
            'status'=>$info['status']
        ];

        $ok = $role_table->where('id',$id)->update($data);

        if(is_numeric($ok)){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function editshow(){
        $id = request()->get('id')+0;
        $cid = request()->get('cid')+0;

        $info = Db::table('course_task')->where('id',$id)->find();

        $taskmode = Db::table('task_mode')->field('id,name')->select();
        $chapter = Db::table('course_chapter')->field('id,title')->where('courseid',$cid)->select();
//        $testpaper = Db::table('testpaper')->field('id,name')->where('courseid',$cid)->select();
        $coursetask=Db::table('course_task')
            ->where(function ($query) {
                $query->where('type','test')->whereor('type','exam')->whereor('type','plan');
            })->where('paperid','<>','')->where('paperid','<>',$info['paperid'])->column('paperid');//查询已经用过的试卷的id
        $testpaper = Db::table('testpaper')->field('id,name')->where('courseid',$cid)->where('id','not in',$coursetask)->select();

        $this->assign('chapter',$chapter);
        $this->assign('a',$info);
        $this->assign('typename','课程任务修改');
        $this->assign('taskmode',$taskmode);
        $this->assign('testpaper',$testpaper);
        $this->assign('cid',$cid);
        $this->assign('uid',session('admin_uid'));
        return $this->fetch();
    }

    public function delete(){
            $id = $_GET['rid']+0;
            $ok=Db::name('course_task')->where("id='$id'")->delete();
            if(is_numeric($ok)){
                return ['info'=>'删除成功','code'=>'000'];//改为删除
            }else{
                return ['info'=>'删除失败','code'=>'400'];//改为删除
            }

    }
    public function upload(){

        $mediafile = new Mediaupload();
        $all = $mediafile->getfile();

        if($all['message']=='success'){

            //mp4上传
            $video_info = getVideoInfo($all['fileinfo']['name']);
            $duration = isset($video_info['duration'])?$video_info['duration']:NULL;
            $all['fileinfo']['duration'] = $duration;

        }

        echo json_encode($all);
        exit;

    }

    public function ajax($id,$type){
        $coursetask=Db::table('course_task')
            ->where(function ($query) {
                $query->where('type','test')->whereor('type','exam')->whereor('type','plan');
            })->where('paperid','<>','')->column('paperid');//查询已经用过的试卷的id
        $testpaper = Db::table('testpaper')
            ->field('id,name')
            ->where('courseid',$id)
            ->where('id','not in',$coursetask)
            ->where('type',$type)->select();
        return $testpaper;
    }

}