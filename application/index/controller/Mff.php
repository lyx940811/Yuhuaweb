<?php
namespace app\index\controller;

use app\manage\controller\Integral;
use think\Controller;
use think\Config;
use think\Loader;
use app\index\model\User;
class Mff extends Controller
{

    public function __construct()
    {
        parent::__construct();

    }

    public function index(){
        $ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => 'G:\mff\ffmpeg-20180102-57d0c24-win64-static\ffmpeg-20180102-57d0c24-win64-static\bin\ffmpeg.exe',
            'ffprobe.binaries' => 'G:\mff\ffmpeg-20180102-57d0c24-win64-static\ffmpeg-20180102-57d0c24-win64-static\bin\ffprobe.exe'
        ]);
        $video = $ffmpeg->open('G:\wamp64\www\Yuhuaweb\public\uploads\20171212112601.flv');

        $video->save(new \FFMpeg\Format\Video\X264(), 'G:\wamp64\www\Yuhuaweb\public\uploads\x264.mp4');
    }

    private function transToH264($url,$newurl)
    {

        $ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => 'G:\mff\ffmpeg-20180102-57d0c24-win64-static\ffmpeg-20180102-57d0c24-win64-static\bin\ffmpeg.exe',
            'ffprobe.binaries' => 'G:\mff\ffmpeg-20180102-57d0c24-win64-static\ffmpeg-20180102-57d0c24-win64-static\bin\ffprobe.exe'
        ]);

        $video = $ffmpeg->open($url);

        $format = new \FFMpeg\Format\Video\X264();
        $format->setAudioCodec("libmp3lame");

        $video->save($format, $newurl);
    }

    //检查视频编码格式，不是h264的进行编码
    public function checkType()
    {
        //用存储路径来获得视频编码格式
        $url = 'path \ to \ file';
        $matches = $this->getType($url);
        if(!empty($matches)&&isset($matches[0]['vcodec'])){
            if (preg_match("/h264/i", $matches[0]['vcodec']))
            {
                //是可以播放的MP4

            }else{
                //不能播放的MP4，需要转码
                $newurl = '';
                $this->transToH264($url,$newurl);
            }
        }
    }

    public function getType($url)
    {
        define('KC_FFMPEG_PATH', 'G:\mff\ffmpeg-20180102-57d0c24-win64-static\ffmpeg-20180102-57d0c24-win64-static\bin\ffmpeg -i "%s" 2>&1');
        function video_info($file) {
            ob_start();
            passthru(sprintf(KC_FFMPEG_PATH, $file));
            $info = ob_get_contents();
            ob_end_clean();
            // 通过使用输出缓冲，获取到ffmpeg所有输出的内容。
            $ret = array();
            // Duration: 01:24:12.73, start: 0.000000, bitrate: 456 kb/s
            if (preg_match("/Duration: (.*?), start: (.*?), bitrate: (\d*) kb\/s/", $info, $match)) {
                $ret['duration'] = $match[1]; // 提取出播放时间
                $da = explode(':', $match[1]);
                $ret['seconds'] = $da[0] * 3600 + $da[1] * 60 + $da[2]; // 转换为秒
                $ret['start'] = $match[2]; // 开始时间
                $ret['bitrate'] = $match[3]; // bitrate 码率 单位 kb

            }
            // Stream #0.1: Video: rv40, yuv420p, 512x384, 355 kb/s, 12.05 fps, 12 tbr, 1k tbn, 12 tbc
            if (preg_match("/Video: (.*?), (.*?), (.*?)[,\s]/", $info, $match)) {
                $ret['vcodec'] = $match[1]; // 编码格式
                $ret['vformat'] = $match[2]; // 视频格式
                $ret['resolution'] = $match[3]; // 分辨率
                $a = explode('x', $match[3]);
                $ret['width'] = $a[0];
                $ret['height'] = $a[1];
            }
            // Stream #0.0: Audio: cook, 44100 Hz, stereo, s16, 96 kb/s
            if (preg_match("/Audio: (\w*), (\d*) Hz/", $info, $match)) {
                $ret['acodec'] = $match[1]; // 音频编码
                $ret['asamplerate'] = $match[2]; // 音频采样频率

            }
            if (isset($ret['seconds']) && isset($ret['start'])) {
                $ret['play_time'] = $ret['seconds'] + $ret['start']; // 实际播放时间

            }
            $ret['size'] = filesize($file); // 文件大小
            return array($ret, $info);
        }

        return $res = video_info(iconv('utf-8','gb2312',$url));
    }


    public function sss()
    {
        echo (5600/17*10)+(7000/17*7);
    }

}
