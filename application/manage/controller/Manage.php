<?php
namespace app\manage\controller;
use think\Config;
use think\Db;

/**
 * Created by phpstorm.
 * User: m's
 * Date: 2017/12/5
 * Time: 17:16
 *
 */

class Manage extends Base{

    public function index(){

        parent::_initialize();
        $admin_info['name'] = session('admin_name');
        $admin_info['uid'] = session('admin_uid');
        $img = Config::get('view_replace_str');
        $admin_info['img'] = $img['__MANAGE_IMG__'].'profile_small.jpg';
        $admin_info['role'] = session('admin_role');

        $this->assign('typename','后台首页');
        $this->assign('admin_info',$admin_info);

        $roles = [
            ['typename'=>'用户管理','url'=>'/manage/user/index','children'=>[
                  ['url'=>'/manage/user/index','name'=>'用户列表'],
                  ['url'=>'/manage/role/index','name'=>'角色列表'],
                  ['url'=>'/manage/rolefunction/index','name'=>'权限组'],
                ]
            ],

            ['typename'=>'栏目管理','url'=>'/manage/functions/index','children'=>[
                    ['url'=>'/manage/functions/index','name'=>'栏目功能列表'],
                ]
            ],
            ['typename'=>'日志管理','url'=>'/manage/log/index','children'=>[
                    ['url'=>'/manage/log/index','name'=>'日志列表'],
                ]
            ],
            ['typename'=>'专业管理','url'=>'/manage/category/index','children'=>[
                    ['url'=>'/manage/category/index','name'=>'专业列表'],
                    ['url'=>'/manage/categorycourse/index','name'=>'专业课程'],
                    ['url'=>'/manage/studentenroll/index','name'=>'专业报名数据查询'],
                ]
            ],
            ['typename'=>'类别管理','url'=>'/manage/classcity/index','children'=>[
                    ['url'=>'/manage/classcity/index','name'=>'区域列表'],
                    ['url'=>'/manage/companynature/index','name'=>'企业性质'],
                    ['url'=>'/manage/companysize/index','name'=>'企业规模'],
                    ['url'=>'/manage/classcourse/index','name'=>'课程类型'],
                    ['url'=>'/manage/filetype/index','name'=>'文件类型'],
                    ['url'=>'/manage/tasktype/index','name'=>'任务类型'],
                ]
            ],
            ['typename'=>'标签管理','url'=>'/manage/tag/index','children'=>[
                    ['url'=>'/manage/tag/index','name'=>'标签列表'],
                ]
            ],
            ['typename'=>'课程管理','url'=>'/manage/course/index','children'=>[
                    [ 'url'=>'/manage/course/index','name'=>'课程列表'],
                    [ 'url'=>'/manage/coursefile/index','name'=>'课程资料'],
                    [ 'url'=>'/manage/coursefavorite/index','name'=>'收藏记录'],
                    [ 'url'=>'/manage/coursereview/index','name'=>'课程评价'],
                ]
            ],
            ['typename'=>'公告管理','url'=>'/manage/notice/index','children'=>[
                [ 'url'=>'/manage/notice/index','name'=>'公告列表'],
            ]
            ],
            ['typename'=>'题库管理','url'=>'/manage/question/index','children'=>[
                    [ 'url'=>'/manage/question/index','name'=>'题目列表'],
                ]
            ],
            ['typename'=>'试卷管理','url'=>'/manage/testpaper/index','children'=>[
                    [ 'url'=>'/manage/testpaper/index','name'=>'试卷列表'],
                    ['url'=>'/manage/paperexamines/index','name'=>'试卷批阅'],
                ]
            ],
            ['typename'=>'笔记管理','url'=>'/manage/coursenote/index','children'=>[
                    ['url'=>'/manage/coursenote/index','name'=>'笔记列表'],
                ]
            ],
            ['typename'=>'学生管理','url'=>'/manage/userprofile/index','children'=>[
                    ['url'=>'/manage/userprofile/index','name'=>'学生列表'],
                ]
            ],

            ['typename'=>'积分管理','url'=>'/manage/integral/index','children'=>[
                ['url'=>'/manage/integral/index','name'=>'积分记录'],
            ]
            ],
            ['typename'=>'宿舍列表','url'=>'/manage/dormitory/index','children'=>[
                    ['url'=>'/manage/dormitory/index','name'=>'宿舍管理'],
                ]
            ],
            ['typename'=>'班级管理','url'=>'/manage/classroom/index','children'=>[
                    ['url'=>'/manage/classroom/index','name'=>'班级管理'],
                ]
            ],
            ['typename'=>'数据统计','url'=>'/manage/statistics/index','children'=>[
                ['url'=>'/manage/statistics/index','name'=>'数据统计'],
                ['url'=>'/manage/studentstatistics/index','name'=>'学生统计'],
                ['url'=>'/manage/teacherstatistics/index','name'=>'教师统计'],
                ['url'=>'/manage/coursestatistics/index','name'=>'课程统计'],
                ['url'=>'/manage/loginlogstatistics/index','name'=>'登录日志统计'],
            ]
            ],
            ['typename'=>'招生管理','url'=>'/manage/admission/index','children'=>[
                    ['url'=>'/manage/admission/index','name'=>'招生列表'],
                    ['url'=>'/manage/studentenroll2/index','name'=>'报名管理'],
                    ['url'=>'/manage/returnvisit/index','name'=>'生员回访'],
                ]
            ],
            ['typename'=>'教师管理','url'=>'/manage/teacherinfo/index','children'=>[
                    ['url'=>'/manage/teacherinfo/index','name'=>'教师列表'],
                    ['url'=>'/manage/teacherreview/index','name'=>'教师评价列表'],
                ]
            ],
            ['typename'=>'问答管理','url'=>'/manage/questionanswers/index','children'=>[
                    ['url'=>'/manage/questionanswers/question','name'=>'问答列表'],
                ]
            ],
            ['typename'=>'话题管理','url'=>'/manage/topicconversation/index','children'=>[
                    ['url'=>'/manage/topicconversation/index','name'=>'话题列表'],
                ]
            ],
            ['typename'=>'广告管理','url'=>'/manage/ad/index','children'=>[
                    ['url'=>'/manage/ad/index','name'=>'广告列表'],
                ]
            ],
            ['typename'=>'渠道管理','url'=>'/manage/channel/index','children'=>[
                    ['url'=>'/manage/channel/index','name'=>'渠道列表'],
                ]
            ],
        ];


        $this->assign('roles',$roles);

        return $this->fetch('index');
    }



    public function right(){
        return $this->fetch('right');
    }


}