<?php
namespace app\common\validate;

use think\Validate;
use think\model\concern\SoftDelete;

class Department extends Validate
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';

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