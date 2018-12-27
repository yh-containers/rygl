<?php
namespace app\common\model;

class UserReqEvent extends Base
{
    //用作权限判断名
    const AUTH_FIELD = ['c_n_auth'];

    protected $name='user_req_event';


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

    //获取审核时间
    public function getAuthTimeAttr($value)
    {
        return $value?date('Y-m-d H:i:s',$value):'';
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
        if(!empty($this->auth_uid)){
            return $this->auth_uid;
        }

        return $this->uid;
    }

    /*
     * 获取流程内容
     * */
    protected function getHandleContent($is_update=false)
    {
        $type_name = $this->type_name;
        $status = $this->status;
        $status_name = $this->status_name;
        $content='';


        if($status>1){ //审核状态
            if($status==2) {
                $content = '申请已通过,';
            }elseif($status ==3){
                $content = '申请被拒绝,';
            }
            //补充理由
            if(!empty($this->auth_content)){
                $content .=' 理由:'.$this->auth_content;
            }

        }else{
            if(empty($status)){
                $content = $is_update?'更新申请内容':'已'.$status_name.$type_name.'请耐心等待审核';
            }elseif ($status==1){
                $content = '已'.$status_name.$type_name.'申请';
            }elseif ($status==2){
                $content = '您的'.$type_name.'申请已审核'.$status_name;
            }elseif ($status==3){
                $content = '您的'.$type_name.'申请已被'.$status_name;
            }

        }



        return $content;

    }

    /*
     * 取消审核
     * */
    public function cancelAction($id,$user_id)
    {
        $model = $this->where(['id'=>$id,'uid'=>$user_id])->find();
        empty($model) && abort(40001,'资源异常');
//        !empty($model->status) && abort(40001,'流程未处于审核状态无法操作');
        $model->status=1;
        $model->save();
        return true;
    }

    /*
     * 流程审核
     * */
    public function authAction()
    {
        $input_data = func_get_args();
        empty($input_data) && abort(40001,'参数异常');
        $input_data = $input_data[0];


        $validate = new \app\common\validate\UserReqEvent();
        $validate->scene('auth');
        if(!$validate->check($input_data)) {
            abort(40001,$validate->getError());
        }

        $model = $this->find($input_data['id']);

        empty($model) && abort(40001,'资源异常');
        !empty($model->status) && abort(40001,'记录未处于审核状态,无法进行此操作');
        !empty($input_data['cid']) && $model->cid !=$input_data['cid'] && abort(40001,'操作流程异常');


        $input_data['auth_time'] = time();
        $bool=$model->save($input_data);
        return $bool;
    }


    /*
     * 关联流程
     * */
    public function linkFlow()
    {
        return $this->hasMany('UserReqEventFlow','rid')->order('id','desc');
    }

    /*
     * 关联用户
     * */
    public function linkUserInfo()
    {
        return $this->belongsTo('Users','uid');
    }

    /*
     * 关联审核用户
     * */
    public function linkAuthUserInfo()
    {
        return $this->belongsTo('Users','auth_uid');
    }
    /*
     * 获取申请类型
     * */
    public static function fieldsType()
    {
        $fields_type = [];
        foreach (self::$fields_type as $key=>$vo) {
            if($vo){
                $fields_type[]=[
                    'type' => $vo,
                    'value' => $key
                ];
            }
        }
        return $fields_type;
    }

}