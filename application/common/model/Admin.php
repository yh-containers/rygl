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

        return self::pwdEncrypt($value,$salt);//md5($value.md5($value.$salt).$salt);
    }

    /*
     * 管理员密码加密
     * */
    public static function pwdEncrypt($password,$salt)
    {
        return md5($password.md5($password.$salt).$salt);
    }


    //处理登录--管理员登录
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

        //登录成功--生成登录凭证
        $this->_generateLoginInfo($user_info['id'],$user_info['name'],$role_info['name'],1);
        return [true,'登录成功'];
    }

    //公司登录
    public function handleCompanyLogin($input_data)
    {
        $validate = new \app\common\validate\Users();
        $validate->scene('admin_login');//用户登录后台场景

        if(!$validate->check($input_data)){
            return [false,$validate->getError()];
        }

        $account = $input_data['account'];
        $password = $input_data['password'];
        $user_model = new \app\common\model\Users();
        //验证用户信息
        try{
            $user_info = $user_model->checkInfo($account,$password);
            //获取公司信息
            $company_info = $user_info->linkCompany;
            if(empty($company_info)){
                return [false,'请先加入公司后登录'];
            }
            //登录成功--生成登录凭证
            $this->_generateLoginInfo($user_info['id'],$user_info['name'],$company_info['name'],0,$company_info['id']);
            return [true,'登录成功'];
        }catch (\Exception $e) {
            return [false,$e->getMessage()];
        }
    }

    /*
     * 生成登录信息
     * @param $id int 当前登录者id
     * @param $name string 登录者名称
     * @param $ind_name 登录者身份
     * @param $is_admin 是否是管理员登录
     * @param $com_id int 公司id
     * */
    private function _generateLoginInfo($id,$name,$ind_name,$is_admin=0,$com_id=0)
    {
        session('admin_info',[
            'admin_id' => $id,
            'admin_name' => $name,
            'admin_role_name' => $ind_name,
            'is_admin' => $is_admin,
            'com_id' => $com_id,
        ]);
    }


    //关联角色
    public function linkRole()
    {
        return $this->belongsTo('SysRole','rid');
    }
}