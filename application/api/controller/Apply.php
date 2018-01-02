<?php
namespace app\api\controller;

use think\Db;
use think\Exception;
use think\Loader;
use app\index\model\StudentEnroll;
use think\Validate;

/** app报名相关的接口
 * Class Apply
 * @package app\index\controller
 */
class Apply extends Home
{
    public $LogicUpload;
    public function __construct(){
        parent::__construct();
        $this->LogicUpload = Loader::controller('Upload', 'logic');
    }

    /**
     * 获得最新报名批次
     * @return array
     */
    public function getadmission(){
        $admission = Db::name('admission')->order('createdTime desc')->field('id,title')->find();
        return json_data(0,$this->codeMessage[0],$admission);
    }

    /**
     * 报名提交表单
     * @return array
     */
    public function apply()
    {
        $data = $this->data;

        $key = [
            'realname'  =>  '',
            'cardsn'  =>  '',
            'education'  =>  '',
            'admissionID'  =>  '',
            'categoryID'  =>  '',
            'telephone'  =>  '',
            'promotMan'  =>  '',
            'school'  =>  '',
            'address'  =>  '',
        ];

        $data = array_intersect_key($data,$key);
        $validate = $this->validateData();
        if(!$validate->check($data)){
            return json_data(130,$validate->getError(),'');
        }

        $data['createTime'] = date('Y-m-d H:i:s',time());
        StudentEnroll::create($data);

        return json_data(0,$this->codeMessage[0],'');

    }

    /**
     * 验证表单信息
     * @return Validate
     */
    public function validateData(){
        $validate = new Validate([
            'realname|真实姓名'  =>  'require|chsAlpha|length:1,20',
            'cardsn|身份证号'  =>  'require|length:18',
            'admissionID|批次id'  =>  'require|integer',
            'categoryID|专业id'  =>  'require',
            'telephone|电话'  =>  'require|length:11',
            'promotMan|推荐人'  =>  'length:1,20',
            'school|毕业学校'  =>  'length:1,40',
            'address|家庭地址'  =>  'length:1,50',
        ]);

        return $validate;
    }

    /**
     * 获取招生详细信息
     */
    public function getaddetail(){
        $admission = Db::name('admission')->order('createdTime desc')->field('id,title,content')->find();
        return json_data(0,$this->codeMessage[0],$admission);
    }



}