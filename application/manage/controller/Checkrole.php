<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2017/12/14
 * Time: 15:00
 */
namespace app\manage\controller;

use think\Controller;
use think\Db;

class Checkrole extends Controller{

    /**
     * 根据用户传入的值判断是否有权限访问
     * @access public
     * @param string $uid 用户当前的id
     * @param array  $url  操作的数据
     * @return array or boolean
     */
    public function check($uid,$url='')
    {

        //获取用户权限组functioncode
        $func_code = $this->groups($uid);

        if($this->is_admin()){

            return true;

        }elseif($func_code){

            $funcs = Db::name('function')->field('url')->where('id','in',$func_code)->select();


            $flag = false;

            if(is_string($url) && strlen($url)>0){

                foreach ($funcs as $k=>$v){

                    if($url==$v['url']){//如果当前路由在允许的表里
                        $flag = true;
                    }

                }

                return $flag;

            }elseif(is_array($url)){

                $urls = [];
                foreach ($funcs as $k=>$v){

                    $urls[$k] = $v['url'];

                }

                $urls2 = [];
                foreach ($url as $v){

                  if(in_array($v,$urls)){

                      $urls2[] = $v;

                  }else{

                      $urls2[] = $flag;

                  }

                }

//                var_dump($urls2);
//exit;
                return $urls2;

            }else{

                $current_url = url('','',false);//当前路由

                foreach ($funcs as $k=>$v){

                    if($current_url==$v['url']){//如果当前路由在允许的表里
                        $flag = true;
                    }

                }

                return $flag;

            }



        }else{

            return false;
        }
    }

    protected function groups($uid){
        // 执行查询;
        $user_groups = Db::view('user', 'id,username')
            ->view('role', 'name,code', "user.roles=role.id", 'LEFT')
            ->view('role_function', 'rolecode,functioncode', "role_function.rolecode=role.id", 'LEFT')
            ->where("user.id='$uid' and role_function.flag='1'")
            ->find();

//        var_dump(Db::view('role_function')->getLastSql());exit;

        if(!$user_groups){
            return false;
        }
        $groups = $user_groups['functioncode'];
        return $groups;
    }

    protected function is_admin(){

        $admin_id = session('admin_uid');

        if($admin_id==1){
            return true;
        }

        return false;

    }

}