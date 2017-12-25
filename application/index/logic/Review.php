<?php

namespace app\index\logic;

use app\index\model\Question;
use app\index\model\CourseReview;
use think\Exception;
use think\Loader;
use think\Config;
use think\Request;
use think\Validate;
use think\Db;
/**
 * 课程评论
 */
class Review extends Base
{
    public function __construct()
    {
        parent::__construct();

    }

    public function review($id){
        if($id==1){
            exit(json_encode(json_data(920,'1232222','')));
        }

        return 'hahaha';
    }

    /**
     * 写一条评论
     * @param $data
     * @throws Exception
     */
    public function writeComment($data){
        $validate = Loader::validate('CourseReview');
        if(!$validate->check($data)){
            throw new Exception();
        }
        CourseReview::create($data);
    }
    /**
     * 得到某课程下的所有一级评论
     */
    public function getcoursecomment($courseid){
        $comment = Db::name('course_review')
            ->where('courseid',$courseid)
            ->where('parentid',0)
            ->field('id,userid,content,createdTime')
            ->select();
        if($comment){
            foreach ($comment as &$c){
                $c['username'] = Db::name('user')->where('id',$c['userid'])->value('username');
                $c['avatar']   = Db::name('user')->where('id',$c['userid'])->value('title');
            }
        }
        return $comment;
    }
    /**
     * 得到某个评论的详细评论
     */
    public function getcommentdetail($commentid){
        $comment = Db::name('course_review')->field('id,userid,content,createdTime')->find($commentid);

        $son = Db::name('course_review')->where('parentid',$commentid)->field('id,userid,content,createdTime,touserId')->select();
        if($son){
            foreach ($son as &$s){
                $s['username'] = Db::name('user')->where('id',$s['userid'])->value('username');
                $s['tousername'] = Db::name('user')->where('id',$s['touserId'])->value('username');
                $s['avatar'] = Db::name('user')->where('id',$s['userid'])->value('title');
                $s['son'] = Db::name('course_review')->where('parentid',$s['id'])->field('id,userid,content,createdTime,touserId')->select();
                if($s['son']){
                    foreach ($s['son'] as &$ss){
                        $ss['username'] = Db::name('user')->where('id',$ss['userid'])->value('username');
                        $s['tousername'] = Db::name('user')->where('id',$s['touserId'])->value('username');
                        $ss['avatar'] = Db::name('user')->where('id',$ss['userid'])->value('title');
                    }
                }
            }
        }
        $comment['son'] = $son;
        return $comment;
    }
}
?>
