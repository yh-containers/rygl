<?php
namespace app\common\model;
use think\model\concern\SoftDelete;

class Department extends Base
{
    use SoftDelete;
    protected $name = 'department';
}