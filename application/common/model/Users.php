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

    //检测用户各种问题
    public function checkInfo()
    {
        if($this->status==2) {
            abort(40001,'账号已被禁用');
        }

    }


    //添加用户
    public function accountLogin($account,$password)
    {
        $where = [
            ['phone','=',$account]
        ];
        $user_info = $this->where($where)->find();

        if(empty($user_info)){
            abort(40001,'用户名或密码不正确');
        }
        //验证用户信息
        $user_info->checkInfo();

        if($user_info['password'] != self::pwdEncrypt($password,$user_info['salt'])) {
            abort(40002,'用户名或密码不正确');
        }
        return $user_info->handleLoginInfo();


    }

    //处理用户登录凭证
    protected function handleLoginInfo()
    {
        $data = [
            'user_id'   =>  $this->id,
            'name'      =>  $this->name,
            'time'      => time()
        ];

        return self::tokenEncrypt($data);
    }

    //用户登录凭证数据加密
    public static function tokenEncrypt($data)
    {
        $sign = self::handleSign($data);
        $json_token = json_encode($data);
        return base64_encode($json_token).'.'.$sign;
    }

    //token验证
    public static function tokenDecrypt($token)
    {
        $arr = explode('.',$token);
        if (count($arr)!=2) {
            return false;
        }

        $base = base64_decode($arr[0]);
        $data = json_decode($base,true);

        if(!is_array($data)){
            return false;
        }
        $check_sign = self::handleSign($data);
        $sign = $arr[1];
        if($check_sign!=$sign) {
            return false;
        }

        return $data;

    }

    //数据签名
    public static function handleSign(array $data)
    {
        //绑定user_agent
        $data['user_agent'] = request()->header('user-agent');
        ksort($data);
        $str = '';
        foreach ($data as $key=>$vo) {
            $str = $key.'='.$vo.'&';
        }
        $str = substr($str,0,-1);

        unset($data['user_agent']);
        return  sha1($str);
    }
}