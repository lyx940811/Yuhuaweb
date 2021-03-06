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
        if(!empty($info['status'])){
            if($info['status']==1){
                $where['d.id']=array('EXP','IS NULL');
            }
            if($info['status']==2){
                $where['d.id'] = ['neq',''];
            }
        }

        if(!empty($info['name'])){
            $where['a.name'] = ['like',"%{$info['name']}%"];
        }

        $list = Db::table('testpaper a')
            ->join('course b','a.courseid=b.id','LEFT')
            ->join('user c','a.createdUserId=c.id','LEFT')
            ->join('course_task d','d.paperid=a.id','LEFT')
            ->field('a.*,b.title,c.username,d.id as pid')
            ->where($where)
            ->order('a.id desc')
            ->paginate(20,false,['query'=>request()->get()]);

//        echo Db::table('testpaper a')->getLastSql();exit;
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
            $nlist[$k]['qinfo'] = Db::table('question a')
                ->join('testpaper_item b','a.id=b.questionId','LEFT')
                ->field('a.id,a.stem,a.metas,a.answer,a.type')
                ->group('a.id')
                ->where('b.paperId',$v['id'])->select();
        }

        $this->assign('course',$course);

        $this->assign('list',$nlist);
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
            'name.length' => '试卷名称长度不符合',
            'description.length' => '试卷说明长度不符合',
            'courseid.require' => '请先选择适用课程',
            'courseid.number' => '适用课程必须为数字',
        ];
        $validate = new Validate([
            'name'   => 'require|length:2,20',
            'description'   => 'length:2,20',
            'courseid'  => 'require|number',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('testpaper');

        $total = 0;
        $score = isset($info['scores'])?$info['scores']:0;
        $counts = isset($info['counts'])?$info['counts']:0;


        $num = 0;
        if($counts){
            foreach ($counts as $k=>$v){
                $total +=$v*$score[$k];
                if($counts[$k]>0){
                    $num++;
                }
            }
        }
        if($num<1){
            return ['error'=>'请填写相关题目数量','code'=>'300'];
        }

        $meta = [
            'mode'=>$info['mode'],
            'ranges'=>$info['ranges'],
            'counts'=>isset($counts)?$counts:'',
            'scores'=>$info['scores'],
            'missScores'=>$info['missScores'],
            'percentages'=>$info['percentages'],
        ];

        $data = [
            'name'  => $info['name'],
            'description'  => $info['description'],
            'metas' => json_encode($meta,true),
            'courseid'=>$info['courseid'],
            'type'  => $info['type'],
            'score'  => $total,
            'itemCount'  => $num,
            'createdUserId'=>session('admin_uid'),
            'createTime'=>date('Y-m-d H:i:s',time()),

        ];

        //开启事务
        Db::startTrans();
        $ok = $role_table->insert($data);

        if($ok){
            $id = $role_table->getLastInsID();//先取testpaper插入的id

            $question = isset($counts)?$counts:0;
            $questionitem = [];
            foreach ($question as $k=>$v){

                if($v>0){
                    $qwhere = [
                        'type'=>$k,
                        'courseId'=>$info['courseid']
                    ];

                    $questionitem[] = Db::table('question')->field('id,type')->where($qwhere)->order('RAND()')->limit($v)->select();

                }
            }

            //循环插入与题目相关联的表里
            foreach ($questionitem as $k=>$v){

                foreach ($v as $kk=>$vv){

                    Db::table('testpaper_item')->insert(
                        ['paperID'=>$id,'questionId'=>$vv['id'],'score'=>$score[$vv['type']],'questiontype'=>$vv['type']]
                    );
                    Db::table('question')->where('id',$vv['id'])->update(['score'=>$score[$vv['type']]]);

                }
            }

            $res = [
                'id'=>$id,
                'questionitem'=>$questionitem

            ];

            Db::commit();
            return ['info'=>$res,'code'=>000];
        }else{
            Db::rollback();
            return ['error'=>'添加失败','code'=>'300'];
        }
    }


    public function edit(){
        $info = input('post.');

        $msg  =   [
            'name.require' => '请填写试卷名称',
            'name.length' => '试卷名称长度不符合',
            'description.length' => '试卷说明长度不符合',
            'courseid.require' => '适用课程不能为空',
            'courseid.number' => '适用课程必须为数字',
        ];
        $validate = new Validate([
            'name'   => 'require|length:2,20',
            'description'   => 'length:2,20',
            'courseid'  => 'require|number',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('testpaper');

        $data = [
            'name'  => $info['name'],
            'description'  => $info['description'],
            'courseid'=>$info['courseid'],
//            'createdUserId'=>session('admin_uid'),
//            'createTime'=>date('Y-m-d H:i:s',time()),
        ];

        $id = $info['id']+0;
        $ok = $role_table->where('id',$id)->update($data);

        if($ok){

            return ['info'=>'修改成功','code'=>'000'];
        }else{
            return ['error'=>'修改失败','code'=>'300'];
        }
    }

    /*
     * 添加试卷页面
     */
    public function addtest(){
        $course = Db::table('course')->where('teacherIds',session('admin_uid'))->select();

        $id = request()->get('id');
        $article = Db::table('testpaper')->where('id',$id)->find();

        $item = [
            ['id'=>1,'name'=>'单选题','type'=>'single_choice'],
            ['id'=>2,'name'=>'多选题','type'=>'choice'],
            ['id'=>3,'name'=>'判断题','type'=>'determine'],
            ['id'=>4,'name'=>'问答题','type'=>'essay'],
        ];

//        foreach ($item as $k=>$v){
//            $item[$k]['num'] = Db::table('question')->where('type',$v['type'])->value('count(id) as num');
//        }

        $metas = !empty($article['metas'])?json_decode($article['metas']):'';

        $this->assign('article',$article);
        $this->assign('metas',$metas);
        $this->assign('id',$id);
        $this->assign('item',$item);
        $this->assign('typename','试卷管理');
        $this->assign('course',$course);
        return $this->fetch();
    }


    /*
     * 点击适用课程请求题目数量
     */
    public function requestnum(){

        $courseid = request()->get('courseid')+0;
        $info = Db::table('question')->where('courseId',$courseid)->field('count(id) as num,type')->group('type')->select();

        $qtype = [
            ['id'=>1,'name'=>'单选题','type'=>'single_choice'],
            ['id'=>2,'name'=>'多选题','type'=>'choice'],
            ['id'=>3,'name'=>'判断题','type'=>'determine'],
            ['id'=>4,'name'=>'问答题','type'=>'essay'],
        ];

        $aItem = [];
        foreach ($qtype as $k=>$v){

            foreach ($info as $kk=>$vv){
                if(in_array($v['type'],$vv)){
                    $qtype[$k]['num'] = $vv['num'];
                }
            }

        }

        if($info){
            return ['info'=>$qtype,'code'=>'000'];
        }else{
            return ['error'=>'没找到数据','code'=>'200'];
        }
    }


    /*
     * 添加试卷的下一步页面
     */
    public function additem(){
        $paperid = request()->param('id')+0;
        if(!$paperid){
            return ['error'=>'请先添加试卷','code'=>200];
        }

        if(request()->get('do')=='savescore'){
            //点击保存试卷的操作
            $info = input('post.');

            if($info['passedScore']>$info['paperscore']){
                return ['error'=>'及格分数不能大于试卷总和','code'=>200];
            }

            $ok = Db::table('testpaper')->where('id',$paperid)->update(
                [
                    'passedScore'=>$info['passedScore']+0,
                ]
            );

            if(is_numeric($ok)){
                return ['info'=>'保存成功','code'=>'000'];
            }else{
                return ['error'=>'保存失败','code'=>'200'];
            }

        }else{

            $list = Db::table('testpaper_item')->field('questionId as qid')->where('paperID',$paperid)->select();

            $newlist = [];
            foreach ($list as $k=>$v){
                $newlist[] = Db::table('question a')
                    ->join('course b','a.courseId=b.id','LEFT')
                    ->field('a.id,a.type,a.stem,a.score,b.title')->where('a.id',$v['qid'])->find();
            }

            $paperscore = Db::table('testpaper')->where('id',$paperid)->value('score');

            $this->assign('list',$newlist);
            $this->assign('typename','试卷管理');
            $this->assign('id',$paperid);
            $this->assign('paperscore',$paperscore);
            return $this->fetch();
        }

    }




    public function delete(){
        $id = $_GET['rid']+0;

        Db::startTrans();
        $ok = Db::name('testpaper')->where('id',$id)->delete();

        if(is_numeric($ok)){

            Db::table('testpaper_item')->where('paperID',$id)->delete();

            Db::commit();
            return ['info'=>'删除成功','code'=>'000'];
        }else{
            Db::rollback();
            return ['error'=>'删除失败','code'=>'200'];
        }
    }
}