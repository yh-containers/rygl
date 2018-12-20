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

}