<?php

namespace app\common\model;



class Sms extends Base
{

    protected $name = 'sms';

    public static $type_all = [
        //[类型名,有效时间( s , 0不限制 ) ]
        ['', 0],
        ['用户注册', 0]
    ];

    //检测验证码使用情况
    public static function checkVerify($type, $phone, $verify)
    {
        //特殊验证码特殊处理
        if($verify=='12345'){
            return [true,''];
        }

        //获取验证码有效期
        $exp_time = !empty(self::$type_all[$type]) ? self::$type_all[$type][1]: 0;

        $model = new self();
        $where = [
            ['type', '=', $type],
            ['phone', '=', $phone],
        ];
        //查询最后一条短信
        $info = $model->where($where)->order('id', 'desc')->find();
        if (empty($info)) {
            return [false, '请输入正确的验证码'];
        } elseif ($info['verify'] != $verify) {
            return [false, '验证码错误'];
        }  elseif ($info['status'] != 1) {
            return [false, '验证码已使用'];
        } elseif ($exp_time && $info['create_time']+$exp_time < time()) {
            return [false, '验证码已过期'];
        } else {
            return [true, ''];
        }

    }

}