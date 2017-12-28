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
class User extends Validate
{
    // 定义验证规则
    protected $rule = [
//        'title|标题' => 'require|length:1,30',
//        'cover|图片' => 'require',
//        'url|链接'   => 'require|url',
        'email'      => 'require|email',
        'username'   => 'require|length:1,50|chsDash',
        'password'   => 'require'
    ];

}
