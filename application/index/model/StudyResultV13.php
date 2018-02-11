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
class StudyResultV13 extends ThinkModel
{
    public function user()
    {
        return $this->hasOne('User','id','userid');
    }
    public function task()
    {
        return $this->hasOne('CourseTask','id','taskid');
    }
}