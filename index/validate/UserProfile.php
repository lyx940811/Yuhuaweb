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
class UserProfile extends Validate
{
    // 定义验证规则
    protected $rule = [
        'userid|用户id'           => 'require',
        'mobile|电话'             => 'length:1,20',
        'idcard|身份证号码'       => 'length:1,20',
        'city|身份证号码'         => 'length:1,20',
        'birthday|'               => 'date',
        'signature|身份证号码'    => 'length:1,50',
        'qq|身份证号码'           => 'length:1,20',
        'mobile|身份证号码'       => 'length:1,20',
        'about|身份证号码'        => 'length:1,2000',
        'company|身份证号码'      => 'length:1,200',
        'job|身份证号码'          => 'length:1,100',
        'school|身份证号码'       => 'length:1,100',
        'weibo|身份证号码'        => 'length:1,100',
        'weixin|身份证号码'       => 'length:1,100',
        'site|身份证号码'         => 'length:1,500',
    ];

}
