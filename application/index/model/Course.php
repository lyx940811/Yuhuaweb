<?php
// +----------------------------------------------------------------------
// | 海豚PHP框架 [ DolphinPHP ]
// +----------------------------------------------------------------------
// | 版权所有 2016~2017 河源市卓锐科技有限公司 [ http://www.zrthink.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://dolphinphp.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------

namespace app\index\model;

use think\Model as ThinkModel;

/**
 * 用户模型
 * @package app\cms\model
 */
class Course extends ThinkModel
{
    // 自动写入时间戳
//    protected $autoWriteTimestamp = true;
    public function category()
    {
        return $this->hasOne('category','code','categoryId');
    }

    public function teacher()
    {
        return $this->hasOne('User','id','userid');
    }

    public function teacherinfo(){
        return $this->hasOne('TeacherInfo','userid','teacherIds');
    }

    public function ask()
    {
        return $this->hasMany('Asklist','courseid','id');
    }

    public function studyresult()
    {
        return $this->hasMany('StudyResult','courseid','id');
    }

    public function file()
    {
        return $this->hasMany('CourseFile','courseid','id');
    }

    public function review()
    {
        return $this->hasMany('CourseReview','courseid','id');
    }

    public function note()
    {
        return $this->hasMany('CourseNote','courseId','id');
    }

    public function notice()
    {
        return $this->hasMany('CourseNotice','courseid','id');
    }

}