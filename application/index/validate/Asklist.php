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
 * 题目验证器
 */
class Asklist extends Validate
{
    // 定义验证规则
    protected $rule = [
        'type|题目类型'         =>  'require',
        'stem|题干'             =>  'require',
        'createUserid|创建者'   =>  'require',
        'analysis|分析'         =>  'require',
        'score|分数'            =>  'require|float',
        'answer|答案'           =>  'require',
        'metas|题目元信息'      =>  'require',
        'difficulty|难易程度'   =>  'require',
        'courseId|课程id'       =>  'require',
    ];

}
