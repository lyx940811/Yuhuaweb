<?php
/**
 * Created by PhpStorm.
 * User: m's
 * Date: 2017/12/29
 * Time: 12:01
 */
namespace app\manage\controller;

use think\Db;
use think\Validate;
use think\Loader;
use PDFConverter\PDFConverter;

class Userprofile extends Base{

    public function index(){

        $info = input('get.');
        $where = [];
        $data['realname']='';
        $data['sex']='';
        $data['depart']='';
        $data['majors']='';
        $data['class']='';
        if(!empty($info['realname'])){
            $data['realname']=$info['realname'];
            $where['a.realname'] = ['like',"%{$info['realname']}%"];
        }
        if(!empty($info['sex'])){
            $data['sex']=$info['sex'];
            $where['a.sex']=$info['sex']-1;//由于0的特殊性，页面搜索数据全部加1
        }
        if(!empty($info['depart'])){
            $data['depart']=$info['depart'];
            $where['b.depart']=$info['depart'];
        }
        if(!empty($info['majors'])){
            $data['majors']=$info['majors'];
            $where['b.majors']=$info['majors'];
        }
        if(!empty($info['class'])){
            $data['class']=$info['class'];
            $where['b.class']=$info['class'];
        }

        $list = Db::table('user_profile a')
            ->join('student_school b','a.userid=b.userid','LEFT')
//            ->join('student_class c','a.userid=c.userid','LEFT')
            ->join('classroom d','b.class=d.id','LEFT')
            ->field('a.*,b.grade,b.starttime,b.depart as bdepart,b.majors as bmajors,b.culture,b.academic,b.quarter,b.level,b.class,b.style,b.studentstatus,d.title')
            ->where($where)
            ->order('createdTime desc')
            ->paginate(20,false,['query'=>request()->get()]);

        $newlist = [];
        foreach ($list as $k=>$v){
            $newlist[$k] = $v;
            $newlist[$k]['home'] = Db::table('student_home')->where('userid',$v['userid'])->select();
            $newlist[$k]['depart'] = Db::table('category')->where('code',$v['bdepart'])->value('name');
            $newlist[$k]['majors'] = Db::table('category')->where('code',$v['bmajors'])->value('name');
        }

//        print_r($newlist);exit;

        $classroom = Db::table('classroom')->field('id,title')->select();
        $depart = Db::table('category')->field('id,code,name')->where('grade=2')->select();
        $category = Db::table('category')->field('code,name')->select();

        $this->assign('info',$data);
        $this->assign('list',$newlist);
        $this->assign('typename','学生列表');
        $this->assign('classroom',$classroom);
        $this->assign('category',$category);
        $this->assign('depart',$depart);
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    public function add(){
        $info = input('post.');


        $msg  =   [
            'sn.require' => '请输入学号',
            'sn.alphaDash' => '学号必须是英文或者下划线组成的',
            'realname.require' => '真实性别不能为空',
            'nation.require' => '请输入民族',
            'birthday.require' => '请输入生日',
            'mobile.require' => '手机号必须填写',
        ];
        $validate = new Validate([
            'sn'  => 'require|alphaDash',
            'realname'   => 'require',
            'nation'   => 'require',
            'birthday'   => 'require',
            'mobile'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('user_profile');

        $data = [
            'nickname' => $info['mobile'],
            'username'=> $info['mobile'],
            'password' => password_hash('123456',PASSWORD_DEFAULT),
            'type'=>3,
            'title'=>'static/index/images/avatar.png',
            'mobile'=>$info['mobile'],
            'createUserID'=>session('admin_uid'),
            'createdIp'=>request()->ip(),
            'createdTime'=>date('Y-m-d H:i:s',time()),
        ];
        Db::startTrans();
        $ok = Db::table('user')->insert($data);

        if($ok){
            $userid = Db::table('user')->getLastInsID();
            $cardpic = $info['cardpic'];
            $cardpic_ser = serialize(['front_pic'=>isset($cardpic[0])?$cardpic[0]:'','behind_pic'=>isset($cardpic[1])?$cardpic[1]:'']);

            //添加学生在校信息
            $data2 = [
                'sn' => $info['sn'],
                'userid'=>$userid,
                'realname' => $info['realname'],
                'sex'=>$info['sex'],
                'nation'=>$info['nation'],
                'cardpic'=>$cardpic_ser,
                'birthday'=>$info['birthday'],
                'idcard'=>$info['idcard'],
                'policy'=>$info['policy'],
                'mobile'=>$info['mobile'],
                'city'=>$info['city'],
                'household'=>$info['household'],
                'address'=>$info['address'],
                'createdTime'=>date('Y-m-d H:i:s',time()),
            ];

            $role_table->insert($data2);

            $sdata = [
                'userid'=>$userid,
                'grade'=>$info['grade'],
                'depart'=>$info['depart'],
                'majors'=>$info['majors'],
                'class'=>$info['class'],
                'culture'=>isset($info['culture'])?$info['culture']:'',
                'style'=>isset($info['style'])?$info['style']:'',
                'academic'=>isset($info['academic'])?$info['academic']:'',
                'starttime'=>isset($info['starttime'])?$info['starttime']:'',
                'quarter'=>isset($info['quarter'])?$info['quarter']:'',
                'studentstatus'=>isset($info['studentstatus'])?$info['studentstatus']:'',
                'level'=>isset($info['level'])?$info['level']:'',
                'createTime'=>date('Y-m-d H:i:s',time()),
                'createuserid'=>session('admin_uid'),

            ];
            Db::table('student_school')->insert($sdata);

            Db::table('student_class')->insert(
                [
                   'userid'=>$userid,
                    'classid'=>$info['class']
                ]

            );


            manage_log('101','003','添加学员',serialize($info),0);
            Db::commit();
            return ['info'=>'添加成功','code'=>'000'];
        }else{
            Db::rollback();
            return ['error'=>'添加失败','code'=>'400'];
        }
    }

    //excel导入
    public function import(){
        //引入文件（把扩展文件放入vendor目录下，路径自行修改）
        Loader::import('PHPExcel.PHPExcel');//手动引入PHPExcel.php
        Loader::import('PHPExcel.PHPExcel.IOFactory.PHPExcel_IOFactory');

        //获取表单上传文件
//        $file =$_FILES;
        $file = request()->file('excel');
        $info = $file->validate(['ext' => 'xlsx,xls'])->move(ROOT_PATH . 'public' . DS . 'upload' . DS . 'TaoBao');
        //数据为空返回错误
        if(empty($info)){
            $output['status'] = false;
            $output['info'] = '导入数据失败~';
            $this->ajaxReturn($output);
        }

        //获取文件名
        $exclePath = $info->getSaveName();
        //上传文件的地址
        $filename = ROOT_PATH . 'public' . DS . 'upload' . DS . 'TaoBao'. DS . $exclePath;

        //判断截取文件
        $extension = strtolower( pathinfo($filename, PATHINFO_EXTENSION) );

        //区分上传文件格式
        if($extension == 'xlsx') {
            $objReader =\PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }else if($extension == 'xls'){
            $objReader =\PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($filename, $encode = 'utf-8');
        }

        $excel_array = $objPHPExcel->getsheet(0)->toArray();   //转换为数组格式
        array_shift($excel_array);  //删除第一个数组(标题);
        $city = [];
        Db::startTrans();
        foreach($excel_array as $k=>$v) {
            if(!empty($v[0]) || !empty($v[1]) || !empty($v[2])){
                $role_table = Db::name('user_profile');
                $username=Db::table('user')->where('username',$v[2])->find();
                if(!empty($username)){
                    continue;
                }
                $data = [
                    'nickname' => $v[0],
                    'username'=> $v[2],
                    'password' => password_hash('123456',PASSWORD_DEFAULT),
                    'type'=>3,
                    'roles'=>5,
                    'title'=>'static/index/images/avatar.png',
                    'mobile'=>0,
                    'createUserID'=>session('admin_uid'),
                    'createdIp'=>request()->ip(),
                    'createdTime'=>date('Y-m-d H:i:s',time()),
                    'status'=>1,
                ];
                $ok = Db::table('user')->insert($data);

                if($ok) {
                    $userid = Db::table('user')->getLastInsID();
                    //添加学生在校信息
                    $array=['男'=>0,'女'=>1,'保密'=>2];
                    $data2 = [
                        'sn' => 0,
                        'userid' => $userid,
                        'realname' => $v[0],
                        'sex' => $array[$v[1]],
                        'nation' => empty($v[4]) ? 0 : $v[4],
                        'cardpic' => 0,
                        'birthday' => empty($v[3]) ? 0 : $v[3],
                        'idcard' => $v[2],
                        'policy' => 0,
                        'mobile' => 0,
                        'city' => empty($v[7]) ? 0 : $v[7],
                        'household' => empty($v[6]) ? 0 : $v[6],
                        'address' => 0,
                        'createdTime' => date('Y-m-d H:i:s', time()),
                    ];

                    $role_table->insert($data2);
                    $categoryid = Db::table('category')->where('name', $v[10])->find();
                    $classid = Db::table('classroom')->where('title', $v[11])->value('id');
                    $departcode=Db::table('category')->where('code',$categoryid['parentcode'])->value('code');
                    $sdata = [
                        'userid' => $userid,
                        'grade' => 0,
                        'depart' => empty($departcode) ? 0 : $departcode,
                        'majors' => empty($categoryid['code']) ? 0 : $categoryid['code'],
                        'class' => empty($classid) ? 0 : $classid,
                        'culture' => empty($v[5]) ? 0 : 1,
                        'style' => empty($v[9]) ? 0 : $v[9],
                        'academic' => empty($v[8]) ? 0 : $v[8] + 1,
                        'starttime' => date('Y-m-d H:i:s', time()),
                        'quarter' => 0,
                        'studentstatus' => 0,
                        'level' => 0,
                        'createTime' => date('Y-m-d H:i:s', time()),
                        'createuserid' => session('admin_uid'),

                    ];
                    Db::table('student_school')->insert($sdata);

                    Db::table('student_class')->insert(
                        [
                            'userid' => $userid,
                            'classid' => empty($classid) ? 0 : $classid,
                        ]

                    );


                    manage_log('101', '003', '添加学员', serialize($v), 0);

                }else{
                    Db::rollback();
                    return ['error'=>'导入失败','code'=>'400'];
                }
            }else{
                break;
            }
        }
        Db::commit();
        return ['info'=>'导入成功','code'=>'000'];
    }
    public function edit(){

        $info = input('post.');

        $msg  =   [
            'sn.require' => '请输入学号',
            'realname.require' => '真实性别不能为空',
            'nation.require' => '请输入民族',
            'birthday.require' => '请输入生日',
            'mobile.require' => '手机号必须填写',
        ];
        $validate = new Validate([
            'sn'  => 'require',
            'realname'   => 'require',
            'nation'   => 'require',
            'birthday'   => 'require',
            'mobile'   => 'require',
        ],$msg);

        $validate->check($info);

        $error = $validate->getError();//打印错误规则

        if(is_string($error)){
            return ['error'=>$error,'code'=>'200'];
        }

        $role_table = Db::name('user_profile');

        $id = $info['rid']+0;

        $userprofile = $role_table->field('userid,cardpic')->where('id',$id)->find();

        $cardpic = $info['cardpic'];

        if($info['cardpic']!=''){
            $cardpic_ser = serialize(['front_pic'=>isset($cardpic[0])?$cardpic[0]:'','behind_pic'=>isset($cardpic[1])?$cardpic[1]:'']);
        }else{
            $ser = unserialize($userprofile['cardpic']);
            $cardpic_ser = serialize(['front_pic'=>isset($cardpic[0])?$cardpic[0]:isset($ser['front_pic'])?$ser['front_pic']:'','behind_pic'=>isset($cardpic[1])?$cardpic[1]:isset($ser['behind_pic'])?$ser['behind_pic']:'']);
        }


        $data = [
            'sn' => $info['sn'],
            'realname' => $info['realname'],
            'sex'=>$info['sex'],
            'nation'=>$info['nation'],
            'cardpic'=>$cardpic_ser,
            'birthday'=>$info['birthday'],
            'idcard'=>$info['idcard'],
            'policy'=>$info['policy'],
            'mobile'=>$info['mobile'],
            'city'=>$info['city'],
            'household'=>$info['household'],
            'address'=>$info['address'],
        ];
        Db::startTrans();
        $ok = $role_table->where('id',$id)->update($data);


        if(is_numeric($ok)){

            Db::table('user')->where('id='.$userprofile['userid'])->update(
                [
                    'nickname'=>$info['realname'],
//                    'username'=>$info['mobile'],
                    'mobile'=>$info['mobile']
                ]
            );

            $isHave = Db::table('student_school')->where('userid='.$userprofile['userid'])->find();

            if($isHave){
                //如果有这条信息,修改学员在校信息
                $sdata = [
                    'grade'=>$info['grade'],
                    'depart'=>$info['depart'],
                    'majors'=>$info['majors'],
                    'class'=>$info['class'],
                    'culture'=>$info['culture'],
                    'style'=>$info['style'],
                    'academic'=>$info['academic'],
                    'starttime'=>$info['starttime'],
                    'quarter'=>$info['quarter'],
                    'studentstatus'=>$info['studentstatus'],
                    'level'=>$info['level'],
                ];
                Db::table('student_school')->where('userid',$userprofile['userid'])->update($sdata);

//                echo Db::table('student_school')->getLastSql();exit;

            }else{
                //如果没有这条在校信息，就重新添加一条
                $sdata = [
                    'userid'=>$userprofile['userid'],
                    'grade'=>$info['grade'],
                    'depart'=>$info['depart'],
                    'majors'=>$info['majors'],
                    'class'=>$info['class'],
                    'culture'=>isset($info['culture'])?$info['culture']:'',
                    'style'=>isset($info['style'])?$info['style']:'',
                    'academic'=>isset($info['academic'])?$info['academic']:'',
                    'starttime'=>isset($info['starttime'])?$info['starttime']:'',
                    'quarter'=>isset($info['quarter'])?$info['quarter']:'',
                    'studentstatus'=>isset($info['studentstatus'])?$info['studentstatus']:'',
                    'level'=>isset($info['level'])?$info['level']:'',
                    'createTime'=>date('Y-m-d H:i:s',time()),
                    'createuserid'=>session('admin_uid'),

                ];
                Db::table('student_school')->insert($sdata);
            }

            $isHave_class = Db::table('student_class')->where('userid='.$userprofile['userid'])->find();

            if($isHave_class){//如果存在这个人对应的这个班级，直接修改

                Db::table('student_class')->where('userid='.$userprofile['userid'])->update(
                    [
//                        'userid'=>$userprofile['userid'],
                        'classid'=>$info['class']
                    ]
                );
            }else{//如果没有这个人对应的班级，直接创建
                Db::table('student_class')->insert(
                    [
                        'userid'=>$userprofile['userid'],
                        'classid'=>$info['class']
                    ]

                );
            }
            Db::commit();

            manage_log('101','004','修改学员',serialize($info),0);

            return ['info'=>'修改成功','code'=>'000'];
        }else{
            Db::rollback();
            return ['error'=>'修改失败','code'=>'200'];
        }
    }

    public function upload(){

        $id = $_GET['id']+0;
        $file = new Upload();
        $res = $file->uploadPic($_FILES,'userprofile');

        $res['path'] = $res['newfile'.$id]['path'];
        $res['code'] = $res['newfile'.$id]['code'];
        return $res;
    }


    public function delete(){

        $id = $_GET['rid']+0;
        $ok=Db::name('student_home')->field('Flag,createtime')->where("id='$id'")->delete();
        if(is_numeric($ok)){
            return ['info'=>'删除成功','code'=>'000'];//改为删除
        }else{
            return ['info'=>'删除失败','code'=>'400'];//改为删除
        }
    }

    public function addhome(){

        $info = input('post.');

        $data = [
            'userid'=>$info['userid'],
            'name'=>$info['name'],
            'phone'=>$info['phone'],
            'relation'=>$info['relation'],
            'createuserid'=>session('admin_uid'),
            'createTime'=>date('Y-m-d H:i:s',time())
        ];

        $ok = Db::table('student_home')->insert($data);

        if($ok){

            return ['info'=>'添加成功','code'=>'000'];
        }else{
            return ['error'=>'添加失败','code'=>'100'];
        }

    }


    public function editstudenthome(){
        $id=$this->request->param('id');
        $list=Db::table('student_home')->where('id', $id)->find();
        return ['data'=>$list];
    }


    public function studyresult(){

        $userid=$this->request->param('id')+0;//在学生列表跳转到本列表是使用
        $info = input('get.');
        $search='';
        $where = [];
        if(!empty($info['realname'])){
            $search=$info['realname'];
            $where['u.username'] = ['like',"%{$info['realname']}%"];
        }
        if($userid){
            $where['a.userid']=$userid;
        }
        $list = Db::table('study_result_log a')
            ->field('a.*,b.title,c.title ctit,u.username,ct.title as cttitle,ct.length')
            ->join('course b','a.courseid=b.id','LEFT')
            ->join('course_chapter c','a.chapterid=c.id','LEFT')
            ->join('user u','a.userid=u.id','LEFT')
            ->join('course_task ct','b.id=ct.courseId')
            ->where($where)
            ->paginate(20,['query'=>$info]);
        $test=new Studyresult();
        $data = $test->Percentage($list);
        $course = Db::table('course')->field('id,title')->select();

        $this->assign('list',$data);
        $this->assign('course',$course);
        $this->assign('search',$search);
        $this->assign('userid',$userid);
        $this->assign('typename','学习记录');
        $this->assign('page',$list->render());
        return $this->fetch();


    }

    public function integrallist(){
        //接收从学生列表传过来的userid
        $userid=$this->request->param('id')+0;
        $info = input('get.');
        $search='';
        $where = [];
        if(!empty($info['name'])){
            $search=$info['name'];
            $where['u.username'] = ['like',"%{$info['name']}%"];
        }
        if($userid){
            $where['u.id']=$userid;
        }
        $where['rpf.type'] = 'outflow';
        $list = DB::table('user')
            ->alias('u')
            ->join('user_profile up','u.id=up.userid')
            ->join('reward_point_flow rpf','u.id=rpf.userid')
            ->join('reward_point rp','u.id=rp.userid')
            ->field('rp.*,u.username,up.sn,sum(rpf.point) as point')
            ->group('rpf.userid')
            ->where($where)
            ->paginate(20,false,['query'=>request()->get()]);//查找积分规则列表数据
        $this->assign('list',$list);
        $this->assign('search',$search);
        $this->assign('page',$list->render());
        return $this->fetch();
    }

    public function selectcategory(){
        $info = input('get.');
        $category = Db::table('category')->field('code,name')->where(['parentcode'=>$info['code']])->select();

        if ($category){

            return ['info'=>$category,'code'=>000];
        }else{

            return ['error'=>'没有此数据','code'=>100];

        }


    }

}