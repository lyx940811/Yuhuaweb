<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/231
 * Time: 10:40
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;

class Question extends Base{

    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['type'])){
            $where['a.type'] = ['eq',$info['type']];
        }
        if(!empty($info['courseId'])){
            $where['a.courseId'] = ['eq',$info['courseId']];
        }
        if(!empty($info['status'])){
            $titem = Db::table('testpaper_item')->field('questionId')->group('questionId')->select();

            $arr = '';
            foreach ($titem as $k=>$v){
                $arr.= ','.$v['questionId'];
            }
            $arr = trim($arr,',');
            if($info['status']==1){
                $where['a.id'] = ['not in',$arr];
            }else{
                $where['a.id'] = ['in',$arr];
            }

        }
        if(!empty($info['stem'])){
            $where['a.stem'] = ['like',"%{$info['stem']}%"];
        }

        $list = Db::table('question a')
            ->join('course b','a.courseid=b.id','LEFT')
            ->field('a.*,b.title as name')
            ->where($where)
            ->order('a.id desc')
            ->group('a.id')
            ->paginate(20);

        $qtype = [
            ['id'=>1,'name'=>'单选题','type'=>'single_choice'],
            ['id'=>2,'name'=>'多选题','type'=>'choice'],
            ['id'=>3,'name'=>'判断题','type'=>'determine'],
            ['id'=>4,'name'=>'问答题','type'=>'essay'],
        ];
        $course = Db::table('course')->where('teacherIds',session('admin_uid'))->select();

        $nlist = [];
        foreach ($list as $k=>$v){
            $nlist[$k] = $v;
            $nlist[$k]['isuse'] = Db::table('testpaper_item')->where('questionId',$v['id'])->count();
        }

        $this->assign('course',$course);

        $this->assign('list',$nlist);
        $this->assign('page',$list->render());

        $this->assign('typename','题库管理');
        $this->assign('qtype',$qtype);
        $this->assign('uid',session('admin_uid'));

        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'courseId.require' => '适用课程不能为空',
            'courseId.number' => '适用课程必须为数字',
            'stem.require' => '请填写题干',
            'stem.length' => '题干长度不符合',
            'answer.require' => '请填写答案',
        ];
        $validate = new Validate([
            'courseId'  => 'require|number',
            'stem'   => 'require|length:2,100',
            'answer'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('question');


        $data = [
            'stem'  => $info['stem'],
            'metas' => isset($info['metas'])?json_encode($info['metas'],true):'',
            'type'  => $info['type'],
            'courseId'=>$info['courseId'],
            'verification'=>$info['verification'],
            'analysis'=>$info['analysis'],
            'createdUserId'=>session('admin_uid'),
            'answer'=>isset($info['answer'])?json_encode($info['answer'],true):'',
            'difficulty'=>'normal',
            'createdTime'=>date('Y-m-d H:i:s',time()),

        ];

        $ok = $role_table->insert($data);

        if($ok){
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'300'];
        }
    }

    public function edit(){

        $info = input('post.');

        $msg  =   [
            'courseId.require' => '适用课程不能为空',
            'courseId.number' => '适用课程必须为数字',
            'stem.require' => '请填写题干',
            'stem.length' => '题干长度不符合',
            'answer.require' => '请填写答案',
        ];
        $validate = new Validate([
            'courseId'  => 'require|number',
            'stem'   => 'require|length:2,100',
            'answer'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('question');

        $id = $info['id']+0;

        $data = [
            'stem'  => $info['stem'],
            'metas' => isset($info['metas'])?json_encode($info['metas'],true):'',
            'type'  => $info['type'],
            'verification'=>$info['verification'],
            'courseId'=>$info['courseId'],
            'analysis'=>$info['analysis'],
            'answer'=>isset($info['answer'])?json_encode($info['answer'],true):'',
//            'difficulty'=>'normal',
//            'createdUserId'=>session('admin_uid'),
//            'createdTime'=>date('Y-m-d H:i:s',time()),

        ];

        $ok = $role_table->where('id',$id)->update($data);

        if(is_numeric($ok)){
            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'300'];
        }

    }



    public function single_choice(){
        $this->q_main('单选题');

        return $this->fetch();
    }


    public function choice(){
        $this->q_main('多选题');

        return $this->fetch();
    }



    public function determine(){
        $this->q_main('判断题');
        return $this->fetch();
    }



    public function essay(){
        $this->q_main('问答题');
        return $this->fetch();
    }

    public function q_main($type){
        $course = Db::table('course')->where('teacherIds',session('admin_uid'))->select();

        $id = request()->get('id');
        $article = Db::table('question')->where('id',$id)->find();


        $metas = !empty($article['metas'])?json_decode($article['metas']):'';

        $this->assign('article',$article);

        $this->assign('metas',$metas);
        $this->assign('id',$id);
        $this->assign('typename',$type);
        $this->assign('course',$course);
    }


    public function delete(){
        $id = $_GET['rid']+0;
        $ok = Db::name('question')->where('id',$id)->delete();

        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}