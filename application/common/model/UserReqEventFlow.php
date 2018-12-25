<?php
namespace app\common\model;

use think\Model;

class UserReqEventFlow extends Base
{
    protected $name='user_req_event_flow';
    /*
     * 添加处理流程
     * */
    public function handleFlow(Model $model,$uid,$content)
    {
        $data = [
            'uid' => $uid,
            'rid' => $model->getKey(),
            'content' => $content,
        ];
        $this->save($data);
    }
}