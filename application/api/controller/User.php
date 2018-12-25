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
        $bool = $model->allowField($allow_field)->save($input_data,['id'=>$this->user_id]);
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
        return jsonOut($result['msg'],$result['code'],$model->getKey());
    }

}