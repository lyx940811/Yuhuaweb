<?php
namespace app\api\controller;

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
    public function __construct()
    {
        parent::__construct();
    }
    public function test()
    {
        return $this->fetch();
    }

    /**
     * 首页得到分类列表
     * @return array
     */
    public function getcategory(){

        if(!empty($this->user)){
            $map['code'] = $this->user->stuclass->majors;
            $category = Db::name('category')->where($map)->field('name,code')->select();
            return json_data(0,$this->codeMessage[0],$category);
        }
        $category = Db::name('category')
            ->where('grade',3)
            ->where('Flag',1)
            ->field('name,code')
            ->select();
        return json_data(0,$this->codeMessage[0],$category);
    }

    /**
     * 首页得到不同条件的课程
     * @return array
     */
    public function getindexcourse(){
        $type               = $this->data['type'];
        $map['categoryId']  = $this->data['categoryId'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;

        $type_array = ['hot','recommend','new'];
        if(!in_array($type,$type_array)){
            return json_data(1000,$this->codeMessage[1000],'');
        }
        if(empty($map['categoryId'])){
            unset($map['categoryId']);
        }

//        switch($type){
//            case 'hot':
//                $map['is_hot'] = 1;
//                break;
//            case 'recommend':
//                $map['recommended'] = 1;
//                break;
//            case 'new':
//                $map['is_new'] = 1;
//                break;
//        }

        $map['status'] = 1;
        if(!empty($this->user)){
            $map['categoryId'] = $this->user->stuclass->majors;
            if(!empty($this->user->stuclass->academic)&&$this->user->stuclass->academic!=0){
                $map[]=['exp','FIND_IN_SET('.$this->user->stuclass->academic.',school_system)'];
            }
        }

        $course = Db::name('course')
            ->where($map)
            ->field('id,title,smallPicture,price')
            ->order('createdTime desc')
            ->page($page,6)
            ->select();
        //需要对在学人数和评论进行计数
        foreach ( $course as &$c ){
            $learnNum = Db::name('study_result_v13')
                ->alias('sr')
                ->join('course_task ct','sr.taskid=ct.id')
                ->where('ct.courseId',$c['id'])
                ->group('sr.userid')
                ->count();
            $c['learnNum'] = $learnNum;
            $c['commentsNum']   = Db::name('course_review')->where('courseid',$c['id'])->count();

            $c['price']==0.00?$c['is_free'] = 1:$c['is_free'] = 0;
            unset($c['price']);
            $c['smallPicture']  = $this->request->domain()."/".$c['smallPicture'];
        }

        return json_data(0,$this->codeMessage[0],$course);
    }


    /**
     * 得到轮播图(不带跳转链接版本)
     */
//    public function getscrollpic(){
//        $pic = Db::name('ad')->where('type','mobile')->where('flag',1)->field('img')->select();
//
//        if($pic){
//            $pic = array_column($pic,'img');
//            foreach ($pic as &$item) {
//               $item = $this->request->domain()."/".$item;
//            }
//        }
//        return json_data(0,$this->codeMessage[0],$pic);
//    }

    /**
     * 得到轮播图(带跳转链接版本)
     */
    public function getscrollpic(){
        $pic = Db::name('ad')->where('type','mobile')->where('flag',1)->field('img,url')->select();
        if($pic){
            foreach ($pic as &$p) {
                $p['img'] = $this->request->domain()."/".$p['img'];
            }
        }
        return json_data(0,$this->codeMessage[0],$pic);
    }


    /**
     * 搜索
     */
    public function searchcourse(){
        $keywords = $this->data['keywords'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $orderby="CONVERT( title USING gbk ) COLLATE gbk_chinese_ci ASC";
        $map['status'] = 1;
        if(!empty($this->user)){
            $map['categoryId'] = $this->user->stuclass->majors;
        }
        $course = Db::name('course')
            ->where('title','like','%'.$keywords.'%')
            ->where($map)
            ->field('id,title,smallPicture,price')
            ->order($orderby)
            ->page($page,6)
            ->select();

        //需要对在学人数和评论进行计数
        foreach ( $course as &$c ){
            $learnNum = Db::name('study_result_v13')
                ->alias('sr')
                ->join('course_task ct','sr.taskid=ct.id')
                ->where('ct.courseId',$c['id'])
                ->group('sr.userid')
                ->count();
            $c['learnNum'] = $learnNum;
            $c['commentsNum']   = Db::name('course_review')->where('courseid',$c['id'])->count();

            $c['price']==0.00?$c['is_free'] = 1:$c['is_free'] = 0;
            unset($c['price']);
            $c['smallPicture']  = $this->request->domain()."/".$c['smallPicture'];
        }
        if(!empty($keywords)){
        $word = Db::name('search_word')->where('keyword',$keywords)->find();
        if($word){
            $data['hit'] = $word['hit']+1;
            Db::name('search_word')->where('id',$word['id'])->update($data);
        }
        else{
            $data['keyword'] = $keywords;
            Db::name('search_word')->insert($data);
        }
        }
        

        return json_data(0,$this->codeMessage[0],$course);
    }
    
    /**
     * 得到热门搜索词
     */
    public function gethotword(){
        $word = Db::name('search_word')->order('hit desc')->field('keyword')->limit(4)->select();
        $word = array_column($word,'keyword');
        return json_data(0,$this->codeMessage[0],$word);
    }

}
