<?php
namespace app\api\controller;

use think\Db;

class Company extends Common
{
    protected $is_need_auth=true;
    //获取公司作息时间
    public function info()
    {
        $fields = $this->request->param('field','*');

        $visible = ['id','uid','name','logo','area_id','address','industry_id',
            'description','website','contact','phone','coordinate','sign_mac','work_time','create_time'];
        if(strpos($fields,',')){
            $fields = explode(',',$fields);
            $fields = array_values(array_intersect($visible,$fields));
        }

        $model = new \app\common\model\Company();
        $info = $model->field($fields)->visible($visible)->find($this->company_id);

        return jsonOut('获取成功',1,$info);
    }

    //修改公司信息
    public function modInfo()
    {
        $input_data = $this->request->param();
        $allowField = ['name','logo','area_id','address','industry_id',
            'description','website','contact','phone','coordinate','sign_mac','work_time','update_time'];


        $model = new \app\common\model\Company();
        list($bool) = $this->checkUserAuth($model,['c_m_base']);
        !$bool && abort(40001,'你无权操作');

        $bool = $model->allowField($allowField)->save($input_data,['id'=>$this->company_id]);

        return jsonOut($bool?'操作成功':'操作失败',(int)$bool);
    }

    //获取公司申请类型
    public function reqType()
    {
        return  jsonOut('获取成功',1,\app\common\model\UserReqEvent::fieldsType());
    }

    //获取公司员工
    public function users()
    {
        $model = new \app\common\model\Users();
        $where['cid'] = $this->company_id;
        $data= $model->where($where)->select()->each(function($item,$index)use(&$data){
            $item['py_prefix'] = empty($item['py'])?'#':strtoupper($item['py'][0]);
        });
        $need_fields = [
            'id'=>0,'name'=>'','phone'=>'','header_img'=>'','status'=>'','create_time'=>'','sex'=>'0','is_auth'=>0
        ];
        $list = filter_data($data,$need_fields,2);
        return jsonOut('获取成功',1,$list);
    }

    //获取出勤汇总--按月获取
    public function workSum()
    {
        $year = $this->request->request('year',0,'intval');
        $time[] = empty($year)?date('Y'):$year;
        $month = $this->request->request('month',0,'intval');
        $time[] = empty($month)?date('m'):$month;
        $time[] = '1';

        $year_month = implode('-',$time);

        $model = new \app\common\model\Users();
        $data = $model->workSignRecord($this->company_id,$year_month);
        $need_fields = [
            'id'=>0,'name'=>'','header_img'=>'',
            'link_sign_count'=>['sign_times'=>'>0','late_times'=>'>0','advance_times'=>'>0','work_day'=>'>0'],
            'link_req_event_count'=>['req_times'=>'>0'],
        ];
        $list = filter_data($data,$need_fields,2);
        return jsonOut('获取成功',1,$list);
    }


    //工作日志-列表
    public function reportList(){

        $report_date = $this->request->request('report_date',Date('Y-m-d',time()));

        $start_time = strtotime($report_date);

        $end_time = strtotime($report_date . ' 23:59:59');

        $model = new \app\common\model\WorkReport();

        $list = $model
            ->whereBetweenTime('work_report.create_time',$start_time,$end_time)
            ->field(['type','content','create_time'])
            ->withJoin(['linkUserInfo'=>['name']],'LEFT')
            ->paginate()->each(function($item,$index){
                $item['name'] = $item['link_user_info']['name'];
                unset($item['link_user_info']);
            });

        return jsonOut('获取成功',1,$list);
	}

    //审核动作
    public function reqAuth()
    {
        $input_data = $this->request->param();
        $model = new \app\common\model\UserReqEvent();
        $input_data['auth_uid'] = $this->user_id;
        $input_data['cid'] = $this->company_id;
        $bool = $model->authAction($input_data);
        return jsonOut($bool?'操作成功':'操作失败',(int)$bool);

    }
}

