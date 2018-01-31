<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2018/1/31
 * Time: 10:06
 */
namespace app\manage\model;

use think\Model;

class Channel extends Model{

    public function channellevel()
    {
        return $this->hasOne('ChannelLevel','code','level');
    }

    public function channeltype(){
        return $this->hasOne('ChannelType','code','type');
    }

    public function channelsalary(){
        return $this->hasOne('ChannelSalary','code','salary');
    }


}