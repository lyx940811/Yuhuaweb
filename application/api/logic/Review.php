<?php

namespace app\api\logic;

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
        $validate = Loader::validate('index/CourseReview');
        if(!$validate->check($data)){
            throw new Exception();
        }
        CourseReview::create($data);
    }
    /**
     * 得到某课程下的所有一级评论
     */
    public function getcoursecomment($courseid,$page){
        $request = Request::instance();
        $comment = Db::name('course_review')
            ->where('courseid',$courseid)
            ->where('parentid',0)
            ->field('id,userid,content,createdTime')
            ->page($page,10)
            ->select();
        if($comment){
            foreach ($comment as &$c){
                $user = \app\index\model\User::get($c['userid']);
                $c['username'] = $user->username;
                $c['avatar']   = $request->domain().DS.$user->title;
                $c['sonreviewNum']   = Db::name('course_review')->where('parentid',$c['id'])->count();
                $c['likeNum']   = Db::name('like')->where('type','comments')->where('articleid',$c['id'])->count();
            }
        }
        return $comment;
    }
    /**
     * 得到某个评论的详细评论
     */
//    public function getcommentdetail($commentid){
//        $request = Request::instance();
//        $comment = Db::name('course_review')->field('id,userid,content,createdTime')->find($commentid);
//
//        $son = Db::name('course_review')->where('parentid',$commentid)->field('id,userid,content,createdTime,touserId')->select();
//        if($son){
//            foreach ($son as &$s){
//                $s['username'] = Db::name('user')->where('id',$s['userid'])->value('username');
//                $s['tousername'] = Db::name('user')->where('id',$s['touserId'])->value('username');
//                $s['avatar'] = $request->domain().DS.Db::name('user')->where('id',$s['userid'])->value('title');
//            }
//        }
//        $comment['son'] = $son;
//        return $comment;
//    }
}
?>
