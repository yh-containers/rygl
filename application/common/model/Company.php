<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class Company extends Base
{
    use SoftDelete;

    protected $name = 'company';
}