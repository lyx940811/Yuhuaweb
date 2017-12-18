<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Controller;
use think\Db;
use think\Exception;
use think\Loader;
use app\index\model\StudentEnroll;
use think\Validate;

class Apply extends Home
{
    public $LogicUpload;
    public function __construct(){
        parent::__construct();
        $this->LogicUpload = Loader::controller('Upload', 'logic');
    }

    public function getadmission(){
        $admission = Db::name('admission')->order('createdTime desc')->field('id,title')->find();
        return json_data(0,$this->codeMessage[0],$admission);
    }

    public function apply()
    {
        try {
            $data = $this->data;
            $key = [
                'realname'  =>  '',
                'sex'  =>  '',
                'age'  =>  '',
                'cardsn'  =>  '',
                'education'  =>  '',
                'admissionID'  =>  '',
                'categoryID'  =>  '',
                'phone'  =>  '',
                'promotMan'  =>  '',
                'school'  =>  '',
                'address'  =>  '',
            ];

            $data = array_intersect_key($data,$key);
            $validate = $this->validateData();
            if(!$validate->check($data)){
                return json_data(130,$validate->getError(),'');
            }
            $file = $_FILES;
            if($file){
                $res = $this->LogicUpload->uploadPic($file);
                $data['cardpic']    = serialize($res);
            }
            $data['createTime'] = date('Y-m-d H:i:s',time());
            StudentEnroll::create($data);

            return json_data(0,$this->codeMessage[0],'');
        } catch (Exception $e) {
            $errorCode = $e->getCode();
            return json_data($errorCode,$this->codeMessage[$errorCode],'');
        }

    }

    public function validateData(){
        $validate = new Validate([
            'realname|真实姓名'  =>  'require|chsAlpha',
//            'sex|性别'  =>  'require',
            'age|年龄'  =>  'between:1,101',
            'cardsn|身份证号'  =>  'require',
//            'education|教育'  =>  'require',
            'admissionID|批次id'  =>  'require|integer',
            'categoryID|专业id'  =>  'require',
            'phone|电话'  =>  'require',
//            'promotMan|推荐人'  =>  'require',
//            'school|毕业学校'  =>  '',
//            'address|家庭地址'  =>  '',
        ]);

        return $validate;
    }

    public function getmajor(){
        try{
//            $code = $this->request->param('code');
            $major = Db::name('category')
                ->where('grade',3)
                ->field('name,code')
                ->select();

            if(!$major){
                throw new Exception('not find any major,might be wrong code',1100);
            }
            return json_data(0,$this->codeMessage[0],$major);
        }
        catch ( Exception $e){
            $errorCode = $e->getCode();
            return json_data($errorCode,$this->codeMessage[$errorCode],'');
        }

    }

}