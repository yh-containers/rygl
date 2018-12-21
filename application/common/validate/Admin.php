<?php
namespace app\common\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'account'       => 'require|min:4|checkUnique',
        'name'          => 'require',
        'password'      => 'min:6',

    ];

    protected $message = [
        'account.require'        => '帐号必须输入',
        'account.gt'             => '帐号长度必须超过 :rule 位',
        'account.unique'         => '帐号已存在',
        'name.require'           => '用户名必须输入',
        'password.require'       => '密码必须输入',
        'password.min'           => '密码长度必须超过 :rule 位',

        'captcha.require'        => '验证码必须输入',
        'captcha.captcha'        => '验证码错误',
    ];


    protected $scene = [
        'admin_add' => 'account,name,password',
    ];

    //登录场景
    public function sceneLogin()
    {
        return $this->only(['account','password','captcha'])
            ->append('password','require')
            ->append('captcha|验证码','require|captcha')
            ->remove('account','checkUnique')
            ;
    }

    //验证帐号唯一
    public function checkUnique($value,$rule,$data=[])
    {
        $model = model('Admin');
        $where = [
            ['account','=',$value],
            ['id','<>',empty($data['id'])?0:$data['id']]
        ];
        $info = $model->where($where)->find();
        return empty($info)?true:'用户名已存在';
    }
}