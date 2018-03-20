<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use PDFConverter\PDFConverter;
use think\queue\job\Redis;

class Pdfconver extends Controller
{

    public function ppttest()
    {
        $converter = new PDFConverter();
        $source = ROOT_PATH."public\\upload/20180208/1518076432.ppt" ;
        $export = ROOT_PATH."public\\upload/20180208/1518076432.pdf";
        $converter->execute($source, $export);
        echo 'Done';
    }

    public function set()
    {
        $converter = new PDFConverter();
        $err_array = [];
        $media = Db::name('course_task')->where('type','in',['doc','docx','ppt','pptx','xls','xlsx'])->column('mediaSource');
        foreach ($media as $m){
            $source = ROOT_PATH."public/".$m;
            if(file_exists($source)){
                $explSource = explode('.',$m);
                $export = ROOT_PATH."public/".$explSource[0].".pdf";
                $converter->execute($source, $export);
            }else{
                $err_array[] = $m;
            }
        }
        var_dump($err_array);
        echo 'Done';
    }

    public function redistest()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        var_dump($redis->getOption());
    }







}
