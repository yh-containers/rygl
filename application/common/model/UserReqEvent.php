<?php
namespace app\common\model;

class UserReqEvent extends Base
{
    protected $name='user_req_event';

    protected $handle_content;
    protected $handle_uid;

    public static $fields_type = ['','事假','调休','加班'];
    public static $fields_status = ['创建','取消','通过','拒绝'];

    protected $insert = ['status'=>0];

    public function setStartTimeAttr($value)
    {
        return $value?strtotime($value):0;
    }

    public function setEndTimeAttr($value)
    {
        return $value?strtotime($value):0;
    }


    public static function init()
    {
        //注册事件
        //创建后
        self::event('after_insert', function ($model) {
            //添加流程
            $model_flow = new UserReqEventFlow();
            $model_flow->handleFlow($model,$model->getHandleUid(),$model->getHandleContent());

        });
        //更新动作
        self::event('after_update', function ($model) {
            //添加流程
            $model_flow = new UserReqEventFlow();
            $model_flow->handleFlow($model,$model->getHandleUid(),$model->getHandleContent(true));

        });
    }

    /*
     * 获取操作用户
     * */
    protected function getHandleUid()
    {
        if($this->handle_uid){
            return $this->handle_uid;
        }

        return $this->uid;
    }


    /*
     * 获取流程内容
     * */
    protected function getHandleContent($is_update=false)
    {
        if($this->handle_content){
            return $this->handle_content;
        }


        $type = $this->getData('type');
        $status = $this->getData('status');
        $type_name = !empty(self::$fields_type[$type])?self::$fields_type[$type]:'';
        $status_name = !empty(self::$fields_status[$status])?self::$fields_status[$status]:'';

        $content='';

        if(empty($status)){
            $content = $is_update?'更新申请内容':'已'.$status_name.$type_name.'请耐心等待审核';
        }elseif ($status==1){
            $content = '已'.$status_name.$type_name.'申请';
        }elseif ($status==2){
            $content = '您的'.$type_name.'申请已审核'.$status_name;
        }elseif ($status==3){
            $content = '您的'.$type_name.'申请已被'.$status_name;
        }
        return $content;

    }



    /*
     * 关联流程
     * */
    public function linkFlow()
    {
        return $this->hasMany('UserReqEventFlow','rid');
    }
}