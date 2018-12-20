<?php
namespace app\common\validate;

use think\Validate;

class Users extends Validate
{
    protected $rule = [
        'phone'       => 'require|mobile|checkUnique',
        'password'    => 'require|min:6',

    ];

    protected $message = [
        'phone.require'        => '帐号必须输入',
        'phone.mobile'         => '请输入正确的手机号',
        'password.require'     => '密码必须输入',
        'password.min'         => '密码字符长度不得低于 :rule 位',

        'verify.require'       => '验证码必须输入',
    ];

    //api注册
    public function sceneApi_opt()
    {
        return $this->only(['phone','password','verify'])
            ->append('verify','require|checkSms:1')
            ;
    }

    //验证帐号唯一
    public function checkUnique($value,$rule,$data=[])
    {
        $model = model('Users');
        $where = [
            ['phone','=',$value],
            ['id','<>',empty($data['id'])?0:$data['id']]
        ];
        $info = $model->where($where)->find();
        return empty($info)?true:'手机号已被注册';
    }

    //验证短信验证码
    public function checkSms($value,$rule,$data=[])
    {
        list($state,$msg) = \app\common\model\Sms::checkVerify($rule,$data['phone'],$value);
        return $state ? true : $msg;
    }
}