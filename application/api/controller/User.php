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
        list($bool,$msg,$time) = $model->sign($this->user_id,$this->company_id,$input_data);
        return jsonOut($msg,(int)$bool,$bool?date('Y-m-d H:i:s',$time):'');
    }
}