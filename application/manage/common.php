<?php
/**
 * Created by PhpStorm.
 * User: m's
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

    $admin_id = session('admin_uid');

    if($admin_id==1){

        return true;

    }

    // 执行查询;
    $user_groups = Db::view('user', 'id,username')
        ->alias('a')
        ->view('role b', 'name,code', "a.roles=b.id", 'LEFT')
        ->view('role_function c', 'rolecode,functioncode', "c.rolecode=b.id", 'LEFT')
        ->where("a.id='$uid' and c.Flag='1'")
        ->find();

    if(!$user_groups){
        return false;
    }

    $groups = $user_groups['functioncode'];

    if($groups){

        $funcs = Db::name('function')->field('url')->where('id','in',$groups)->select();


        $flag = false;

        if(is_string($url) && strlen($url)>0){

            foreach ($funcs as $k=>$v){

                if($url==$v['url']){//如果当前路由在允许的表里
                    $flag = true;
                    break;
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
                    break;
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
    $lnbsp .='&nbsp;&nbsp;&nbsp;';
    $levelHtml ='└ ';
    foreach ($arr as $k=>$v){
        if($v['parentcode']==$pcode){

            $v['level'] = $flag;//把层级压进去
            $v['levelHtml'] = $lnbsp.$levelHtml;//把层级压进去
            array_push($newArr, $v);

            tree($arr,$v['code'],$flag,$newArr,$levelHtml,$lnbsp);

        }

    }


//    $flag++;//层级
//    $levelHtml .='|--';
//    $lnbsp .='&nbsp;&nbsp;';
//
//    foreach ($arr as $k=>$v){
//        if($v['parentcode']==$pcode){
//
//            $v['level'] = $flag;//把层级压进去
//            $v['levelHtml'] = $levelHtml.$lnbsp;//把层级压进去
//            array_push($newArr, $v);
//            tree($arr,$v['code'],$flag,$newArr,$levelHtml,$lnbsp);
//
//        }
//
//    }

    return $newArr;
}

/*
 * 证书图片上传
 */
function upload($filename,$path){

    // 获取上传文件
    $file = request() -> file($filename);
    // 验证图片,并移动图片到框架目录下。

    $npath = DS.'uploads'.DS.$path;
    $movepath = ROOT_PATH.'public'.DS.$npath;
//  $movepath = ROOT_PATH.'public'.DS.'uploads'.DS.'certificate';
    $info = $file ->validate(['size' => 512000,'ext' => 'jpg,png,jpeg','type' => 'image/jpeg,image/png']) -> move($movepath);
    if($info){
        // $info->getExtension();         // 文件扩展名
        $mes = $info->getFilename();      // 文件名
        $mes2 = $info->getSaveName();

        return ['mes'=>$mes,'mes2'=>$mes2,'path'=>$npath.DS.$mes2,'code'=>000];
    }else{
        // 文件上传失败后的错误信息
        $mes = $file->getError();
        return ['mes'=>$mes,'code'=>200];

    }

}

function getVideoInfo($file) {


    $command = sprintf('ffmpeg -i "'.$file.'" 2>&1', $file);
//    exec('ffmpeg -i '.$file.' 2>&1',$arr);
//    var_dump($arr);
    ob_start();
    passthru($command);
    $info = ob_get_contents();
    ob_end_clean();

    $data = array();
    if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
        $data['duration'] = $match[1]; //播放时间
        $arr_duration = explode(':', $match[1]);
        $data['seconds'] = $arr_duration[0] * 3600 + $arr_duration[1] * 60 + $arr_duration[2]; //转换播放时间为秒数
        $data['start'] = $match[2]; //开始时间
        $data['bitrate'] = $match[3]; //码率(kb)
    }
//    if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
//        $data['vcodec'] = $match[1]; //视频编码格式
//        $data['vformat'] = $match[2]; //视频格式
//        $data['resolution'] = $match[3]; //视频分辨率
//        $arr_resolution = explode('x', $match[3]);
//        $data['width'] = $arr_resolution[0];
//        $data['height'] = $arr_resolution[1];
//    }
//    if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
//        $data['acodec'] = $match[1]; //音频编码
//        $data['asamplerate'] = $match[2]; //音频采样频率
//    }
//    if (isset($data['seconds']) && isset($data['start'])) {
//        $data['play_time'] = $data['seconds'] + $data['start']; //实际播放时间
//    }
    $data['size'] = filesize($file); //文件大小
    return $data;
}


function manage_log($module,$action,$message,$data,$level){
    $event = controller('Index/Log', 'logic');
    $event->createLog(session('admin_uid'),$module,$action,$message,$data,$level);
}


