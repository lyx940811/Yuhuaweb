<?php

namespace app\api\logic;

use app\index\model\Question;
use app\index\model\Testpaper as TestpaperModel;
use think\Exception;
use think\Loader;
use think\Config;
use think\Request;
use think\Validate;
class Testpaper extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function editPaper($id,$data){
        $validate = Loader::validate('Testpaper');
        if(!$validate->check($data)){
            throw new Exception($validate->getError(),130);
        }
        if(!$id){
            TestpaperModel::create($data);
        }
        else{
            TestpaperModel::where('id',$id)->update($data);
        }
    }
}
?>
