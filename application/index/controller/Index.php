<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Controller;
use think\Db;
use think\Exception;
use think\captcha\Captcha;

/** app首页相关的接口【1.还缺少得到轮播图的接口2.还需要对课程人数和评论进行计数】
 * Class Index
 * @package app\index\controller
 */
class Index extends Home
{
    public function test()
    {
        return $this->fetch();
    }

    /**
     * 首页得到分类列表
     * @return array
     */
    public function getcategory(){
        $category = Db::name('category')->where('grade',3)->where('Flag',1)->field('name,code')->select();
        return json_data(0,$this->codeMessage[0],$category);
    }

    /**
     * 首页得到不同条件的课程
     * @return array
     */
    public function getindexcourse(){
        $type               = $this->data['type'];
        $map['categoryId']  = $this->data['category'];

        $type_array = ['hot','recommend','new'];
        if(!in_array($type,$type_array)){
            return json_data(1000,$this->codeMessage[1000],'');
        }
        if(empty($map['categoryId'])){
            unset($map['categoryId']);
        }

        switch($type){
            case 'hot':
                $map['hot'] = 1;
                break;
            case 'recommend':
                $map['recommended'] = 1;
                break;
            case 'new':
                $map['is_new'] = 1;
                break;
        }

        $course = Db::name('course')->where($map)->field('id,title,smallPicture')->select();
        //需要对在学人数和评论进行计数



        return json_data(0,$this->codeMessage[0],$course);
    }

    /**
     * 得到轮播图
     */
    public function getscrollpic(){}

    /**
     * 搜索
     */
    public function searchcourse(){
        $keywords = $this->data['keywords'];
        $course = Db::name('course')
            ->where('title','like','%'.$keywords.'%')
            ->field('id,title,smallPicture')
            ->select();

        return json_data(0,$this->codeMessage[0],$course);
    }

}
