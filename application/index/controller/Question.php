<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Loader;
use think\Db;
use think\Exception;
use app\index\model\QuestionType;
class Question extends Home
{
    public $LogicTestpaper;
    public function __construct(){
        parent::__construct();
        $this->LogicTestpaper  = Loader::controller('Question','logic');
    }

    /**
     * 得到所有题目类型（选择题、判断题、问答题）
     * 已经写入文档
     * @return array
     */
    public function getqsttype(){
        try{

            $queType = QuestionType::where('flag',1)->field('name,code')->select();
            return json_data(0,$this->codeMessage[0],$queType);
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }
    }

    /**
     * 得到本课程下的所有题目
     * 已写入文档
     * @return array
     */
    public function getqstlist(){
        try{
            $page     = $this->data['page'];
            $courseid = $this->data['courseid'];

            $qstList  = $this->LogicTestpaper->getQuestionList($courseid,$page);
            return json_data(0,$this->codeMessage[0],$qstList);
        }
        catch ( \ErrorException $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$this->codeMessage[$e->getCode()],'');
        }
    }

    /**
     * 得到单个题目的详细内容
     * @return array
     */
    public function getqstdetail(){
        try{
            $id = $this->data['id'];
            $question = $this->LogicTestpaper->getQuestionDetail($id);
            /**
             * 有坑，要把meta反序列化或者json解码
             * */

            return json_data(0,$this->codeMessage[0],$question);
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$this->codeMessage[$e->getCode()],'');
        }
    }

    /**
     * 增加/更新一个题目（未完成，对于序列化的内容）
     * @return array
     */
    public function addqst(){
        try{
//            $newdata = [
//                'type'          =>  $this->data['type'],        //和question_type对应的code
//                'stem'          =>  $this->data['stem'],        //题干，带html标签（富文本编辑器内的内容）
//                'createUserid'  =>  $this->data['createUserid'],//创建人id
//                'analysis'      =>  $this->data['analysis'],    //分析，带html标签（富文本编辑器内的内容）
//                'score'         =>  $this->data['score'],       //分数，float
//                'answer'        =>  $this->data['answer'],      //答案，json编码的数组，0键对应正确答案
//                'metas'         =>  $this->data['metas'],       //题目元信息，json编码过，每一个键值对应每一个选项内容，answer的答案代表这里的键名
//                'difficulty'    =>  $this->data['difficulty'],  //难易程度
//                'courseId'      =>  $this->data['courseId'],    //课程id
//                'createdTime'   =>  date('Y-m-d H:i:s',time()), //创建时间
//            ];
            $id = "";//5;//$this->data['id'];
            $newdata = [
                'type'          =>  "002",
                'stem'          =>  '题222asd干',
                'createUserid'  =>  3,
                'analysis'      =>  '题目分析',
                'score'         =>  5,
                'answer'        =>  '0',
                'metas'         =>  '{}',//序列化的表单
                'difficulty'    =>  'normal',
                'courseId'      =>  3,
                'createdTime'   =>  date('Y-m-d H:i:s',time()),
            ];
            if($id){
                $this->LogicTestpaper->updateQuestion($newdata,$id);
            }
            else{
                $this->LogicTestpaper->createQuestion($newdata);
            }
            return json_data(0,$this->codeMessage[0],'');
        }
        catch( Exception $e ){
            $code = $e->getCode();
            if($code==130){
                return json_data($code,$e->getMessage(),'');
            }
            else{
                return json_data($code,$this->codeMessage[$code],'');
            }
        }
    }

    /**
     * 删除题目
     * 已写入文档
     */
    public function delqst(){
        try{
            $id = 4;//$this->data['id'];//id可以为1，也可以为[1,2,3]
            $this->LogicTestpaper->delQuestion($id);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$this->codeMessage[$e->getCode()],'');
        }

    }

    /**
     * 搜索题目
     * 已写入文档
     * $type        题目类型      没有的话传空
     * $courseid    课程id        没有的话传空
     * $keywords    关键词        没有的话传空
     */
    public function searchqst(){
        try{
            $type       = $this->data['type'];
            $courseid   = $this->data['courseid'];
            $keywords   = $this->data['keywords'];
            $page       = $this->data['page'];
            $res = $this->LogicTestpaper->searchQuestion($type,$courseid,$keywords,$page);
            return json_data(0,$this->codeMessage[0],$res);
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }
    }
}
