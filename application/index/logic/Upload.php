<?php

namespace app\index\logic;


use think\Exception;

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
                throw new Exception('wrong file type',700);
            }
            if(filesize($files[$key]['tmp_name'])>3145728){
                throw new Exception('file over size',710);
            }
            $save_path[$key] = $this->upload($files[$key]);
        }
        return $save_path;
    }

    public function upload($file){
        $name        = $file['name'];
        $tmp_name    = $file['tmp_name'];
        $uploads_dir = "uploads".DS."pictures".DS.date('Y',time()).DS.date('m',time()).DS.date('d',time());
        $date_dir    = ROOT_PATH."public".DS.$uploads_dir;
        if(!file_exists($date_dir)){
            mkdir($date_dir,0775,true);
        }
        //rename file
        $name = explode('.',$name);
        $name[0] = date('Ymd',time()).uniqid();
        $name = implode('.',$name);

        $file_dir = ROOT_PATH."public".DS.$uploads_dir.DS.$name;
        move_uploaded_file($tmp_name, iconv("utf-8","gb2312",$file_dir));
        return $uploads_dir.DS.$name;
    }


}
?>
