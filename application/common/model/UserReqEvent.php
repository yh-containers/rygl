<?php
namespace app\common\model;

class UserReqEvent extends Base
{
    //用作权限判断名
    const AUTH_FIELD = ['c_n_auth'];

    protected $name='user_req_event';

    protected $handle_content;
    protected $handle_uid;

    public static $fields_type = ['','事假','调休','加班'];
    public static $fields_status = ['创建','取消','通过','拒绝'];

    protected $insert = ['status'=>0];

    public function getStartTimeAttr($value)
    {
        return $value?date('Y-m-d H:i:s',$value):'';
    }

    public function getEndTimeAttr($value)
    {
        return $value?date('Y-m-d H:i:s',$value):'';
    }


    public function setStartTimeAttr($value)
    {
        return $value?strtotime($value):0;
    }

    public function setEndTimeAttr($value)
    {
        return $value?strtotime($value):0;
    }

    //获取类型名
    public function getTypeNameAttr($value,$data)
    {
        $type = !empty($data['type'])?$data['type']:0;

        $type_name = !empty(self::$fields_type[$type])?self::$fields_type[$type]:'';
        return $type_name;

    }
    //获取状态名
    public function getStatusNameAttr($value,$data)
    {
        $status = !empty($data['status'])?$data['status']:0;
        $status_name = !empty(self::$fields_status[$status])?self::$fields_status[$status]:'';
        return $status_name;
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

        $type_name = $this->type_name;
        $status_name = $this->status_name;

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
        return $this->hasMany('UserReqEventFlow','rid')->order('id','desc');
    }
}