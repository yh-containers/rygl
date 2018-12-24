<?php
namespace app\admin\controller;

class CompanySys extends Common
{
    //签到设置
    public function setting()
    {
        $model = new \app\common\model\Company();
        $model = $model->find($this->com_id);
        $work_time = $model['work_time'];
        //上班时间规则
        $set_work_time = \app\common\model\Company::getWorkTime();

        return view('setting',[
            'model'=>$model,
            'set_work_time'=>$set_work_time,
            'work_time' => $work_time
        ]);
    }


    //保存签到信息
    public function signAction()
    {
        $sign_mac = $this->request->param('sign_mac');
        $model = new \app\common\model\Company();
        list($state,$msg) = $model->setMacSign($this->com_id,$sign_mac);

        return ['code'=>(int)$state,'msg'=>$msg];
    }

    //设置工作时间
    public function workTimeAction()
    {
        $am = $this->request->param('am','0','intval');
        $am_str = implode(':',$am);
        $pm = $this->request->param('pm','0','intval');
        $pm_str = implode(':',$pm);

        $model = new \app\common\model\Company();
        list($state,$msg) =  $model->setWorkTime($this->com_id,$am_str,$pm_str);
        return ['code'=>(int)$state,'msg'=>$msg];
    }
}