<?php
namespace app\api\controller;

class Company extends Common
{
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
}

