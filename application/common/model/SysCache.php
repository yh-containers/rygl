<?php
namespace app\common\model;

class SysCache extends Base
{
    protected $name = 'sys_cache';

    public static $type_info = [
        'reg_protocol'      => '注册协议',
        'privacy_policy'    => '隐私政策',
    ];


    /*
     * 获取内容
     * */
    public function getContent($type)
    {
        return $this->cache($this->name.$type)->getFieldBytype($type,'content');
    }

    /*
     * 获取内容
     * */
    public function setContent($type,$content)
    {
        //清空数据
        cache($this->name.$type,null);

        return $this->save(['content'=>$content],['type'=>$type]);
    }
}