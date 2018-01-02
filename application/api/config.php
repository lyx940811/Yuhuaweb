<?php
/**
 * Created by PhpStorm.
 * User: Tian chen
 * Date: 2017/11
 * Time: 10:11
 */
return[
    // 默认输出类型
    'default_return_type'    => 'json',
    //视图输出字符串内容替换
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
        150     =>  'wrong user type',
        160     =>  'user has been locked',
        170     =>  'user have not been checked',
        180     =>  'wrong type',
        181     =>  'delete like error',//取消点赞失败
        //关于课程Course相关的
        200     =>  'not find the course',
        210     =>  'wrong request course type',
        220     =>  'not find any course files',
        230     =>  'not find the note',
        240     =>  '用户已经收藏了该课程',
        250     =>  '用户还没收藏该课程',
        //题目相关的
        300     =>  'not find the question',
        310     =>  'page cannot be null',
        //试卷相关的
        400     =>  'not find the testpaper',
        //话题相关的
        500     =>  'not find the ask',
        //评论相关的
        600     =>  'not find the comment',


        //文件上传部分
        700     =>  'wrong file type',//文件格式不符合上传要求
        710     =>  'file is over size',//文件超过大小限制
        //邮件发送部分
        800     =>  'email send error,check your email address',

        //token验证
        900     =>  'token verified error',
        910     =>  'user_token is empty or invalid',
        920     =>  'code verify error',
        //关于请求的
        1000    =>  'wrong request type'
    ],
];