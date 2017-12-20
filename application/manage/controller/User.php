<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2017/12/8
 * Time: 17:19
 */
namespace app\manage\controller;

use think\Db;
use think\paginator\driver\Bootstrap;
use think\Validate;

class User extends Base{


    public function index(){

        $lists = Db::name('user')->field('id,username,title,mobile,roles,type,email,createdIp,createdTime')->order('id asc')->paginate(20);


        $this->assign('list', $lists);
        $this->assign('typename','用户列表');
        $this->assign('page', $lists->render());
        return $this->fetch('index');
    }

}