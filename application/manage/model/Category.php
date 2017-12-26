<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/25
 * Time: 15:06
 */
namespace app\manage\model;
use think\Model;

class Category extends Model{

    protected $readonly = ['name','code'];

    protected $field = ['name','code'];

}