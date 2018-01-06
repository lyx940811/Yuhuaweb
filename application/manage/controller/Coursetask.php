<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/2
 * Time: 14:34
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

/*
 * 课程任务管理
 */
class Coursetask extends Base{

    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['title'])){

            $where['a.title'] = ['like',"%{$info['title']}%"];
        }

        $list = Db::table('course_task a')
            ->field('a.id,a.title,a.courseId,a.mediaSource,a.startTime,a.endTime,b.title btit')
            ->join('course b','a.courseId=b.id','LEFT')
            ->where($where)
            ->paginate(20,['query'=>request()->get()]);

        $course = Db::table('course')->field('id,title')->select();

        $this->assign('list',$list);
        $this->assign('course',$course);
        $this->assign('typename','课程任务');
        $this->assign('page',$list->render());
        return $this->fetch();
    }


    public function add(){
        $info = input('post.');


        $msg  =   [
            'title.require'     => '任务名称不能为空',
            'title.length'      => '任务名称长度太短',
            'startTime.require' => '开始时间不能为空',
            'endTime.require'   => '结束时间不能为空',
            'courseId.require'  => '课程不能为空',
        ];
        $validate = new Validate([
            'title'     => 'require|length:2,20',
            'startTime' => 'require',
            'endTime'   => 'require',
            'courseId'  => 'require'
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('course_task');

        $data = [
            'title' => $info['title'],
            'startTime' => $info['startTime'],
            'endTime'=> $info['endTime'],
            'isFree'=>$info['isFree'],
            'isOptional'=>$info['isOptional'],
            'mode'=>$info['mode'],
            'type'=>isset($info['type'])?$info['type']:'url',
            'length'=>isset($info['length'])?$info['length']:0,
            'mediaSource'=>isset($info['mediaSource'])?$info['mediaSource']:'',
            'maxOnlineNum'=>$info['maxOnlineNum']+0,
            'maxPoint'=>$info['maxPoint']+0,
            'courseId'=>$info['courseId']+0,
            'createdUserId'=>session('admin_uid'),
            'createdTime'=>date('Y-m-d H:i:s',time()),
            'status'=>1,
        ];

        $ok = $role_table->field('title,startTime,endTime,isFree,isOptional,mode,type,length,mediaSource,maxOnlineNum,maxPoint,courseId,createdUserId,createdTime,status')->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'400'];
        }
    }


    public function edit(){
        //前台先获取资料
        if(isset($_GET['do'])=='get'){
            $id = $_GET['rid']+0;

            $have = Db::name('course_task')->field('title,startTime,endTime,isFree,isOptional,mode,type,length,mediaSource,maxOnlineNum,maxPoint,courseId')->where("id='$id'")->find();

            if(!$have){//如果这个code有
                return ['error'=>'没有此任务','code'=>'300'];
            }else{
                return ['info'=>$have,'code'=>'000'];
            }

        }

        $info = input('post.');

        $msg  =   [
            'rid'               =>'任务id不能为空',
            'title.require'     => '任务名称不能为空',
            'title.length'      => '任务名称长度太短',
            'startTime.require' => '开始时间不能为空',
            'endTime.require'   => '结束时间不能为空',
            'courseId.require'  => '课程不能为空',
        ];
        $validate = new Validate([
            'rid'       => 'require',
            'title'     => 'require|length:2,20',
            'startTime' => 'require',
            'endTime'   => 'require',
            'courseId'  => 'require'
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('course_task');

        $id = $info['rid']+0;
        $have = $role_table->field('id')->where("id='$id'")->find();

        if(!$have){//如果没这个code
            return ['error'=>'没有此任务','code'=>'300'];
        }

        $data = [
            'title' => $info['title'],
            'startTime' => $info['startTime'],
            'endTime'=> $info['endTime'],
            'isFree'=>$info['isFree'],
            'isOptional'=>$info['isOptional'],
            'mode'=>$info['mode'],
            'type'=>isset($info['type'])?$info['type']:'url',
            'length'=>isset($info['length'])?$info['length']:0,
            'mediaSource'=>isset($info['mediaSource'])?$info['mediaSource']:'',
            'maxOnlineNum'=>$info['maxOnlineNum']+0,
            'maxPoint'=>$info['maxPoint']+0,
            'courseId'=>$info['courseId']+0,
        ];

        $ok = $role_table->field('title,startTime,endTime,isFree,isOptional,mode,type,length,mediaSource,maxOnlineNum,maxPoint,courseId')->where('id',$id)->update($data);

        if($ok){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function upload(){

//
//        $file = request()->file('mediaSource');
//
//        print_r($file)

        $mediafile = new Mediaupload();
        $all = $mediafile->getfile();


        if($all['message']=='success'){
            //用法
            $video_info = getVideoInfo($all['fileinfo']['name']);
            $duration = $video_info['duration'];
            $all['duration'] = $duration;

        }

        echo json_encode($all);
        exit;



    }

}