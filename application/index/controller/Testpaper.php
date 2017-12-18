<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Loader;
use think\Db;
use think\Exception;
use app\index\model\Question;
use app\index\model\QuestionType;
class Testpaper extends Home
{
    public $LogicTestpaper;
    public function __construct(){
        parent::__construct();
        $this->LogicTestpaper  = Loader::controller('Testpaper','logic');
    }

    /**
     * 得到所有题目类型
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
     * @return array
     */
    public function getqstlist(){
        try{
            $page     = $this->data['page'];
            $courseid = $this->data['courseid'];
            $qstList  = $this->LogicTestpaper->getQuestionList($courseid,$page);
            return json_data(0,$this->codeMessage[0],$qstList);
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
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
     * 增加一个题目（未完成）
     * @return array
     */
    public function addqst(){
        try{
//            $data = [
//                'type'          =>  $this->data['type'],
//                'stem'          =>  $this->data['stem'],
//                'createUserid'  =>  $this->data['createUserid'],
//                'analysis'      =>  $this->data['analysis'],
//                'score'         =>  $this->data['score'],
//                'answer'        =>  $this->data['answer'],
//                'metas'         =>  $this->data['metas'],
//                'difficulty'    =>  $this->data['difficulty'],
//                'courseId'      =>  $this->data['courseId'],
//                'createdTime'   =>  date('Y-m-d H:i:s',time()),
//            ];
            $newdata = [
                'type'          =>  002,
                'stem'          =>  '题干',
                'createUserid'  =>  3,
                'analysis'      =>  '题目分析',
                'score'         =>  5,
                'answer'        =>  '0',
                'metas'         =>  '{}',
                'difficulty'    =>  'normal',
                'courseId'      =>  3,
                'createdTime'   =>  date('Y-m-d H:i:s',time()),
            ];
            $this->LogicTestpaper->createQuestion($newdata);
            return json_data(0,$this->codeMessage[0],'');

        }
        catch( Exception $e ){
            $code = $e->getCode();
            if($code==130){
                return json_data($code,$e->getMessage(),'');
            }
            else{
                echo $e->getMessage();
            }
        }
    }

    /**
     * 删除题目
     */
    public function delque(){
        try{
            $id = $this->data['id'];//id可以为1，也可以为[1,2,3]
            $this->LogicTestpaper->delQuestion($id);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$this->codeMessage[$e->getCode()],'');
        }

    }

    /**
     * 搜索题目
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
