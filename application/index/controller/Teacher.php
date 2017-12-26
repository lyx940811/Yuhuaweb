<?php
namespace app\index\controller;

use Couchbase\Document;
use think\Loader;
use think\Db;
use think\Exception;
use app\index\model\QuestionType;
use app\index\model\Testpaper as TestpaperModel;
use app\index\model\TestpaperItem;
use app\index\model\CourseFile;
use app\index\model\Course;
class Teacher extends User
{
    public $LogicTestpaper;
    public $LogicQuestion;
    public $LogicUpload;
    public $LogicCourse;
    public function __construct(){
        parent::__construct();
        $this->LogicTestpaper  = Loader::controller('Testpaper','logic');
        $this->LogicQuestion   = Loader::controller('Question','logic');
        $this->LogicUpload     = Loader::controller('Upload','logic');
        $this->LogicCourse     = Loader::controller('Course','logic');
    }

    /**
     * 【试卷部分】
     */

    /**
     * 新增/更新试卷
     * @return array
     */
    public function editpaper(){
        try{
            $id = $this->data['id'];
            $data = [
                'courseid'  =>  3,
                'name'      =>  '试卷名称',
                'description'   =>  '',
                'userid'    =>  2,
                'createTime'=>  date('Y-m-d H:i:s',time())
            ];
            $this->LogicTestpaper->editPaper($id,$data);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }
    }

