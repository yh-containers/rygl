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

        return pwdEncrypt($value,$salt);//md5($value.md5($value.$salt).$salt);
    }

    /*
     * 管理员密码加密
     * */
    public static function pwdEncrypt($password,$salt)
    {
        return md5($password.md5($password.$salt).$salt);
    }


    //处理登录
    public function handleLogin($input_data)
    {
        $validate = new \app\common\validate\Admin();
        $validate->scene('login');
        if(!$validate->check($input_data)){
            return [false,$validate->getError()];
        }

        $account = $input_data['account'];
        $password = $input_data['password'];
        $user_info = $this->where('account',$account)->find();
        if(empty($user_info)) {
            return [false,'用户名或密码错误'];
        }

        $encrypt = self::pwdEncrypt($password,$user_info['salt']);
        if($encrypt!=$user_info['password']){
            return [false,'用户名或密码错误'];
        }

        //更新登录信息
        $role_info = $user_info->linkRole;

        $user_info->last_time = time();
        $user_info->login_ip = request()->ip();
        $user_info->save();

        //登录成功
        session('admin_info',[
            'admin_id' => $user_info['id'],
            'admin_name' => $user_info['name'],
            'admin_role_name' => $role_info['name'],
        ]);

        return [true,'登录成功'];
    }





    //关联角色
    public function linkRole()
    {
        return $this->belongsTo('SysRole','rid');
    }
}