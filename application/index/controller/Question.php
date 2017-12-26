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


}
