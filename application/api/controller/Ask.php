<?php
namespace app\api\controller;

use app\index\model\Asklist;
use think\Exception;
use think\Loader;
use think\Config;
use app\index\model\Course as CourseModel;
use app\index\model\AskAnswer;
use app\index\model\User;
use think\Db;
use think\Validate;


/**
 * Class Course 在教师角色下的我的教学-在教课程-课程管理中的一些功能
 * @package app\index\controller
 */
class Ask extends Home
{
    /**
     * 得到专业列表，显示的是最新一条的提问
     */
    public function cateasklist(){
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $category = Db::name('category')
            ->where('grade',3)
            ->where('Flag',1)
            ->field('name,code')
            ->page($page,10)
            ->select();
        foreach ( $category as &$c ){
            $c['newask'] = Db::name('asklist')->where('category_id',$c['code'])->field('id as askID,userID,title')->order('addtime')->find();
            if($c['newask']){
                $user = User::get($c['newask']['userID']);
                $c['newask']['username'] = $user->username;
                $c['newask']['avatar']   = $this->request->domain()."/".$user->title;
                $answer = Db::name('ask_answer')->where('askID',$c['newask']['askID'])->field('addtime as answerTime')->order('addtime desc')->find();
                if($answer){
                    $c['newask']['answerTime'] =$answer['answerTime'];
                }
            }
        }

        return json_data(0,$this->codeMessage[0],$category);
    }

    /**
     * 得到某个专业下的所有提问【右侧还没确定显示课程还是不显示】
     */
    public function asklistbycate(){
        $category_id = $this->data['category_id'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $askList = Db::name('asklist')
            ->where('category_id',$category_id)
            ->page($page,10)
            ->select();
        if($askList){
            foreach ( $askList as &$a ){
                $user = User::get($a['userID']);
                $a['username']  = $user->username;
                $a['avatar']    = $this->request->domain()."/".$user->title;
                $a['commentsNum'] = Db::name('ask_answer')->where('askID',$a['id'])->count();
            }
        }
        return json_data(0,$this->codeMessage[0],$askList);
    }

    /**
     * 得到某个问答的详细信息（问题详情、回答数）
     */
    public function getaskdetail(){
        $askid = $this->data['askid'];
        $ask = Db::name('asklist')->find($askid);
        if(!$ask){
            return json_data(500,$this->codeMessage[500],'');
        }
        $user = User::get($ask['userID']);
        $ask['avatar']   = $this->request->domain()."/".$user->title;
        $ask['username'] = $user->username;
        $ask['category']  = Db::name('category')->where('code',$ask['category_id'])->value('name');
        $ask['answerNum'] = Db::name('ask_answer')->where('askID',$askid)->count();
        unset($ask['category_id'],$ask['userID'],$ask['courseid']);
        return json_data(0,$this->codeMessage[0],$ask);
    }

    /**
     * 得到某个问答的回答列表
     */
    public function getaskanswer(){
        $askid = $this->data['askid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        if(!Asklist::get($askid)){
            return json_data(500,$this->codeMessage[500],'');
        }
        $answer = Db::name('ask_answer')
            ->where('askID',$askid)
            ->page($page,10)
            ->select();
        if($answer){
            foreach ( $answer as &$as){
                $user = User::get($as['answerUserID']);
                $as['username'] = $user->username;
                $as['avatar']   = $this->request->domain()."/".$user->title;
                unset($as['askID'],$as['answerUserID']);
//                $as['like']     = Db::name('like')->where('type','answer')->where('articleid',$as['id'])->count();
            }
        }
        return json_data(0,$this->codeMessage[0],$answer);

    }




}