    /**
     * 得到某课程下的所有试卷
     * @return array
     * @throws Exception
     */
    public function getpaperlist(){
        $courseid = 5;//$this->data['courseid'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        if(!Course::get($courseid)){
            throw new Exception($this->codeMessage[200],200);
        }
        if(empty($page)){
            $page = 1;
        }
        $paperList = Db::name('testpaper')
            ->where('courseid',$courseid)
            ->page($page,10)
            ->select();//TestpaperModel::all(['courseid'=>$courseid]);
        foreach ( $paperList as &$p){
            $p['username'] = Db::name('user')
                ->where('id',$p['userid'])
                ->value('username');
        }
        return json_data(0,$this->codeMessage[0],$paperList);
    }

    /**
     * 删除试卷
     * @return array
     * @throws Exception
     */
    public function delpaper(){
        $paperid = $this->data['id'];
        if(is_array($paperid)){
            if(!TestpaperModel::all($paperid)){
                return json_data(400,$this->codeMessage[400],'');
            }
            TestpaperModel::destroy($paperid);
        }
        else{
            if(!TestpaperModel::get($paperid)){
                return json_data(400,$this->codeMessage[400],'');
            }
            TestpaperModel::destroy($paperid);
        }
        return json_data(0,$this->codeMessage[0],'');
    }

    /**
     * 得到某个试卷的详细信息
     */
    public function getpaperdetail(){
        $paperid = $this->data['id'];
        if(!$paperDetail = TestpaperModel::get($paperid)){
            return json_data(400,$this->codeMessage[400],'');
        }
        return $paperDetail;
    }
    /**
     * 得到某个试卷下的所有问题
     */
    public function getqstlist(){
        $paperid = 1;//$this->data['id'];

        $type  = array();
        $final = array();

        if(!TestpaperModel::get($paperid)){
            return json_data(400,$this->codeMessage[400],'');
        }

        $qstList = Db::name('testpaper_item')
            ->alias('ti')
            ->join('question q','ti.questionid = q.id')
            ->field('ti.id,ti.score,ti.seq,q.stem,q.difficulty,q.type,q.id as questionid')
            ->select();
        if($qstList){
            foreach ( $qstList as &$q ){
                $q['typename'] = Db::name('question_type')
                    ->where('code',$q['type'])
                    ->value('name');
                $type[] = $q['typename'];
            }
            $type = array_unique($type);
            $List = $qstList;
            foreach ( $type as $t ){
                foreach ( $List as $l ){
                    if($l['typename']==$t){
                        $final[$t][] = $l;
                    }
                }
            }
        }
        return json_data(0,$this->codeMessage[0],$final);
    }

    /**
     * 编辑试卷下的问题(更新&增加)
     */
    public function editquestion(){
        $paperid     = 1;//$this->data['paperid'];
        $passedScore = 2.5;//$this->data['passedScore'];
        $question    = [
            ['questionId'=>10,'score'=>2.5,'questiontype'=>'001'],
            ['questionId'=>12,'score'=>3.5,'questiontype'=>'002'],
        ];//$this->data['question'];

        if(!$paper = TestpaperModel::get($paperid)){
            return json_data(400,$this->codeMessage[400],'');
        }
        $paper->save(['passedScore'=>$passedScore,
            'itemCount' =>  count($question),
            'score'     =>  array_sum(array_column($question,'score')),
        ],['id'=>$paperid]);

        foreach ( $question as $q ){
            if($item_qst = TestpaperItem::get(['questionId'=>$q['questionId'],'paperID'=>$paperid])){
                $item_qst->isUpdate(true)
                    ->save($q);
            }
            else{
                $item_qst = new TestpaperItem;
                $q['paperID'] = $paperid;
                $item_qst->isUpdate(false)
                    ->save($q);
            }
        }
        return json_data(0,$this->codeMessage[0],'');
    }

    /**
     * 得到某试卷下还未被添加的题目列表
     */
    public function getaddqst(){
        $paperid  = 1;//$this->data['paperid'];
        $item_qst = array();

        if(!TestpaperModel::get($paperid)){
            return json_data(400,$this->codeMessage[400],'');
        }

        $item_qst = Db::name('testpaper_item')
            ->where('paperID',$paperid)
            ->field('questionId')
            ->select();
        $item_qst = implode(',',array_column($item_qst,'questionId'));

        $question = Db::name('question')
            ->where('id','not in',$item_qst)
            ->field('id,type,stem,score')
            ->select();//题干，分值，类型

        if($question){
            foreach ( $question as &$q ){
                $q['typename'] = Db::name('question_type')
                    ->where('code',$q['type'])
                    ->value('name');
            }
        }

        return json_data(0,$this->codeMessage[0],$question);
    }

    /**
     * 【课程部分】
     */

    /**
     * 添加课程（教师）
     */
    public function createcourse(){
        $title  = $this->data['title'];
        $userid = $this->data['userid'];
        $res    = $this->LogicCourse->createCourse($title,$userid);
        return $res;
    }

    /**
     * 教师在我的教学-课程中【获得】课程信息
     * 现有type对应：
     * base（基本信息）
     * detail（详细信息）
     * cover（封面图片）
     * files（课程文件）
     * testpaper（试卷管理）
     * question（题目管理）
     * 计划任务//course_task
     * 计划设置//旧模板course_v8，新的没有对应表
     * 营销设置
     * teachers(教师设置)
     *
     * 学员管理
     * 试卷批阅
     * 作业批阅
     * 学习数据
     * 订单查询
     * 教学计划管理
     */
    public function getcourse(){
        $data['courseid'] = 5;
        $data['type'] = 'question';
        $res = $this->LogicCourse->getCourseInfo($data);
        return $res;
    }



    /**
     * 教师在我的教学-课程中【设置、更新】课程信息
     * 现有type对应：
     * base（基本信息）
     * detail（详细信息）
     * （封面图片）
     * （课程文件）
     * （试卷管理）
     * （题目管理）
     * 计划任务//course_task
     * 计划设置//旧模板course_v8，新的没有对应表
     * 营销设置
     * （教师设置)
     *
     * 学员管理
     * 试卷批阅
     * 作业批阅
     * 学习数据
     * 订单查询
     * 教学计划管理
     */
    public function setcourse(){
        $type = 'cover';//$this->data['type'];
        $courseid = 5;//$this->data['courseid'];
        $data = [
            'title'=>'123update test',
            'subtitle'=>'vice title',
            'tags'=>'test|tags',
            'categoryId'=>1,
            'status'=>3,
        ];//$this->data;
        switch ($type){
            case 'base':
                //基本信息
                $key = ['title'=>'','subtitle'=>'','tags'=>'','categoryId'=>'','status'=>''];
                $data = array_intersect_key($data,$key);
                return $this->LogicCourse->updateCourseInfo($courseid,$data);
                break;
            case 'detail':
                //详细信息
                $key = ['about'=>'','goals'=>'','audiences'=>''];
                $data = array_intersect_key($data,$key);
                return $this->LogicCourse->updateCourseInfo($courseid,$data);
                break;
            case 'cover':
                //上传图片
                $file = $_FILES;
                $res = $this->LogicCourse->uploadFile($file);
                var_dump($res);
                if(!empty($res)){
                    //update

                }
                break;
        }

    }

    /**
     * 课程管理-题目管理-得到本课程下的所有题目
     * 已写入文档
     * @return array
     */
    public function getcqlist(){
        try{
            $courseid = $this->data['courseid'];
            !empty($this->data['page'])?$page = $this->data['page']:$page = 1;

            $qstList  = $this->LogicQuestion->getQuestionList($courseid,$page);
            return json_data(0,$this->codeMessage[0],$qstList);
        }
        catch ( \ErrorException $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$this->codeMessage[$e->getCode()],'');
        }
    }

    /**
     * 删除课程文件
     */
    public function delfile(){
        try{
            $id = $this->data['id'];
            if(is_array($id)){
                $fileList = CourseFile::all($id);
            }
            else{
                $fileList = CourseFile::get($id);
            }
            if(!$fileList){
                throw new Exception($this->codeMessage[220],220);
            }
            CourseFile::destroy($id);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }

    }

    /**
     * 教师页面上传课程文件
     * 记得修改php.ini中的上传选项
     * 还没有上传type的限制，还没有大小的限制
     */
    public function uploadfile(){
        try{
            $courseid = 2;//$this->data['courseid'];
            $files = $_FILES;
            $res = $this->LogicUpload->uploadFile($files);

            foreach ($res as &$r){
                $r['courseid']   = $courseid;
                $r['createTime'] = date('Y-m-d H:i:s',time());
                $name_type = explode('.',$r['filename']);
                //确定文件类型
                $type = null;
                if($name_type[1]){
                    $type = Db::name('course_file_type')
                        ->where('ietype|firefoxtype',$r['type'])
                        ->where('simpletype',$name_type[1])
                        ->value('simpletype');
                }
                !empty($type)?$r['type'] = $type:$r['type'] = 'others';
            }

            $coursefile = new CourseFile();
            $coursefile->saveAll($res);
            return json_data(0,$this->codeMessage[0],$res);
        }
        catch( Exception $e){
            return json_data($e->getCode(),$e->getMessage(),'');
        }
    }

    /**
     * 改变课程发布的状态
     * @return mixed
     */
    public function chcoursestatu(){
        $courseid = $this->data['courseid'];
        $status   = $this->data['status'];

        return $this->LogicCourse->chCourseStatus($courseid,$status);
    }

    /**
     * 【问答部分】
     */

    /**
     * 教师获得我的教学-学员问答 列表
     * totalPage 总页数
     * page 当前页码
     * askList:
    'id'        => int 1
    'userID'    => int 1
    'content'   => string '问问问' (length=9)
    'addtime'   => string '2017-12-21 16:47:48' (length=19)
    'courseid'  => int 3
    'teacherid' => int 3
    'title'     => string '工业机器人技术基础' (length=27)
    'answerUserID'      => int 1
    'LateranswerTime'   => string '2017-12-21 16:58:20' (length=19)
    'answerUsername'    => string '中1文1调用测试123' (length=23)
     */
    public function gstuqlist(){
        $teacherid = $this->data['id'];
        !empty($this->data['page'])?$page = $this->data['page']:$page = 1;
        $askList = Db::name('asklist')
            ->alias('al')
            ->join('course c','c.id = al.courseid')
            ->field('al.*,c.userid as teacherid,c.title')
            ->where('c.userid',$teacherid)
            ->page($page,10)
            ->select();
        foreach ( $askList as &$a){
            $answer = Db::name('ask_answer')
                ->where('askID',$a['id'])
                ->order('addtime desc')
                ->field('answerUserID,addtime as LateranswerTime')
                ->find();
            if($answer){
                $a = array_merge($a,$answer);
                $a['answerUsername']  = Db::name('user')->where('id',$a['answerUserID'])->value('username');
                $a['LateranswerTime'] = time_tran($a['LateranswerTime']);
            }
        }
        $total = Db::name('asklist')
            ->alias('al')
            ->join('course c','c.id = al.courseid')
            ->field('al.*,c.userid as teacherid,c.title')
            ->where('c.userid',$teacherid)
            ->count();
        $data = [
            'totalPage' => ceil($total/10),
            'askList'   => $askList,
            'page'      => $page
        ];
        return json_data(0,$this->codeMessage[0],$data);
    }


    /**
     * 【题目部分】
     */

    /**
     * 增加/更新一个题目（未完成，对于序列化的内容）
     * @return array
     */
    public function addqst(){
        try{
//            $newdata = [
//                'type'          =>  $this->data['type'],        //和question_type对应的code
//                'stem'          =>  $this->data['stem'],        //题干，带html标签（富文本编辑器内的内容）
//                'createUserid'  =>  $this->data['createUserid'],//创建人id
//                'analysis'      =>  $this->data['analysis'],    //分析，带html标签（富文本编辑器内的内容）
//                'score'         =>  $this->data['score'],       //分数，float
//                'answer'        =>  $this->data['answer'],      //答案，json编码的数组，0键对应正确答案,多选题的时候有多个键值
//                'metas'         =>  $this->data['metas'],       //题目元信息，json编码过，每一个键值对应每一个选项内容，answer的答案代表这里的键名
//                'difficulty'    =>  $this->data['difficulty'],  //难易程度
//                'courseId'      =>  $this->data['courseId'],    //课程id
//                'createdTime'   =>  date('Y-m-d H:i:s',time()), //创建时间
//            ];
            $id = "";//5;//$this->data['id'];
            $newdata = [
                'type'          =>  "002",
                'stem'          =>  '题222asd干',
                'createUserid'  =>  3,
                'analysis'      =>  '题目分析',
                'score'         =>  5,
                'answer'        =>  '0',
                'metas'         =>  '{}',//序列化的表单
                'difficulty'    =>  'normal',
                'courseId'      =>  3,
                'createdTime'   =>  date('Y-m-d H:i:s',time()),
            ];
            if($id){
                $this->LogicTestpaper->updateQuestion($newdata,$id);
            }
            else{
                $this->LogicTestpaper->createQuestion($newdata);
            }
            return json_data(0,$this->codeMessage[0],'');
        }
        catch( Exception $e ){
            $code = $e->getCode();
            if($code==130){
                return json_data($code,$e->getMessage(),'');
            }
            else{
                return json_data($code,$this->codeMessage[$code],'');
            }
        }
    }

    /**
     * 删除题目
     * 已写入文档
     */
    public function delqst(){
        try{
            $id = 4;//$this->data['id'];//id可以为1，也可以为[1,2,3]
            $this->LogicTestpaper->delQuestion($id);
            return json_data(0,$this->codeMessage[0],'');
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$this->codeMessage[$e->getCode()],'');
        }

    }

    /**
     * 搜索题目
     * 已写入文档
     * $type        题目类型      没有的话传空
     * $courseid    课程id        没有的话传空
     * $keywords    关键词        没有的话传空
     */
    public function searchqst(){
        try{
            $type       = $this->data['type'];
            $courseid   = $this->data['courseid'];
            $keywords   = $this->data['keywords'];
            $page       = $this->data['page'];
            $res = $this->LogicTestpaper->searchQuestion($type,$courseid,$keywords,$page);
            return json_data(0,$this->codeMessage[0],$res);
        }
        catch ( Exception $e ){
            return json_data($e->getCode(),$e->getMessage(),'');
        }
    }
}
