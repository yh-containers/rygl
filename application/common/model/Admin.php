<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class Admin extends Base
{
    use SoftDelete;

    protected $name = 'sys_admin';

    protected $insert = [];

    public function setPasswordAttr($value)
    {
        if(!$value){
            return;
        }

        $salt = rand(1000,9999);
        $this->setAttr('salt',$salt);

        return md5($value.md5($value.$salt).$salt);
    }
}