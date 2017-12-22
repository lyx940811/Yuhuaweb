<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Controller;
use think\Db;
use think\Exception;
use think\Loader;
use app\index\model\Asklist;
use think\Validate;

class Ask extends Home
{
    public function __construct(){
        parent::__construct();
    }

    /**
     * 教师获得我的教学-学员问答 列表
     * totalPage 总页数
     * page 当前页码
     * askList:
            'id'        => int 1
            'userID'    => int 1
            'content'   => string '问问问' (length=9)
            'addtime'   => string '2017-12-21 16:47:48' (length=19)
            'courseid'  => int 3
            'teacherid' => int 3
            'title'     => string '工业机器人技术基础' (length=27)
            'answerUserID'      => int 1
            'LateranswerTime'   => string '2017-12-21 16:58:20' (length=19)
            'answerUsername'    => string '中1文1调用测试123' (length=23)
     */
    public function tchgetlist(){
        $teacherid = $this->data['id'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $askList = Db::name('asklist')
            ->alias('al')
            ->join('course c','c.id = al.courseid')
            ->field('al.*,c.userid as teacherid,c.title')
            ->where('c.userid',$teacherid)
            ->page($page,10)
            ->select();
        foreach ( $askList as &$a){
            $answer = Db::name('ask_answer')
                ->where('askID',$a['id'])
                ->order('addtime desc')
                ->field('answerUserID,addtime as LateranswerTime')
                ->find();
            if($answer){
                $a = array_merge($a,$answer);
                $a['answerUsername']  = Db::name('user')->where('id',$a['answerUserID'])->value('username');
                $a['LateranswerTime'] = time_tran($a['LateranswerTime']);
            }
        }
        $total = Db::name('asklist')
            ->alias('al')
            ->join('course c','c.id = al.courseid')
            ->field('al.*,c.userid as teacherid,c.title')
            ->where('c.userid',$teacherid)
            ->count();
        $data = [
            'totalPage' => ceil($total/10),
            'askList'   => $askList,
            'page'      => $page
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }

    /**
     * 编辑/新增问答
     */
    public function editask(){
        $id = $this->data['id'];
        $data = [
            'title'     =>  $this->data['id'],
            'content'   =>  $this->data['content'],
            'userID'    =>  $this->data['userID'],
            'courseid'  =>  $this->data['courseid'],
            'addtime'   =>  date('Y-m-d H:i:s',time()),
        ];
        if($id){
            if(!Asklist::get($id)){
                return json_data(500,$this->codeMessage[500],'');
            }
            Asklist::where('id',$id)->update($data);
        }
        else{
            Asklist::create($data);
        }
    }

    /**
     * 得到一个问答的详细信息（包括回复列表）
     */
    public function getaskdetail(){
        try{
            $id = $this->data['id'];
            if(!$ask = Asklist::get($id)){
                return json_data(500,$this->codeMessage[500],'');
            }
            $ask['username'] = Db::name('user')->where('id',$ask['userID'])->value('username');
            $ask['aftertime'] = time_tran($ask['addtime']);

            $answer = Db::name('ask_answer')->where('askID',$id)->select();
            foreach ($answer as &$a){
                $a['username'] = Db::name('user')->where('id',$a['answerUserID'])->value('username');
                $a['aftertime'] = time_tran($ask['addtime']);
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
    public function delask(){
        try{
            $id = $this->data['id'];
            Asklist::destroy($id);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }
    }

}