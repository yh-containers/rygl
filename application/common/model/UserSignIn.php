<?php
namespace app\common\model;

class UserSignIn extends Base
{
    protected $name = 'user_sign_in';
    //今日时间(Y-m-d)
    public static $current_day;
    //当前时间戳
    public static $current_time;

    public static function init()
    {
        //获取当前日期
        self::$current_day = date('Y-m-d');
        self::$current_time = time();
    }

    /*
     * 用户签到
     * */
    public function sign($user_id,$cid,$input_data)
    {
        empty($cid) && abort(40001,'加入公司后才能打卡');
        empty($input_data['mac']) && abort(40001,'参数异常');
        $mac = $input_data['mac'];

        $user_info = $this->where('uid',$user_id)->order('id','desc')->find();

        if(empty($user_info)) {
            $bool = $this->createSign($user_id,$cid,$mac);

        }else{
            $s_day = date('Y-m-d',$user_info['s_time']);
            if(self::$current_day == $s_day){
                //已打过卡
                $times = $user_info['times']+1;
                $bool = $this->createSign($user_id,$cid,$mac,$times);
            }else{
                //今天未打卡
                $bool = $this->createSign($user_id,$cid,$mac);
            }
        }

        return [$bool,$bool?'打卡成功':'打卡异常',self::$current_time];
    }

    /*
     * 打卡记录
     * @param $user_id int 用户id
     * @param $company_id int 公司id
     * @param $mix_time string  2018-12-24|24/按天  2018-12/按月
     * */
    public function records($user_id,$company_id,$mix_time)
    {
        $count_times=substr_count($mix_time,'-');
        if($count_times==1){ //按月

        }else{//按天

        }



//        $this->
    }


    /*
     * 创建打卡
     * @param $user_id int 用户id
     * @param $cid int 公司id
     * @param $mac 打卡条件
     * @param $times 打卡次数
     * */
    protected function createSign($user_id,$cid,$mac='',$times=1)
    {
        //可直接打卡
        $data = [
            'cid'=>$cid,
            'uid'=>$user_id,
            'mac'=>$mac,
            'times'=>$times,
            's_time'=>self::$current_time,
        ];
        return $this->save($data);
    }
}