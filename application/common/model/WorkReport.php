<?php
namespace app\common\model;
use think\model\concern\SoftDelete;

class WorkReport extends Base
{
    use SoftDelete;
    protected $name = 'work_report';

    public function getDeadlineAttr($value)
    {
        return $value?date('Y-m-d H:i:s',$value):'';
    }

    public function setDeadlineAttr($value)
    {
        return $value?strtotime($value):0;
    }


    public function linkUserInfo()
    {
        return $this->belongsTo('Users','uid');
    }
}