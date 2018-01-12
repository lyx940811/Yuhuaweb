<?php

namespace app\manage\controller;


class Upload
{
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
//                throw new Exception('wrong file type',700);
                return ['error'=>'wrong file type','code'=>700];
            }
            if(filesize($files[$key]['tmp_name'])>3145728){
//                throw new Exception('file over size',710);
                return ['error'=>'file over size','code'=>710];
            }
            $save_path[$key] = $this->upload($files[$key]);
        }
        return $save_path;
    }

    public function uploadFile($files){
        $save_file = array();
        foreach ($files as $key=>$value){
            if(!file_exists($files[$key]['tmp_name'])){
                continue;
            }
//            if(filesize($files[$key]['tmp_name'])>3145728){
//                throw new Exception('file over size',710);
//            }
            $save_file[$key]['filename']      = $files[$key]['name'];
            $save_file[$key]['type']          = $files[$key]['type'];
            $save_file[$key]['filesize']      = $files[$key]['size'];
            $save_file[$key]['filepath']      = $this->upload($files[$key]);
        }
        return $save_file;
    }

    /*
     * 广告管理用到的
     */
    public function upload($file,$branch='ad'){
        $name        = $file['name'];
        $tmp_name    = $file['tmp_name'];
        $uploads_dir = "uploads/".$branch."/".date('Ymd',time());
        $date_dir    = ROOT_PATH."public/".$uploads_dir;
        if(!file_exists($date_dir)){
            mkdir($date_dir,0775,true);
        }
        //rename file
        $name = explode('.',$name);
        $name[0] = uniqid();
        $name = implode('.',$name);

        $file_dir = ROOT_PATH."public/".$uploads_dir.'/'.$name;
        move_uploaded_file($tmp_name, iconv("utf-8","gb2312",$file_dir));
        return ['path'=>$uploads_dir.'/'.$name,'code'=>000];
//        return $uploads_dir.'/'.$name;
    }

}
?>
