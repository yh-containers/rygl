<?php
namespace app\common\model;

class Node extends Base
{
    protected $name = 'sys_node';

    /*
     * 获取菜单树--只有两级
     * */
    public function tree($is_admin=false)
    {
        $where = [
            ['status','=',1],
        ];
        if($is_admin) { //管理员界面
            $where[] =['is_admin','=',0];
            $link = 'linkAdminNode';
            $key = 'link_admin_node';

        }else{      //公司界面
            $where[] =['is_company','=',0];
            $link = 'linkCompanyNode';
            $key = 'link_company_node';
        }

        $data = $this->with([$link=>function($query){
            return $query->where('status',1);
        }])->where($where)->order('sort','asc')->select();
        return [$data,$key];
    }

    /*
     * 一对多关联--管理员
     * */
    public function linkAdminNode()
    {
        return $this->hasMany('Node','is_admin')->order('sort','asc');
    }


    /*
     * 一对多关联--公司
     * */
    public function linkCompanyNode()
    {
        return $this->hasMany('Node','is_company')->order('sort','asc');
    }

}