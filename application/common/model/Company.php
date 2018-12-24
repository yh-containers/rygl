<?php
namespace app\common\model;

use think\model\concern\SoftDelete;

class Company extends Base
{
    use SoftDelete;

    protected $name = 'company';


    //获取工作时间--获取器
    public function getWorkTimeAttr($value)
    {
        if(empty($value)){
            return [];
        }
        $work_time = '[["'.str_replace(['-',':'],['"],["','","'],$value).'"]]';
        $work_time = json_decode($work_time,true);
        return $work_time;
    }


    //设置--wifi 签到信息
    public function setMacSign($cid,$value)
    {
        if(empty($value)){
            return [false,'mac地址不能为空'];
        }
        $state = $this->save(['sign_mac'=>$value],['id'=>$cid]);
        return [(bool)$state,$state?'设置成功':'设置失败'];
    }

    /*
     * 设置工作时间
     * @param $cid int 公司id
     * @param $am string 开始时间 格式 00:00
     * @param $pm string 结束时间 格式 00:00
     * */
    public function setWorkTime($cid,$am,$pm)
    {
        $am_arr = explode(':',$am);
        $am_h = sprintf('%02d',$am_arr[0]);
        $am_m = empty($am_arr[1])?'00':sprintf('%02d',$am_arr[1]);
        $am_block = $am_h.':'.$am_m;

        $pm_arr = explode(':',$pm);
        $pm_h = sprintf('%02d',$pm_arr[0]);
        $pm_m = empty($pm_arr[1])?'00':sprintf('%02d',$pm_arr[1]);
        $pm_block = $pm_h.':'.$pm_m;

        $work_time = $am_block.'-'.$pm_block;
        $state = $this->save(['work_time'=>$work_time],['id'=>$cid]);
        return [(bool)$state,$state?'设置成功':'设置失败'];

    }


    //获取上班时间规则
    public static function getWorkTime()
    {
        $time = [];
        //上班时间 am 早上  pm 下午
        for($i=0;$i<24;$i++){
            $time['am'][0][] = sprintf('%02d',$i);
            $time['pm'][0][] = sprintf('%02d',$i);
        }

        for($i=0;$i<60;$i=$i+5){
            $time['am'][1][] = sprintf('%02d',$i);
            $time['pm'][1][] = sprintf('%02d',$i);
        }

        return $time;


    }
}