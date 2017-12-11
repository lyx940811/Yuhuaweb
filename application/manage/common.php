<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2017/12/11
 * Time: 16:20
 */

function md5code($str,$str1,$md5str='ygs'){
    return md5($str+$str1+$md5str);
}