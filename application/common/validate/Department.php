<?php
namespace app\common\validate;

use think\Validate;

class Department extends Validate
{

    protected $rule = [
        'name'          => 'require',
    ];

    protected $message = [
        'account.require'        => '帐号必须输入',
    ];

    protected $scene = [
        'admin_add' => 'name',
    ];
}