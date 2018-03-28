<?php
namespace app\api\controller;

use Couchbase\Document;
use think\Loader;
use think\Db;
use think\Exception;
use app\index\model\QuestionType;
use app\index\model\Testpaper as TestpaperModel;
use app\index\model\TestpaperItem;
use app\index\model\Course;

/**
 * -----------------------迁移至Teacher
 * Class Testpaper
 * @package app\index\controller
 */
class Testpaper extends Home
{
//    public $LogicTestpaper;
//    public function __construct(){
//        parent::__construct();
//        $this->LogicTestpaper  = Loader::controller('Testpaper','logic');
//    }
//
//    /**
//     * 新增/更新试卷
//     * @return array
//     */
//    public function editpaper(){
//        try{
//            $id = $this->data['id'];
//            $data = [
//                'courseid'  =>  3,
//                'name'      =>  '试卷名称',
//                'description'   =>  '',
//                'userid'    =>  2,
//                'createTime'=>  date('Y-m-d H:i:s',time())
//            ];
//            $this->LogicTestpaper->editPaper($id,$data);
//            return json_data(0,$this->codeMessage[0],'');
//        }
//        catch ( Exception $e ){
//            return json_data($e->getCode(),$e->getMessage(),'');
//        }
//    }
//
//    /**
//     * 得到某课程下的所有试卷
//     * @return array
//     * @throws Exception
//     */
//    public function getpaperlist(){
//        $courseid = 3;//$this->data['courseid'];
//        $page = $this->data['page'];
//        if(!Course::get($courseid)){
//            throw new Exception($this->codeMessage[200],200);
//        }
//        if(empty($page)){
//            $page = 1;
//        }
//        $paperList = Db::name('testpaper')
//            ->where('courseid',$courseid)
//            ->page($page,10)
//            ->select();//TestpaperModel::all(['courseid'=>$courseid]);
//        foreach ( $paperList as &$p){
//            $p['username'] = Db::name('user')
//                ->where('id',$p['userid'])
//                ->value('username');
//        }
//        return json_data(0,$this->codeMessage[0],$paperList);
//    }
//
//    /**
//     * 删除试卷
//     * @return array
//     * @throws Exception
//     */
//    public function delpaper(){
//        $paperid = $this->data['id'];
//        if(is_array($paperid)){
//            if(!TestpaperModel::all($paperid)){
//                return json_data(400,$this->codeMessage[400],'');
//            }
//            TestpaperModel::destroy($paperid);
//        }
//        else{
//            if(!TestpaperModel::get($paperid)){
//                return json_data(400,$this->codeMessage[400],'');
//            }
//            TestpaperModel::destroy($paperid);
//        }
//        return json_data(0,$this->codeMessage[0],'');
//    }
//
//    /**
//     * 得到某个试卷的详细信息
//     */
//    public function getpaperdetail(){
//        $paperid = $this->data['id'];
//        if(!$paperDetail = TestpaperModel::get($paperid)){
//            return json_data(400,$this->codeMessage[400],'');
//        }
//        return $paperDetail;
//    }
//    /**
//     * 得到某个试卷下的所有问题
//     */
//    public function getqstlist(){
//        $paperid = 1;//$this->data['id'];
//
//        $type  = array();
//        $final = array();
//
//        if(!TestpaperModel::get($paperid)){
//            return json_data(400,$this->codeMessage[400],'');
//        }
//
//        $qstList = Db::name('testpaper_item')
//            ->alias('ti')
//            ->join('question q','ti.questionid = q.id')
//            ->field('ti.id,ti.score,ti.seq,q.stem,q.difficulty,q.type,q.id as questionid')
//            ->select();
//        if($qstList){
//            foreach ( $qstList as &$q ){
//                $q['typename'] = Db::name('question_type')
//                    ->where('code',$q['type'])
//                    ->value('name');
//                $type[] = $q['typename'];
//            }
//            $type = array_unique($type);
//            $List = $qstList;
//            foreach ( $type as $t ){
//                foreach ( $List as $l ){
//                    if($l['typename']==$t){
//                        $final[$t][] = $l;
//                    }
//                }
//            }
//        }
//        return json_data(0,$this->codeMessage[0],$final);
//    }
//
//    /**
//     * 编辑试卷下的问题(更新&增加)
//     */
//    public function editquestion(){
//        $paperid     = 1;//$this->data['paperid'];
//        $passedScore = 2.5;//$this->data['passedScore'];
//        $question    = [
//            ['questionId'=>10,'score'=>2.5,'questiontype'=>'001'],
//            ['questionId'=>12,'score'=>3.5,'questiontype'=>'002'],
//        ];//$this->data['question'];
//
//        if(!$paper = TestpaperModel::get($paperid)){
//            return json_data(400,$this->codeMessage[400],'');
//        }
//        $paper->save(['passedScore'=>$passedScore,
//            'itemCount' =>  count($question),
//            'score'     =>  array_sum(array_column($question,'score')),
//        ],['id'=>$paperid]);
//
//        foreach ( $question as $q ){
//            if($item_qst = TestpaperItem::get(['questionId'=>$q['questionId'],'paperID'=>$paperid])){
//                $item_qst->isUpdate(true)
//                    ->save($q);
//            }
//            else{
//                $item_qst = new TestpaperItem;
//                $q['paperID'] = $paperid;
//                $item_qst->isUpdate(false)
//                    ->save($q);
//            }
//        }
//        return json_data(0,$this->codeMessage[0],'');
//    }
//
//    /**
//     * 得到某试卷下还未被添加的题目列表
//     */
//    public function getaddqst(){
//        $paperid  = 1;//$this->data['paperid'];
//        $item_qst = array();
//
//        if(!TestpaperModel::get($paperid)){
//            return json_data(400,$this->codeMessage[400],'');
//        }
//
//        $item_qst = Db::name('testpaper_item')
//            ->where('paperID',$paperid)
//            ->field('questionId')
//            ->select();
//        $item_qst = implode(',',array_column($item_qst,'questionId'));
//
//        $question = Db::name('question')
//            ->where('id','not in',$item_qst)
//            ->field('id,type,stem,score')
//            ->select();//题干，分值，类型
//
//        if($question){
//            foreach ( $question as &$q ){
//                $q['typename'] = Db::name('question_type')
//                    ->where('code',$q['type'])
//                    ->value('name');
//            }
//        }
//
//        return json_data(0,$this->codeMessage[0],$question);
//    }
}
