<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class Users extends Base
{
    use SoftDelete;

    protected $name = 'users';

    protected $insert = ['header_img'];

    /*
     * 密码加密
     * */
    public static function pwdEncrypt($password,$salt)
    {
        return md5($password.md5($password.$salt).$salt);
    }

    /*
     * 设置用户头像
     * */
    public function setHeaderImgAttr($value,$data)
    {
        if(empty($data['id'])){//新增
            $value = '/images/header/default_header_'.rand(1,8).'.png';
        }

        return $value;

    }

    public function setPasswordAttr($value)
    {
        if(empty($value)){
            return;
        }
        $salt = rand(10000,99999);
        $this->data('salt',$salt);
        return self::pwdEncrypt($value,$salt);
    }



    //添加用户
//    public function
}