<?php
namespace app\common\model;

class UserSignIn extends Base
{
    protected $name = 'user_sign_in';
    public static $fields_status = ['','上班','下班'];
    public static $fields_nss = ['正常','迟到','早退'];
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

        //获取公司打卡时间规则
        $company_model = model('Company')->where('id',$cid)->find();
        $work_time = $company_model['work_time'];
        $sign_mac = $company_model['sign_mac'];
        if(!empty($sign_mac) && $sign_mac!=$mac) {
            abort(40001,'请链接公司wifi进行打卡');
        }

        $user_info = $this->where('uid',$user_id)->order('id','desc')->find();


        $s_day = date('Y-m-d',$user_info['s_time']);
        if(empty($user_info) || self::$current_day != $s_day){
            //今天未打卡
            list($bool,$status,$nsm,$nss) = $this->createSign($user_id,$cid,$mac,$work_time);
        }else{
            //已打过卡
            $times = $user_info['times']+1;
            list($bool,$status,$nsm,$nss) = $this->createSign($user_id,$cid,$mac,$work_time,$times);
        }


        return [$bool,$bool?'打卡成功':'打卡异常',self::$current_time,$status,$nsm,$nss];
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
    protected function createSign($user_id,$cid,$mac='',$work_time,$times=1)
    {
        $status = 1; //上班卡
        $nsm = 0; //非正常打卡时间范围
        $nss = 0; //非正常卡 0 正常 1迟到 2早退
        if($times>1) { //下班卡
            $status = 2;
        }

        if(!empty($work_time)) {
            if($status==1) { //上班
                $am_time = implode(':',$work_time[0]);
                $am_time = strtotime($am_time);
                $nsm_time = self::$current_time - $am_time ;//误差时间
                if($nsm_time > 60){
                    $nsm = intval($nsm_time/60);
                    $nss = 1;
                }
            }else{//下班
                $pm_time = implode(':',$work_time[1]);
                $pm_time = strtotime($pm_time);
                $nsm_time = $pm_time - self::$current_time    ;//误差时间
                if($nsm_time > 60){
                    $nsm = intval($nsm_time/60);
                    $nss = 2;
                }
            }
        }




        //可直接打卡
        $data = [
            'cid'=>$cid,
            'uid'=>$user_id,
            'mac'=>$mac,
            'times'=>$times,
            'status'=>$status,
            'nss'=>$nss,
            'nsm'=>$nsm,
            's_time'=>self::$current_time,
        ];

        $bool = $this->save($data);
        return [$bool, $status,$nsm, $nss];
    }
}