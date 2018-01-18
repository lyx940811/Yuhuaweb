<?php
namespace app\index\controller;

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

}
