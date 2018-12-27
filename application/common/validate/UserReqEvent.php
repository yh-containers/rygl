<?php
namespace app\common\validate;

use think\Validate;

class UserReqEvent extends Validate
{
    protected $rule = [
        'type'             => 'require|gt:0',
        'content'          => 'require|min:10',
        'start_time'       => 'require|date',
        'end_time'         => 'require|date|gt:start_time',
    ];

    protected $message = [
        'type.require'           => '申请类型异常',
        'type.gt'                => '申请类型异常',
        'content.require'        => '内容必须输入',
        'content.min'            => '内容长度限制:必须超过 :rule 位',
        'start_time.require'     => '开始时间必须设置',
        'start_time.date'        => '开始时间格式异常',
        'end_time.require'       => '结束时间必须设置',
        'end_time.date'          => '结束时间格式异常',
        'end_time.gt'            => '结束时间必须大于开始时间',

        //审核动作
        'id.require'            =>  '参数异常:attribute',
        'id.gt'                 =>  '参数异常:attribute',
        'auth_uid.require'      =>  '参数异常:attribute',
        'auth_uid.gt'           =>  '参数异常:attribute',
        'status.require'        =>  '参数异常:attribute',
        'status.in'             =>  '参数:attribute范围异常',
    ];


    protected $scene = [
        'admin_add' => 'name,logo,address',
    ];

    //api注册
    public function sceneAuth()
    {
        return $this->only(['id','auth_uid','status'])
            ->append('id','require|gt:0')
            ->append('auth_uid','require|gt:0')
            ->append('status','require|in:2,3')
            ;
    }

}