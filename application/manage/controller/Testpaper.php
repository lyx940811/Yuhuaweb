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

class Testpaper extends Base{

    public function index(){

        $info = input('get.');

        $where = [];
        if(!empty($info['type'])){
            $where['a.type'] = ['eq',$info['type']];
        }
        if(!empty($info['courseid'])){
            $where['a.courseid'] = ['eq',$info['courseid']];
        }
        if(!empty($info['stem'])){
            $where['a.stem'] = ['like',"%{$info['stem']}%"];
        }

        $list = Db::table('testpaper a')
            ->join('course b','a.courseid=b.id','LEFT')
            ->join('user c','a.createdUserId=c.id','LEFT')
            ->field('a.*,b.title,c.username')
            ->where($where)
            ->paginate(20);


        $qtype = [
            ['id'=>1,'name'=>'单选题','type'=>'single_choice'],
            ['id'=>2,'name'=>'多选题','type'=>'choice'],
            ['id'=>3,'name'=>'判断题','type'=>'determine'],
            ['id'=>4,'name'=>'问答题','type'=>'essay'],
        ];
        $course = Db::table('course')->where('teacherIds',session('admin_uid'))->select();


        $this->assign('course',$course);

        $this->assign('list',$list);
        $this->assign('page',$list->render());

        $this->assign('typename','试卷管理');
        $this->assign('qtype',$qtype);
        $this->assign('uid',session('admin_uid'));

        return $this->fetch();
    }

    public function add(){
        $info = input('post.');

        $msg  =   [
            'name.require' => '请填写试卷名称',
            'courseid.require' => '适用课程不能为空',
            'courseid.number' => '适用课程必须为数字',
        ];
        $validate = new Validate([
            'name'   => 'require',
            'courseid'  => 'require|number',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('testpaper');


        $total = 0;
        $score = $info['scores'];

        if(isset($info['counts'])){
            foreach ($info['counts'] as $k=>$v){
                $total +=$v*$score[$k];
            }
        }

        $meta = [
            'mode'=>$info['mode'],
            'ranges'=>$info['ranges'],
            'counts'=>isset($info['counts'])?$info['counts']:'',
            'scores'=>$info['scores'],
            'missScores'=>$info['missScores'],
            'percentages'=>$info['percentages'],
        ];

        $data = [
            'name'  => $info['name'],
            'description'  => $info['description'],
            'metas' => json_encode($meta),
            'courseid'=>$info['courseid'],
            'type'  => $info['type'],
            'score'  => $total,
            'itemCount'  => isset($info['counts'])?count($info['counts']):0,
            'createdUserId'=>session('admin_uid'),
            'createTime'=>date('Y-m-d H:i:s',time()),

        ];

        Db::startTrans();
        $ok = $role_table->insert($data);

        if($ok){

            Db::table('testpaper_item')->insert();

            Db::commit();
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            Db::rollback();
            return ['error'=>'添加失败','code'=>'300'];
        }
    }

    public function addtest(){
        $course = Db::table('course')->where('teacherIds',session('admin_uid'))->select();

        $id = request()->get('id');
        $article = Db::table('testpaper')->where('id',$id)->find();


        //单选题
        $item = Db::table('question')->group('type')->field('count(id) as num,type')->select();

        foreach ($item as $k=>$v){

            if($v['type']=='single_choice'){
                $item[$k]['name'] = '单选题';
            }
            if($v['type']=='choice'){
                $item[$k]['name'] = '多选题';
            }
            if($v['type']=='determine'){
                $item[$k]['name'] = '判断题';
            }
            if($v['type']=='essay'){
                $item[$k]['name'] = '问答题';
            }
        }


        $metas = !empty($article['metas'])?json_decode($article['metas']):'';

        $this->assign('article',$article);
        $this->assign('metas',$metas);
        $this->assign('id',$id);
        $this->assign('item',$item);
        $this->assign('typename','试卷管理');
        $this->assign('course',$course);
        return $this->fetch();
    }

    public function delete(){
        $id = $_GET['rid']+0;
        $ok = Db::name('testpaper')->where('id',$id)->delete();

        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}