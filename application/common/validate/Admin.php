<?php
namespace app\common\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'account'       => 'require|min:6|unique:sys_admin',
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
    ];
}