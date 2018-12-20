<?php
namespace app\common\validate;

use think\Validate;

class SysRole extends Validate
{
    protected $rule = [
        'name'          => 'require',

    ];

    protected $message = [
        'name.require'           => '角色名必须输入',
    ];
}