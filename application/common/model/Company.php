<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class Company extends Base
{
    use SoftDelete;

    protected $name = 'company';


    //设置--wifi 签到信息
    public function setMacSign($cid,$value)
    {
        if(empty($value)){
            return [false,'mac地址不能为空'];
        }
        $state = $this->where('id',$cid)->setField('sign_mac',$value);
        return [(bool)$state,$state?'设置成功':'设置失败'];
    }
}