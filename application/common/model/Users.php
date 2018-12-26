<?php
namespace app\common\model;

use think\facade\Env;
use think\model\concern\SoftDelete;
use Overtrue\Pinyin\Pinyin;

class Users extends Base
{
    use SoftDelete;

    const HEADER_PATH = 'uploads/images/user_header/default_';

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
    public function getHeaderImgAttr($value)
    {
        if(empty($value)){
            return $value;
        }
        //判断是那里获取的资源 --api直接加上图片地址
        $module = request()->module();
        if(in_array($module,$this->is_repair_domain)){
            return get_image_location($value,true);
        }

        return $value;
    }

    /*
     * 设置用户头像
     * */
    public function setHeaderImgAttr($value,$data)
    {
        if(empty($data['id'])){//新增
            $value = 'images/header/default_header_'.rand(1,8).'.png';
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

    //设置用户名
    public function setNameAttr($value,$data)
    {
        $allow_fields = $this->checkAllowFields();

        $header_img = isset($this->header_img)?$this->header_img:'';
        if(strpos($header_img,'/default_')){ //表名是系统默认头像
            //更新用户头像
            $mb_len = mb_strlen($value,'utf8');
            if($mb_len<=2){
                $name = $value;
            }else{
                $name = mb_substr($value,-2);
            }
            $root_path = Env::get('root_path');
            $header_img_path = $root_path.'/public/images/header/header_img.png';
            $ttl_path = $root_path.'/public/images/header/DroidSansChinese.ttf';
            if(file_exists($header_img_path) && file_exists($ttl_path)){ //文件存在
                //z字体路径
                $image = \think\Image::open($header_img_path);
                $save_name = self::HEADER_PATH.(!empty($data['id'])?$data['id']:time()).'.png';
                $save_path = $root_path.'/public/'.$save_name;
                // 给原图左上角添加水印并保存
                $image->text($name,$ttl_path,55,'#ffffff',$image::WATER_CENTER)->save($save_path);

                array_push($allow_fields,'header_img'); //添加可写入字段
                $this->data('header_img', $save_name );
            }

        }
        if(!empty($allow_fields)){
            $py = new Pinyin();
            array_push($allow_fields,'py'); //添加可写入字段
            $this->allowField($allow_fields);
            $py = $py->permalink($value,'-',PINYIN_NAME);
            $this->data('py',$py);
        }
        return $value;
    }



    //检测用户各种问题
    public function checkInfo($account,$password)
    {
        $where = [
            ['phone','=',$account]
        ];
        $user_info = $this->where($where)->find();

        if(empty($user_info)){
            abort(40001,'用户名或密码不正确');
        }

        if($user_info['password'] != self::pwdEncrypt($password,$user_info['salt'])) {
            abort(40002,'用户名或密码不正确');
        }

        if($user_info->status==2) {
            abort(40001,'账号已被禁用');
        }

        return $user_info;
    }




    //用户登录
    public function accountLogin($account,$password)
    {

        //验证用户信息
        $user_info = $this->checkInfo($account,$password);

        return $user_info->handleLoginInfo();
    }

    /*
     * 刷新用户登录凭证
     * */
    public function refreshToken($user_id)
    {
        $user_info = $this->find($user_id);

        if(empty($user_info)){
            abort(40001,'用户信息异常');
        }
        //验证用户信息
        $user_info->checkInfo();
        return $user_info->handleLoginInfo();
    }

    //检测用户权限
    public function checkMenuAuth(array $check_menu)
    {
        $menu = $this->getOptMenu();
        $intersect = array_intersect($check_menu, $menu);
        if(empty($intersect)){
            return false;
        }
        return true;
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
     * c_n_auth      审核权限
     *
     * c_m_base      公司基本信息修改
     * c_name        调整公司名
     * c_work_time   调整作息时间
     *
     * auth_name     实名认证
     * */
    public function getOptMenu()
    {
        $menu = [];
        $company_id = $this->getData('cid');
        $user_id = $this->getData('id');
        $is_auth = $this->getData('is_auth');

        //操作栏目
        if($company_id) { //已在公司
            array_push($menu,'c_n_opt');

            //加入公司后可以操作的菜单
            $company_info = model('Company')->find($company_id);
            if($company_info['uid']==$user_id){ //判断是否是创始人
                array_push($menu,'c_c_name','c_name','c_m_base','c_n_auth','c_work_time');
            }


        }else{
            array_push($menu,'c_company','j_company');
        }

        //判断用户是否实名
        $is_auth !=1 && array_push($menu,'auth_name');

        return $menu;
    }


    //处理用户登录凭证
    protected function handleLoginInfo()
    {
        $data = [
            'user_id'   =>  $this->id,      //用户id
            'cid'       =>  $this->cid,     //公司id
            'name'      =>  $this->name,    //用户名
            'opt_menu'  =>  $this->getOptMenu(),             //用户可以操作的栏目
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
        //忽略字段
        if(!empty($data['opt_menu'])) unset($data['opt_menu']);

        //绑定user_agent
        $data['user_agent'] = request()->header('user-agent');
        ksort($data);
        $str = '';
        foreach ($data as $key=>$vo) {
            $str .= $key.'='.$vo.'&';
        }
        $str = substr($str,0,-1);
        unset($data['user_agent']);
        return  sha1($str);
    }

    //关联公司信息
    public function linkCompany()
    {
        return $this->belongsTo('Company','cid');
    }
}