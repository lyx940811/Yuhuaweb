<?php
namespace app\manage\controller;
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

        $this->assign('admin_info',$admin_info);


        return view('index');
    }

    public function right(){
        return view('right');
    }


}