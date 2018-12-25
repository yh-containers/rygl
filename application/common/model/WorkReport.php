<?php
namespace app\common\model;
use think\model\concern\SoftDelete;

class WorkReport extends Base
{
    use SoftDelete;
    protected $name = 'work_report';
}