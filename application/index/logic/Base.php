<?php

namespace app\index\logic;
use think\Loader;
use think\Config;
use think\Validate;
class Base
{
    protected $codeMessage;
    public function __construct()
    {
        $this->codeMessage = Config::get('apicode_message');
    }

    /**
     * 文件上传（多文件）
     * @param $files    文件信息
     * @return array|mixed  以数组的方式对应返回路径,键名为上传时定义的name，键值是路径
     */
    public function uploadFile($files){
        $save_path = array();
        foreach ($files as $key=>$value){
            if(!file_exists($files[$key]['tmp_name'])){
                continue;
            }
            $name        = $files[$key]['name'];
            $tmp_name    = $files[$key]['tmp_name'];
            $uploads_dir = ROOT_PATH."public".DS."uploads".DS.date('Y',time()).DS.date('m',time()).DS.date('d',time());
            if(!file_exists($uploads_dir)){
                mkdir($uploads_dir,0775,true);
            }
            $uploads_dir = $uploads_dir.DS.$name;
            move_uploaded_file($tmp_name, iconv("utf-8","gb2312",$uploads_dir));
            $save_path[$key] = $uploads_dir;
        }
        return $save_path;
    }


    /**
     * 图片上传（多图）
     * @param $files    图片信息
     * @return array|mixed  以数组的方式对应返回路径,键名为上传时定义的name，键值是路径
     */
    public function uploadPic($files){
        $save_path = array();
        $type = ['image/gif','image/jpeg','image/png'];
        foreach ($files as $key=>$value){
            if(!file_exists($files[$key]['tmp_name'])){
                continue;
            }
            if(!in_array($files[$key]['type'],$type)){
                return json_data(700,$this->codeMessage[700],'');
                break;
            }
            $name        = $files[$key]['name'];
            $tmp_name    = $files[$key]['tmp_name'];
            $uploads_dir = ROOT_PATH."public".DS."uploads".DS."pictures".DS.date('Y',time()).DS.date('m',time()).DS.date('d',time());
            if(!file_exists($uploads_dir)){
                mkdir($uploads_dir,0775,true);
            }
            $uploads_dir = $uploads_dir.DS.$name;
            move_uploaded_file($tmp_name, iconv("utf-8","gb2312",$uploads_dir));
            $save_path[$key] = $uploads_dir;
        }
        return $save_path;
    }
}
?>
