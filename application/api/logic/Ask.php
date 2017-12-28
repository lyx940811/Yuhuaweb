<?php
namespace app\api\logic;

use Couchbase\Document;
use think\Controller;
use think\Db;
use think\Exception;
use think\Loader;
use app\index\model\Asklist;
use app\index\model\AskAnswer;
use think\Validate;

class Ask extends Base
{
    public function __construct(){
        parent::__construct();
    }

    /**
     * 编辑/新增问答
     */
    public function editask($ask_data){
        $id = $ask_data['id'];
        $data = [
            'title'     =>  $ask_data['title'],
            'content'   =>  $ask_data['content'],
            'userID'    =>  $ask_data['userID'],
            'courseid'  =>  $ask_data['courseid'],
            'addtime'   =>  date('Y-m-d H:i:s',time()),
        ];
        $validate = Loader::validate('Asklist');
        if(!$validate->check($data)){
            throw new Exception($validate->getError(),130);
        }
        if($id){
            if(!Asklist::get($id)){
                throw new Exception($this->codeMessage[500],500);
            }
            Asklist::where('id',$id)->update($data);
        }
        else{
            Asklist::create($data);
        }
    }

    /**
     * 新增一个回复
     * @param $answer_data
     */
    public function answerask($answer_data){
        if(!Asklist::get($answer_data['askID'])){
            throw new Exception($this->codeMessage[500],500);
        }
        $data = [
            'askID'          =>  $answer_data['askID'],
            'answerUserID'   =>  $answer_data['answerUserID'],
            'content'        =>  $answer_data['content'],
            'addtime'        =>  date('Y-m-d H:i:s',time()),
        ];
        $validate = Loader::validate('AskAnswer');
        if(!$validate->check($data)){
            throw new Exception($validate->getError(),130);
        }
        AskAnswer::create($data);
    }

    /**
     * 得到一个问答的详细信息（包括回复列表）
     */
    public function getaskdetail(){
        try{
            $id = 1;//$this->data['id'];
            if(!$ask = Asklist::get($id)){
                return json_data(500,$this->codeMessage[500],'');
            }
            $ask['username'] = Db::name('user')->where('id',$ask['userID'])->value('username');
            $ask['avatar']   = Db::name('user')->where('id',$ask['userID'])->value('title');
//            $ask['aftertime'] = time_tran($ask['addtime']);//几小时前

            $answer = Db::name('ask_answer')->where('askID',$id)->select();
            foreach ($answer as &$a){
                $a['username'] = Db::name('user')->where('id',$a['answerUserID'])->value('username');
                $ask['avatar']   = Db::name('user')->where('id',$ask['userID'])->value('title');
//                $a['aftertime'] = time_tran($ask['addtime']);
            }
            $ask['answer'] = $answer;
            return json_data(0,$this->codeMessage[0],$ask);
        }
        catch ( Exception $e){
            return json_data($e->getCode(),$e->getMessage(),'');
        }

    }

    /**
     * 删除问答
     */
    public function delask($id){
        Asklist::destroy($id);
        return json_data(0,$this->codeMessage[0],'');
    }

}