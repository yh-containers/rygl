<?php
namespace app\common\validate;

use think\Validate;

class Company extends Validate
{
    protected $rule = [
        'name'          => 'require',
        'logo'          => 'require',
        'address'          => 'require',
    ];

    protected $message = [
        'account.require'        => '帐号必须输入',
        'account.logo'        => 'LOGO必须上传',
        'account.address'        => '地址必须舒服',
    ];


    protected $scene = [
        'admin_add' => 'name,logo,address',
    ];
}