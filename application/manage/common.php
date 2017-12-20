<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2017/12/11
 * Time: 16:20
 */
use think\Db;

function md5code($str,$str1,$md5str='ygs'){
    return md5($str+$str1+$md5str);
}


/*
 *
 */

function check($uid,$url='')
{

    // 执行查询;
    $user_groups = Db::view('user', 'id,username')
        ->view('role', 'name,code', "user.roles=role.id", 'LEFT')
        ->view('role_function', 'rolecode,functioncode', "role_function.rolecode=role.id", 'LEFT')
        ->where("user.id='$uid' and role_function.flag='1'")
        ->find();

    if(!$user_groups){
        return false;
    }

    $groups = $user_groups['functioncode'];

    $admin_id = session('admin_uid');

    if($admin_id==1){

        return true;

    }elseif($groups){

        $funcs = Db::name('function')->field('url')->where('id','in',$groups)->select();


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

//权限组用到，角色也用到
function getUserinfo($uid){

    $uid = $uid+0;
    $user = Db::name('user')->field('nickname')->where("id=$uid")->find();

    return $user['nickname'];

}

/**
 * 找子类
 * @access public
 * @param array $arr       要找子类的数组   必填
 * @param mixed $pcode     父类的code      可选
 * @return array
 */
function tree($arr,$pcode=0,$flag=0,&$newArr=[],$levelHtml='',$lnbsp=''){

    $flag++;//层级
    $levelHtml .='|--';
    $lnbsp .='&nbsp;&nbsp;';

    foreach ($arr as $k=>$v){
        if($v['parentcode']==$pcode){

            $v['level'] = $flag;//把层级压进去
            $v['levelHtml'] = $levelHtml.$lnbsp;//把层级压进去
            array_push($newArr, $v);
            tree($arr,$v['code'],$flag,$newArr,$levelHtml,$lnbsp);

//            echo $v['code'].'-'.$v['parentcode'].'-'.$v['name'].$flag.'级';
//            echo "<hr/>";
        }

    }




    return $newArr;
}
