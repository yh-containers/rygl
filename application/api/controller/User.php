<?php
namespace app\api\controller;

class User extends Common
{
    protected $is_need_auth = true;

    protected $ignore_auth = 'register,login';

    //用户注册
    public function register()
    {
        $input_data = $this->request->param();

        $validate = new \app\common\validate\Users();
        $validate->scene(self::SCENE);

        $model =  new \app\common\model\Users();
        $result = $model->actionAdd($input_data,$validate);

        return jsonOut($result['msg'],$result['code']);
    }

    //用户登录
    public function login()
    {
        $account = $this->request->param('account');
        $password = $this->request->param('password');

        empty($account) && abort(40001,'请输入账号');
        empty($password) && abort(40001,'请输入密码');

        $model =  new \app\common\model\Users();
        $token = $model->accountLogin($account,$password);
        return jsonOut('登录成功',1,$token);
    }


    //修改用户属性
    public function modAttr()
    {
        $input_data = $this->request->param();
        $allow_field = ['name','header_img','province','city','area','addr'];
        $model =  new \app\common\model\Users();
        $model = $model->find($this->user_id);
        empty($model) && abort(40001,'用户信息异常');

        $bool = $model->allowField($allow_field)->save($input_data);
        return jsonOut($bool?'修改成功':'修改失败',(int)$bool);
    }

    //刷新用户凭证
    public function refreshToken()
    {
        $model =  new \app\common\model\Users();
        $token = $model->refreshToken($this->user_id);
        return jsonOut('获取成功',1,$token);
    }

    //用户打卡
    public function sign()
    {
        $input_data = $this->request->param();
        $model =  new \app\common\model\UserSignIn();
        list($bool,$msg,$time,$status,$nsm,$nss) = $model->sign($this->user_id,$this->company_id,$input_data);
        $data =[
            'sign_time' =>date('Y-m-d H:i:s',$time),
            'status' => $status, //签到状态 1上班 2下班
            'nsm' => $nsm, //误差时间
            'nss' => $nss, //打卡状态 0正常 1迟到 2早退
        ] ;
        return jsonOut($msg,(int)$bool,$bool?$data:[]);
    }

    //用户签到记录
    public function records()
    {

        $year = $this->request->param('year',date('Y'),'intval');
        $month = $this->request->param('month',date('m'),'intval');
        $day = $this->request->param('day',0,'intval');
        $user_id = $this->request->param('user_id',$this->user_id,'intval');

        $query_day = $day<=0 ? ($year.'-'.$month) :( $year.'-'.$month.'-'.$day);

        $model =  new \app\common\model\UserSignIn();
        $list = $model->records($this->company_id,$query_day,$user_id);
        $need_fields = ['s_time'=>0,'times'=>0,'status'=>0,'nss'=>0,'nsm'=>0];
        $list = filter_data($list,$need_fields,2);
        $list = handle_data_day($list,'s_time');
        return jsonOut('获取成功',1,$list);
    }

    //创建流程--申请 请假，调休，加班
    public function reqOpt()
    {
        $input_data = $this->request->param();

        $validate = new \app\common\validate\UserReqEvent();
        $model = new \app\common\model\UserReqEvent();

        $input_data['uid'] = $this->user_id;
        $input_data['cid'] = $this->company_id;

        $result = $model->actionAdd($input_data,$validate);
        //主键
        $id = !empty($input_data['id'])?$input_data['id']:($model->getKey()?$model->getKey():0);
        return jsonOut($result['msg'],$result['code'],$id);
    }


