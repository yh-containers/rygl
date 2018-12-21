<?php
namespace app\common\model;
use think\model\concern\SoftDelete;

class Industry extends Base
{
    use SoftDelete;
    protected $name = 'industry';
}