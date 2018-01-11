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

namespace app\index\validate;

use think\Validate;

/**
 * 用户验证器
 */
class Course extends Validate
{
    // 定义验证规则
    protected $rule = [
        'title|课程标题'                 => 'length:1,200',
        'subtitle|课程副标题'            => 'length:1,100',
        'goals|课程目标'                 => 'length:1,2000',
        'audiences|适合人群'             => 'length:1,2000',
        'status|课程状态'                => 'in:1,0',
        'recommended|是否为推荐课程'     => 'in:1,0',
        'buyable|是否开启购买'           => 'in:1,0',
        'buyExpiryTime|购买开放有效期'   => 'int',
        'recommendedSeq|推荐序号'        => 'int',
        'parentId|课程的父id'            => 'int',
        'studentNum|学员数'              => 'int',
        'hitNum|查看次数'                => 'int',
        'daysOfNotifyBeforeDeadline|提前通知天数'    => 'int',
        'watchLimit|课时观看次数限制'                => 'int',
        'maxStudentNum|直播课程最大学员数上线'       => 'int',
        'expiryDay|课程过期天数'                     => 'int',
        'lessonNum|课时数'               => 'int',
        'giveCredit|学完课程所有课时，可获得的总学分'=> 'int',
        'rating|排行分数'                => 'int',
        'ratingNum|投票人数'             => 'int',
        'vipLevelId|可以免费看的，会员等级'          => 'int',
        'price|价格'                     => 'float',
        'coinprice|金币价格'             => 'float',
        'income|课程销售预计总收入'      => 'float',
    ];

}
