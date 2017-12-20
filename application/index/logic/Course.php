<?php

namespace app\index\logic;

use app\index\model\Course as CourseModel;
use app\index\model\CourseFile;
use app\index\model\Question;
use app\index\model\Testpaper;
use app\index\model\User   as UserModel;
use app\index\model\CourseFile   as CourseFileModel;
use think\Exception;
use think\Loader;
use think\Config;
use think\Request;
use think\Validate;
class Course extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建课程
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
        $base_key = [
            'id'    =>  '',
            'title' =>  '',
            'status'=>  '',
            'smallPicture'  =>    ''
        ];
        $course = CourseModel::get([ 'id' => $data['courseid'] ]);

        if($course){
            $course = $course->toArray();
            switch ($data['type']){
                case 'base':
                    //基本信息
                    $key = ['title'=>'','subtitle'=>'','tags'=>'','categoryId'=>'','status'=>''];
                    return $this->getCourseDetailByKey($course,$base_key,$key,$data['type']);
                    break;
                case 'detail':
                    //详细信息
                    $key = ['about'=>'','goals'=>'','audiences'=>''];
                    return $this->getCourseDetailByKey($course,$base_key,$key,$data['type']);
                    break;
                case 'cover':
                    //封面图片
                    $key = ['middlePicture'=>'','largePicture'=>''];
                    return $this->getCourseDetailByKey($course,$base_key,$key,$data['type']);
                    break;
                case 'teachers':
                    //教师设置
                    $key = ['teacherIds'=>''];
                    return $this->getCourseDetailByKey($course,$base_key,$key,$data['type']);
                    break;
                case 'files':
                    //课程文件
                    return $this->getCourseFiles($course,$base_key,$data['courseid']);
                    break;
                case 'testpaper':
                    //得到课程下所有试卷
                    return $this->getCourseTestpaper($course,$base_key,$data['courseid']);
                    break;
                case 'question':
                    //得到课程下所有题目
                    return $this->getCourseQuestion($course,$base_key,$data['courseid']);
                    break;
                default:
                    break;
            }
        }
        else{
            return json_data(200,$this->codeMessage[200],'');
        }
    }


    public function getCourseDetailByKey($course,$base_key,$key,$type=''){
        if(!isset($key)){
            return json_data(210,$this->codeMessage[210],'');
        }
        //取交集
        $key         = array_merge($base_key,$key);
        $course_info = array_intersect_key($course,$key);

        if( $type == 'teachers'){
            $teacher_list                = $this->getCourseTeacherList($course_info);
            $course_info['teacher_list'] = $teacher_list;
        }
        return json_data(0,$this->codeMessage[0],$course_info);
    }

    public function getCourseFiles($course,$base_key,$courseid){
        $files = CourseFileModel::all([ 'courseid ' => $courseid ]);

        $course_info = array_intersect_key($course,$base_key);
        $course_info['files'] = $files;
        return json_data(0,$this->codeMessage[0],$course_info);
    }

    public function getCourseTestpaper($course,$base_key,$courseid){
        $testpaper = Testpaper::where('courseId',$courseid)->column('id,name,score,itemCount,createTime');

        $course_info = array_intersect_key($course,$base_key);
        $course_info['testpaper'] = $testpaper;
        return json_data(0,$this->codeMessage[0],$course_info);
    }

    public function getCourseQuestion($course,$base_key,$courseid){
        $question = Question::where('courseId',$courseid)->column('id,type,stem,createdTime');

        $course_info = array_intersect_key($course,$base_key);
        $course_info['question'] = $question;
        return json_data(0,$this->codeMessage[0],$course_info);
    }


    public function setCourseInfo($courseid,$type,$data){

    }

    /**
     * 更新课程对应部分的内容
     * @param $courseid 课程id
     * @param $data     待更新数据
     * @return array
     */
    public function updateCourseInfo($courseid,$data){
        if(!$findcourse = CourseModel::get($courseid)){
            json_data(200,$this->codeMessage[200],'');
        }
        $course = new CourseModel;
        $course->save( $data ,[ 'id' => $courseid ]);
        return json_data(0,$this->codeMessage[0],'');
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

    public function getCourseFile($courseid){
        if(!CourseModel::get($courseid)){
            throw new Exception($this->codeMessage[200],200);
        }
        $request = Request::instance();
        $fileList = CourseFile::all(['courseid'=>$courseid]);
        foreach ($fileList as &$f){
            $f['filesize'] = attrFilesize($f['filesize']);
            $f['filepath'] = $request->domain().DS.$f['filepath'];
            unset($f['source'],$f['lessonid']);
        }
        return $fileList;
    }




}
?>
