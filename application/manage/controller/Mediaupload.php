<?php
namespace app\manage\controller;

use think\Controller;
use PDFConverter\PDFConverter;
// 指定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');

class Mediaupload extends Controller
{

    public function getfile(){
        $aFiles = $this->getUploadFiles();
        if(isset($aFiles[0])){
            $res = $this->saveMultiFiles($aFiles[0]);
            return $res;
        }

    }

    protected function getUploadFiles()
    {
        $aFiles      = $_FILES;
        $aMultiFiles = array();

        // 判断是否是分片上传
        $iChunk  = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $iChunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        foreach ($aFiles as $sKey => $mFiles) {
            if (is_array($mFiles['name'])) {
                $iCnt = count($mFiles['name']);

                for ($i = 0; $i < $iCnt; ++$i) {
                    $aMultiFiles[] = array(
                        'key'      => $sKey . '_' . $i,
                        'name'     => $mFiles['name'][$i],
                        'tmp_name' => $mFiles['tmp_name'][$i],
                        'error'    => $mFiles['error'][$i],
                        'size'     => $mFiles['size'][$i],
                        'chunk'    => $iChunk,
                        'chunks'    => $iChunks,
                    );
                }
            } else {
                $aMultiFiles[] = array(
                    'key'      => $sKey,
                    'name'     => $mFiles['name'],
                    'tmp_name' => $mFiles['tmp_name'],
                    'error'    => $mFiles['error'],
                    'size'     => $mFiles['size'],
                    'chunk'    => $iChunk,
                    'chunks'   => $iChunks,
                );
            }
        }

        return $aMultiFiles;
    }

    /**
     * 将临时文件合并成正式文件
     */
    protected function saveMultiFiles($aFile)
    {
        $tmp_file_path = './tmp';
        if(!file_exists($tmp_file_path)){
            mkdir($tmp_file_path,0777,true);
        }
        $p_sName         = $aFile['name'];
        $p_sNameFilename = pathinfo($p_sName, PATHINFO_FILENAME);
        $p_sFilePath     = $tmp_file_path.DIRECTORY_SEPARATOR.$p_sNameFilename;

        $p_sFilenamePath = $tmp_file_path.DIRECTORY_SEPARATOR.$p_sName;
        //这里会报错
        if (!file_exists($p_sFilenamePath)) {
            fopen(iconv("utf-8","gb2312",$p_sFilenamePath), "w");
        }

        $p_sTmpName = $aFile['tmp_name'];
        $p_iError   = $aFile['error'];
        $p_iSize    = $aFile['size'];
        $iChunk     = $aFile['chunk'] ;
        $iChunks    = $aFile['chunks'];
        $iError     = 0;


        if ($p_iError > 0) {
            // 文件上传出错
            $iError  = 1;
            $mReturn = '文件上传出错';
            return json_data(700,$mReturn,'');
//            break;
        }

        if (!is_uploaded_file($p_sTmpName)) {
            $iError  = 2;
            $mReturn = 'upload error, use http post to upload';
            return json_data(700,$mReturn,'');
//            break;
        }

        $oFInfo    = finfo_open(FILEINFO_MIME);
        $sMimeType = finfo_file($oFInfo, $p_sTmpName);

        finfo_close($oFInfo);

        $sExtension = pathinfo($p_sName, PATHINFO_EXTENSION);

        if (empty($sExtension)) {
            $iError  = 2;
            $mReturn = 'upload error, The file does not have an extension ';
            return json_data(100,$mReturn,'');
//            break;
        }

        if (!$in = @fopen(iconv("utf-8","gb2312",$p_sTmpName), "rb")) {
            $iError  = 1;
            $mReturn = "Failed to open input stream.";
            return json_data(200,$mReturn,'');
//            break;
        }

        if (!$out = @fopen(iconv("utf-8","gb2312","{$p_sFilePath}_{$iChunk}.parttmp"), "wb")) {
            $iError  = 1;
            $mReturn = "Failed to open output stream.";
            return json_data(300,$mReturn,'');
//            break;
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);

        rename(iconv("utf-8","gb2312","{$p_sFilePath}_{$iChunk}.parttmp"), iconv("utf-8","gb2312","{$p_sFilePath}_{$iChunk}.part"));

        $done  = true;
        for ($index = 0; $index < $iChunks; $index++) {
            if (!file_exists(iconv("utf-8","gb2312","{$p_sFilePath}_{$index}.part"))) {
                $done = false;
//                return json_data(0,'success','./upload/'.time().'.'.$sExtension);
                return json_data(400,"{$p_sFilePath}_{$index}.part",'');
//                break;
            }
        }

        if ($done) {
            $filename=explode('.',$aFile['name']);
            $time = time();
            $path = date('Ymd',$time);
            $path2 = 'upload/'.$path.'/';
            $sDestFile = $path2.$filename[0].'.'.$sExtension;       //合并文件地址

            if(!file_exists($path2)){
                mkdir(iconv("utf-8","gb2312",$path2),0777,true);
            }
            //以下if为判断不是分片上传的话直接挪缓存文件，但是没有删除
            if($iChunks==0){
                move_uploaded_file($p_sTmpName, iconv("utf-8","gb2312",$sDestFile));
                @unlink(iconv("utf-8","gb2312",$p_sFilenamePath));
                @unlink(iconv("utf-8","gb2312","{$p_sFilePath}_0.part"));
//                return json_data(0,'success',$sDestFile);
                $array=['xlsx','xls','doc','docx','ppt','pptx'];
                if(in_array($sExtension,$array)){
                    $pdf=$path2.$filename[0].'.pdf';
                    $converter = new PDFConverter();
                    $source = ROOT_PATH."public\\".$sDestFile;
                    $export = ROOT_PATH."public\\".$pdf;
                    $converter->execute($source, $export);
                    return ['code'=>000,'message'=>'success','fileinfo'=>['name'=>$sDestFile,'type'=>'pdf']];
                }
                return ['code'=>000,'message'=>'success','fileinfo'=>['name'=>$sDestFile,'type'=>$sExtension]];
            }


            if (!$out = @fopen(iconv("utf-8","gb2312",$sDestFile), "wb")) {
                $iError  = 1;
                $mReturn = "1Failed to open output stream.";
                return json_data(500,$mReturn,'');
//                break;
            }

            $sFileSize = 0;

            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $iChunks; $index++) {
                    if (!$in = @fopen(iconv("utf-8","gb2312","{$p_sFilePath}_{$index}.part"), "rb")) {
                        break;
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    @fclose($in);
                    @unlink(iconv("utf-8","gb2312","{$p_sFilePath}_{$index}.part"));
                }
                flock($out, LOCK_UN);
            }
            @fclose($out);

            // 删除临时文件
            @unlink(iconv("utf-8","gb2312",$p_sFilenamePath));

        }
//        send_email('312850391@qq.com','success',$sDestFile);
//        return json_data(0,'success',$sDestFile);
        return ['code'=>000,'message'=>'success','fileinfo'=>['name'=>$sDestFile,'type'=>$sExtension]];
    }










}