    //申请列表
    public function reqList()
    {
        //要查看的用户
        $user_id = $this->request->param('user_id',$this->user_id,'intval');

        $model = new \app\common\model\UserReqEvent();
        $where[] = ['cid','=',$this->company_id];//公司id

        //按类型查看
        $type = $this->request->param('type',0,'intval');
        !empty($type) && $where[] =['type','=',$type];

        //按处理结果查看
        $status = $this->request->param('status',null);
        is_numeric($status) && $where[] =['status','=', $status];

        if(empty($user_id) || $user_id != $this->user_id){  //管理者查看判断

            list($bool) = $this->checkUserAuth($model);
            if(!$bool) {
                return jsonOut('你无权查看列表',0);
            }

            //查看指定用户
            !empty($user_id) && $where[] = ['uid','=',$user_id];

        }else{
            //绑定用户
            $where[] = ['uid','=',$user_id];
        }

        $list = $model
            ->where($where)
            ->order('id','desc')
            ->paginate()
            ->each(function($item,$index){
            $item['type_name'] = $item->type_name;
            $item['status_name'] = $item->status_name;
        })->toArray();

        $need_fields = [
            'total' =>0,'per_page'=>0,'current_page'=>0,'last_page'=>0,
            'data'=>[
                'id'=>0,'type'=>0,'type_name'=>'','content'=>'','start_time'=>'',
                'end_time'=>'','status'=>0,'status_name'=>'','create_time'=>''
            ]
        ];
        $list = filter_data($list,$need_fields);
        return jsonOut('获取成功',1,$list);
    }


    //申请详情
    public function reqDetail()
    {
        $id = $this->request->param('id',0,'intval');
        empty($id) && abort(40001,'参数异常');

        $model = new \app\common\model\UserReqEvent();

        $info = $model->with(['linkFlow'])->where([
            ['id','=',$id],
            ['cid','=',$this->company_id]
        ])->find();
        empty($info) && abort(40001,'数据异常');
        //绑定数据
        $info->append(['type_name','status_name']);

        //获取当前登录者用户信息
        if($this->user_id!=$info['uid']){
            list($bool) = $this->checkUserAuth($model);
            !$bool && abort(40001,'你没有权限查看');
        }
        $need_fields = [
            'id' =>0,'type'=>0,'type_name'=>'','content'=>'','start_time'=>'','end_time'=>'',
            'status'=>'','status_name'=>'','create_time'=>'',
            'link_flow'=>[
                'id'=>0,'content'=>0,'create_time'=>'',
            ]
        ];
        $data = filter_data($info,$need_fields);
        return jsonOut('获取成功',1, $data);
    }

    //申请--编辑详情
    public function reqEditDetail()
    {
        $id = $this->request->param('id',0,'intval');
        empty($id) && abort(40001,'参数异常');

        $model = new \app\common\model\UserReqEvent();
        $model = $model->where(['id'=>$id,'uid'=>$this->user_id])->find();
        empty($model) && abort(40001,'资源获取异常');

        $hidden_fields = ['create_time','update_time','delete_time'];
        $model->hidden($hidden_fields);

        return jsonOut('获取成功',1, $model);
    }

    //获取用户信息
    public function info()
    {
        $user_id = $this->request->param('user_id',$this->user_id,'intval');
        $model = new \app\common\model\Users();
        $model = $model->find($user_id);

        empty($model) && abort(40001,'资源异常');
        $hidden_field = ['delete_time','update_time','password','salt'];
        $model->hidden($hidden_field);

        return jsonOut('获取成功',1, $model);
    }


    //获取出勤汇总--按月获取
    public function workSum()
    {
        $year = $this->request->request('year',0,'intval');
        $time[] = empty($year)?date('Y'):$year;
        $month = $this->request->request('month',0,'intval');
        $time[] = empty($month)?date('m'):$month;
        $time[] = '1';

        $year_month = implode('-',$time);

        $model = new \app\common\model\Users();
        $data = $model->workSignRecord($this->company_id,$year_month,$this->user_id);
        $need_fields = [
            'id'=>0,'name'=>'','header_img'=>'',
            'link_sign_count'=>['sign_times'=>'1|0','late_times'=>'1|0','advance_times'=>'1|0','work_day'=>'1|0'],
            'link_req_event_count'=>['req_times'=>0],
        ];
        $list = filter_data($data,$need_fields);
        return jsonOut('获取成功',1,$list);
    }
}