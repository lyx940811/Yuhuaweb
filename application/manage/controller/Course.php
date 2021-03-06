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
            $majorsid = Db::name('course')->where('id',$v['id'])->find();
            $allstudent=0;
            $where=[];
            if($majorsid) {
//                $where['majors']=$majorsid['categoryId'];
                $where['majors'] =array('in',explode(',',ltrim(rtrim($majorsid['categoryId'],","))));
                if(!empty($majorsid['school_system'])){
                    $aa=explode(',',$majorsid['school_system']);
                    $where['academic']=array('in',$majorsid['school_system']);
                }
                $allstudent = Db::table('student_school')->where($where)->count();
            }
            $categoryids=explode(',',ltrim(rtrim($v['categoryId'],",")));
            $newlist[$k]['categorysname']=DB::table('category')->where('code','in',$categoryids)->column('name');
            $newlist[$k]['categorysid']=$categoryids;
            $newlist[$k]['studentsystem']=[];
            $newlist[$k]['system']='';
            if(!empty($v['school_system'])){
                $system=explode(',',$v['school_system']);
                $array='';
                foreach($system as $key=>$va){
                    $a=$va-1;
                    $array.=$a.'学年,';
                }
                $newlist[$k]['system']=rtrim($array,',');
                $newlist[$k]['studentsystem']=explode(',',$v['school_system']);
            }
            $newlist[$k]['num'] =$allstudent; //Db::table('study_result')->where('courseid='.$v['id'])->group('userid')->count();
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
            'schoolsystem'  =>'学制不能为空',
            'title.length' => '课程名称长度太短',
            'categoryId.require' => '专业不能为空',
        ];
        $validate = new Validate([
            'title'  => 'require|length:2,20',
            'schoolsystem'=>'require',
            'categoryId'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('course');
        $schoolsystem='';
        if(!empty($info['schoolsystem'])){
            $schoolsystem=implode(',',$info['schoolsystem']);
        }
        $categoryId=implode(',',array_unique($info['categoryId']));
        $data = [
            'title'         => $info['title'],
            'subtitle'      => $info['subtitle'],
            'tags'          => $info['tags'],
            'categoryId'    => ','.$categoryId.',',
            'serializeMode' => $info['serializeMode'],
            'status'=>$info['status'],
            'smallPicture'  => $info['pic'],
            'userid'        => session('admin_uid'),
            'school_system' =>$schoolsystem,
            'about'        => $info['about'],
            'teachingplan' => htmlspecialchars_decode($info['teachingplan']),
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
            'schoolsystem'  =>'学制不能为空',
            'categoryId.require' => '专业不能为空',
        ];
        $validate = new Validate([
            'title'  => 'require|length:2,20',
            'schoolsystem'=>'require',
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
        $schoolsystem='';
        if(!empty($info['schoolsystem'])){
            $schoolsystem=implode(',',$info['schoolsystem']);
        }
        $categoryId=implode(',',array_unique($info['categoryId']));
        $data = [
            'title'         => $info['title'],
            'subtitle'      => $info['subtitle'],
            'tags'          => $info['tags'],
            'categoryId'    => ','.$categoryId.',',
            'serializeMode' => $info['serializeMode'],
            'userid'        => session('admin_uid'),
            'school_system' =>$schoolsystem,
            'about'        => $info['about'],
            'teachingplan' => htmlspecialchars_decode($info['teachingplan']),
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