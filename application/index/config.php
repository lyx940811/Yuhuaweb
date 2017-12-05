<?php
/**
 * Created by PhpStorm.
 * User: Tian chen
 * Date: 2017/11
 * Time: 10:11
 */
return[// 视图输出字符串内容替换
    'view_replace_str'       => [
        '__CSS__' => '/static/index/css/',
        '__IMG__' => '/static/index/images/',
        '__JS__' => '/static/index/js/',
        '__JSS__' => '/static/index/jss/',
    ],

    'apicode_message'          =>[
        //关于User相关的
        0       =>  'success',
        100     =>  'user insert error',
        110     =>  'not find the user',
        120     =>  'user has already exist',
        130     =>  '验证模型错误',
        140     =>  'wrong password',
        //邮件发送部分
        800     =>  'email send error',

        //token验证
        900     =>  'token verified error',
    ],
];