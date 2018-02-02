<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/31
 * Time: 14:22
 */
namespace app\manage\validate;

use think\Validate;

class Channelsalary extends Validate{

    protected $rule = [
        'name|名称' => 'require|length:1,30',
    ];
}