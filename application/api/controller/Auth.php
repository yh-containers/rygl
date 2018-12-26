<?php
namespace app\api\controller;

class Auth extends Common
{
    //申请审核动作
    public function reqAction()
    {
        $id = $this->request->param('id',0,'intval');
        $status = $this->request->param('status',0,'intval');
        empty($id) && abort(40001,'参数异常:id');
        empty($status) && abort(40001,'参数异常:status');
        $model = new \app\common\model\UserReqEvent();

        list($bool) = $this->checkUserAuth($model);
        !$bool && abort(40001,'无权进行此操作');

        $bool = $model->authAction($id,$status);

        return jsonOut($bool?'操作成功':'操作异常',(int)$bool);
    }
}

