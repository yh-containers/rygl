<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class SysRole extends Base
{
    use SoftDelete;

    protected $name = 'sys_role';

}