<?php
namespace app\api\controller;

class User extends Common
{
    protected $is_need_auth = true;

    protected $ignore_auth = 'register';

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
}