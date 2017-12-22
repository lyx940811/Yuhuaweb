<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Controller;
use think\Db;
use think\Exception;

class Index extends Home
{

    public $redis;
    public function index()
    {
        phpinfo();
//        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
    }

    public function test()
    {
        return $this->fetch();
    }
    public function file2()
    {
        return $this->fetch();
    }
    public function excepTion(){
//        $res = json_encode(["0"]);
//        var_dump(json_decode('[["\u6674","\u9634","\u96e8"]]'));
//        var_dump(json_decode('["<p>\u95ee\u7b54\u9898-\u7b54\u6848<\/p>\r\n"]'));
//        var_dump(json_decode('{"choices":["<p>\u5355\u9009\u9898-\u9009\u9879A<\/p>\r\n","<p>\u5355\u9009\u9898-\u9009\u9879B<\/p>\r\n","<p>\u5355\u9009\u9898-\u9009\u9879C<\/p>\r\n","<p>\u5355\u9009\u9898-\u9009\u9879D<\/p>\r\n"]}'));
//        $res = json_decode('{"choices":["<p>\u5355\u9009\u9898-\u9009\u9879A<\/p>\r\n","<p>\u5355\u9009\u9898-\u9009\u9879B<\/p>\r\n","<p>\u5355\u9009\u9898-\u9009\u9879C<\/p>\r\n","<p>\u5355\u9009\u9898-\u9009\u9879D<\/p>\r\n"]}');
//        var_dump($res->choices);
        var_dump(json_decode('{"mode":"rand","ranges":{"courseId":"0","lessonId":"0"},"counts":{"single_choice":"2","choice":"1","essay":"0","uncertain_choice":"0","determine":"0","fill":"0","material":"0"},"scores":{"single_choice":"2","choice":"2","essay":"2","uncertain_choice":"2","determine":"2","fill":"2","material":"2"},"missScores":{"choice":"0","uncertain_choice":"0"},"percentages":{"simple":"","normal":"","difficulty":""}}'));
    }
    public function inde(){
        var_dump(time_tran('2017-12-21 10:12:58'));
//        var_dump(explode('.',"readme"));
    }
    public function rrr()
    {
        //connect redis
//        $redis = new \Redis();
//        $re = $redis->connect('127.0.0.1', 6379);
//        $redis->set('wrong1',1);
//        $re = $redis->get('wrong1');
//        $redis->setex('key', 3600, 'value');
//        var_dump($re);
//        phpinfo();
    }
    public function getnav(){
        $nav = Db::name('role')->where('parentcode','0')->field('id,name,code,parentcode')->select();

        $nav = $this->getnac($nav);
        var_dump($nav);
    }
    public function getnac($nav){
        foreach ($nav as &$n){
            if(Db::name('role')->where('parentcode',$n['code'])->field('id,name,code,parentcode')->select()){
                $n['son'] = Db::name('role')->where('parentcode',$n['code'])->field('id,name,code,parentcode')->select();
                $n['son'] = self::getnac($n['son']);
            }
        }
        return $nav;
    }


    public function getrole(){
        $role = Db::name('role')->field('id,name,code,parentcode')->select();
        $range_role = array();
        $role = $this->dealrole($role);

        var_dump($role);
    }
    public function dealrole($role,$range_role = array()){
        if(empty($range_role)){
            foreach ($role as $key=>$value){
                if($value['parentcode']==0){
                    $range_role[] = $value;
                    unset($role[$key]);
                }
            }
            if(empty($role)){
                return $range_role;
            }
            else{
                return self::dealrole($role,$range_role);
            }
        }
        else{
            foreach ($range_role as &$r){
                foreach ($role as $k=>$v){
                    if($v['parentcode']==$r['code']){
                        $r['son'] = $role[$k];
                        unset($role[$k]);
                    }
                }
            }
            return $range_role;
        }
    }




    public function dir(){
        $uploads_dir = "uploads".DS."pictures".DS.date('Y',time()).DS.date('m',time()).DS.date('d',time());
        $date_dir    = ROOT_PATH."public".DS.$uploads_dir;
        if(!file_exists($date_dir)){
            echo 1;
            mkdir($date_dir,0775,true);
        }
        var_dump($date_dir);
    }
    /**
     *
     */
    public function pwd(){
        $pwd = '123456';
        echo md5($pwd);echo '<br>';
        $hash = '$2y$10$Ngmkefwv7LnEh14SSDZnGuTigiCRI7oEkFdhp7jz5UU704RZiDFCa';
        echo $hash;echo '<br>';
        $string = password_verify('123456',$hash);
        echo $string;echo '<br>';
    }
}
