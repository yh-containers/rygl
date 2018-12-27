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
}