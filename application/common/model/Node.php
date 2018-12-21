<?php
namespace app\common\model;

class Node extends Base
{
    protected $name = 'sys_node';

    /*
     * 获取菜单树--只有两级
     * */
    public function tree()
    {
        $where = [
            ['status','=',1],
            ['pid','=',0],
            ['level','=',1],
        ];
        $data = $this->with(['linkNode'=>function($query){
            return $query->where('status',1);
        }])->where($where)->order('sort','asc')->select();
        return $data;
    }

    /*
     * 一对多关联
     * */
    public function linkNode()
    {
        return $this->hasMany('Node','pid')->order('sort','asc');
    }

}