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

        $admin_info['name'] = session('admin_name');
        $admin_info['uid'] = session('admin_uid');
        $img = Config::get('view_replace_str');
        $admin_info['img'] = $img['__MANAGE_IMG__'].'profile_small.jpg';

        $this->assign('typename','后台首页');
        $this->assign('admin_info',$admin_info);


        return $this->fetch('index');
    }

    public function right(){
        return $this->fetch('right');
    }


}