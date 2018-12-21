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

    /*
     * 特殊操作特殊处理-
     * 此处不包含-用户的基本动作 例：修改用户名、头像、手机号.......
     * 获取用户可以操作的栏目
     * c_company     创建公司
     * j_company     加入公司
     *
     *
     * c_n_opt       加入公司后 常规操作--签到(sign)--请假()--调休..查看公司信息......
     * c_name        调整公司名
     * c_work_time   调整作息时间
     *
     * auth_name     实名认证
     * */
    public function getOptMenu()
    {
        $menu = [];
        //操作栏目
        if($this->cid) { //已在公司
            array_push($menu,'c_n_opt');

            //加入公司后可以操作的菜单
            $company_info = model('Company')->find($this->cid);

            if($company_info['uid']==$this->id){ //判断是否是创始人
                array_push($menu,'c_c_name','c_name','c_work_time');
            }


        }else{
            array_push($menu,'c_company','j_company');
        }

        //判断用户是否实名
        $this->is_auth !=1 && array_push($menu,'auth_name');

        return $menu;
    }


    //处理用户登录凭证
    protected function handleLoginInfo()
    {
        $opt_menu = $this->getOptMenu();
        $opt_menu = json_encode($opt_menu);
        $data = [
            'user_id'   =>  $this->id,      //用户id
            'cid'       =>  $this->cid,     //公司id
            'name'      =>  $this->name,    //用户名
            'opt_menu'  =>  $opt_menu,             //用户可以操作的栏目
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