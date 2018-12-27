<?php
namespace app\admin\controller;

class Company extends Common
{
    //公司列表
    public function index()
    {

        $keyword = request()->get('keyword');
        $model = new \app\common\model\Company();
        $list = $model->wherelike('name',"%".$keyword."%")->paginate();
        return view('index',[
            'list' => $list,
            'page' => $list->render(),
            'count'=> count($list),
        ]);
    }

    //公司操作-新增、编辑
    public function companyAdd()
    {
        $id = $this->request->param('id',$this->com_id,'intval');
        $model = new \app\common\model\Company();
        if($this->request->isAjax()) {
            $validate = new \app\common\validate\Company();
            $validate->scene(self::VALIDATE_SCENE);

            $input_data = $this->request->param();
            return $model->actionAdd($input_data,$validate);
        }
        $model = $model->find($id);
        return view('companyAdd',[
            'model' => $model
        ]);
    }

    //删除公司
    public function companyDel()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Company();
        return $model->actionDel($id);
    }

    //部门列表
    public function department()
    {

        $keyword = request()->get('keyword');
        $cid = $this->request->param('cid',0,'intval');
        $model = new \app\common\model\Department();
        $list = $model->wherelike('name',"%".$keyword."%")->paginate();
        return view('department',[
            'list' => $list,
            'page' => $list->render(),
            'count'=> count($list),
            'cid'=>$cid
        ]);
    }

    /*
     * 部门操作-新增、编辑
     * */
    public function departmentAdd()
    {
        $id = $this->request->param('id',0,'intval');
        $cid = $this->request->param('cid',0,'intval');
        $model = new \app\common\model\Department();
        if($this->request->isAjax()) {
            $validate = new \app\common\validate\Department();
            $validate->scene(self::VALIDATE_SCENE);

            $input_data = $this->request->param();
            return $model->actionAdd($input_data,$validate);
        }
        $model = $model->find($id);
        return view('departmentAdd',[
            'model' => $model,
            'cid'=>$cid
        ]);
    }

    //删除部门
    public function departmentDel()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Department();
        return $model->actionDel($id);
    }

    //公司员工列表
    public function users()
    {
        $model = new \app\common\model\Users();
        $list = $model->where('cid','=',session('admin_info.com_id'))->paginate();
        return view('users',[
            'list' => $list,
            'page' => $list->render(),
            'count'=> count($list),
        ]);
    }

    /*
     * 公司员工操作-新增&编辑
     * */
    public function userAdd()
    {
        $id = $this->request->param('id',0,'intval');
        $cid = session('admin_info.com_id');
        $model = new \app\common\model\Users();
        $department_model = new \app\common\model\Department();
        if($this->request->isAjax()) {
            $validate = new \app\common\validate\Users();
            $validate->scene('company_opt');//公司员工管理
            //$validate->scene(self::VALIDATE_SCENE);

            $input_data = $this->request->param();
            return $model->actionAdd($input_data,$validate);
        }

        $model = $model->find($id);
        $department = $department_model->where('cid',$cid)->select();

        return view('userAdd',[
            'model' => $model,
            'department' => $department,
            'cid'=>$cid
        ]);
    }

    //删除员工
    public function userDel()
    {
        $id = $this->request->param('id',0,'intval');
        $model = new \app\common\model\Users();
        return $model->actionDel($id);
    }

    //员工工作汇报
    public function workReports()
    {
        $uid = $this->request->param('uid',0,'intval');
        $model = new \app\common\model\WorkReport();
        $list = $model->where('uid','=',$uid)->paginate();
        return view('workReports',[
            'list' => $list,
            'page' => $list->render(),
            'count'=> count($list),
        ]);
    }

    //流程审批
    public function authFlow()
    {
        $type = $this->request->param('type',0,'intval');
        $model = new \app\common\model\UserReqEvent();
        $where[] = ['cid','=',$this->com_id];
        $type && $where[] = ['type','=',$type];

        $list = $model->with(['linkUserInfo'])->where($where)->paginate();
        return view('authFlow',[
            'type' => $type,
            'list' => $list,
            'page' => $list->render(),
            'type_all' => \app\common\model\UserReqEvent::fieldsType(),
        ]);
    }

    //申请详情
    public function authFlowDetail()
    {
        $id = $this->request->param('id',0,'intval');
        $cid = $this->request->param('cid',null,'intval');  //公司id
        is_null($cid) && $this->com_id && $cid = $this->com_id; //公司id
        $where[] = ['id','=',$id];
        //按公司查询
        !empty($cid) &&   $where[] =['cid','=',$cid];
        $model = new \app\common\model\UserReqEvent();
        $model = $model->with(['linkUserInfo','linkFlow','linkAuthUserInfo'])->where($where)->find();
        return view('authFlowDetail',[
            'model' => $model
        ]);
    }

    //流程审核动作
    public function authFlowAction()
    {
        $input_data = $this->request->param();
        $input_data['auth_uid'] = $this->admin_id;
        $model = new \app\common\model\UserReqEvent();
        $bool = $model->authAction($input_data);
        return ['code'=>(int)$bool,'msg'=>$bool?'操作成功':'操作失败'];
    }



    //打卡记录
    public function signLogs()
    {
        $year = $this->request->request('year',0,'intval');
        $time[] = empty($year)?date('Y'):$year;
        $month = $this->request->request('month',0,'intval');
        $time[] = empty($month)?date('m'):$month;
        $time[] = '1';

        $year_month = implode('-',$time);

        $model = new \app\common\model\Users();
        $list = $model->workSignRecord($this->com_id,$year_month);



        return view('signLogs',[
            'list'=>$list,
        ]);
    }
}