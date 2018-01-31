<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/31
 * Time: 14:22
 */
namespace app\manage\validate;

use think\Validate;

class Channel extends Validate{

    protected $rule = [
        'title|标题' => 'require|length:1,30',
    ];
}