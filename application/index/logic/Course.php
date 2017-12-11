<?php

namespace app\index\logic;

use app\index\model\Course as CourseModel;
use app\index\model\User   as UserModel;
use think\Loader;
use think\Config;
use think\Validate;
class Course extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $title    课程标题
     * @param $userid   创建课程的教师id
     * @return array
     */
    public function createCourse($title,$userid){

        if( UserModel::get( $userid ) ){
            $validate = new Validate([
                'userid'    =>  'require',
                'title'      => 'require|length:1,200',
            ]);
            $data = [
                'userid'    =>  $userid,
                'title'     =>  $title,
            ];
            if(!$validate->check($data)){
                // 验证失败 输出错误信息
                return json_data(130,$validate->getError(),'');
            }

            $data['createdTime'] =  date('Y-m-d H:i:s',time());
            $course  =  CourseModel::create($data);

            return json_data(0,$this->codeMessage[0],$course->id);
        }
        else{
            return json_data(110,$this->codeMessage[110],'');
        }

    }

    /**
     * 通过传入不同的type来获取课程不同的详细内容
     * @param $data['type'] 请求的具体课程的类型
     * @param $data['courseid'] 课程id
     * @return array
     */
    public function getCourseInfo($data){
        //顶部基础课程信息的key
        $base_key = ['id'=>'','title'=>'','status'=>'','smallPicture'=>''];
        $course = CourseModel::get([ 'id' => $data['courseid'] ])->toArray();

        if($course){
            switch ($data['type']){
                case 'base':
                    //基本信息
                    $key = ['title'=>'','subtitle'=>'','tags'=>'','categoryId'=>''];
                    break;
                case 'detail':
                    //详细信息
                    $key = ['about'=>'','goals'=>'','audiences'=>''];
                    break;
                case 'cover':
                    //封面图片
                    $key = ['middlePicture'=>'','largePicture'=>''];
                    break;
                case 'teachers':
                    //封面图片
                    $key = ['teacherIds'=>''];
                    break;
                default:
                    break;
            }

            if(!isset($key)){
                return json_data(210,$this->codeMessage[210],'');
            }
            //取交集
            $key         = array_merge($base_key,$key);
            $course_info = array_intersect_key($course,$key);

            if($data['type']=='teachers'){
                $teacher_list                = $this->getCourseTeacherList($course_info);
                $course_info['teacher_list'] = $teacher_list;
            }

            return json_data(0,$this->codeMessage[0],$course_info);
        }
        else{
            return json_data(200,$this->codeMessage[200],'');
        }
    }

    /**
     * 通过course中的teacherIds中的内容来获取教师列表
     * @param $course_info-里面包含基础的课程信息+需要用到的 teacherIds教师id字符串
     * @return array
     */
    public function getCourseTeacherList($course_info){
        $teacher_ids  = explode('|',$course_info['teacherIds']);
        $user         = UserModel::all($teacher_ids);
        $teacher_list = array();
        $user_key     = ['id'=>'','nickname'=>'','title'=>''];

        if($user){
            $user = collection($user)->toArray();
        }
        foreach ($user as $item){
            $teacher_list[] = array_intersect_key($item,$user_key);
        }

        return $teacher_list;
    }

    /**
     * 改变课程发布状态
     * @param $courseid  课程id
     * @param $status   0未发布、1已发布
     * @return array
     */
    public function chCourseStatus($courseid,$status){
        if($course = CourseModel::get([ 'id' => $courseid ])){

            $validate = new Validate([
                'status' => 'in:0,1'
            ]);
            $data     = [
                'status' => $status
            ];
            if(!$validate->check($data)){
                return json_data(130,$validate->getError(),'');
            }

            $course->status = $status;
            $course->save();

            return json_data(0,$this->codeMessage[0],'');
        }
        else{
            return json_data(200,$this->codeMessage[200],'');
        }
    }




}
?>
