<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/31
 * Time: 10:06
 */
namespace app\manage\model;

use think\Model;

class ChannelLevel extends Model{

    public function c(){
        $this->belongsTo('Channel','left');
    }


}