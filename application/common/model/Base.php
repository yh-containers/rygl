<?php
namespace app\common\model;

use think\Model;
use think\Validate;

class Base extends Model
{
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    public static $fields_status = ['','正常','关闭'];
    protected $autoWriteTimestamp = true;


    //添加动作
    public function actionAdd($input_data,Validate $validate=null)
    {
        if($validate && !$validate->check($input_data)){
            return ['code'=>0,'msg'=>$validate->getError()];
        }
        //
        $model = &$this;

        $where = [];
        $pk = $model->getPk();

        if(!empty($input_data[$pk])){
            $where[$pk] = $input_data[$pk];
            //更新数据保留数据原始数据
            $model = $model->where($where)->find();
            if(empty($model))  return ['code'=>0,'msg'=>'未修改字段信息'];
        }
        // 过滤post数组中的非数据表字段数据
        try{
            $bool = $model->allowField(true)->save($input_data);
            return ['code'=>(int)$bool, 'msg'=>$bool ? '保存成功' : '保存失败'];
        } catch (\Exception $e) {
            return ['code'=>0,'msg'=>'保存异常:'.$e->getMessage()];
        }
    }

    //删除动作
    public function actionDel($where,$is_soft_delete=false)
    {
        $bool = self::destroy($where,$is_soft_delete);
        return ['code'=>(int)$bool, 'msg'=>$bool ? '删除成功' : '删除失败'];
    }
}