<?php
namespace app\admin\controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\App;
use think\facade\Env;

class HandleExcel extends Common
{
    //文件保存有效时间
    const expire_second = 1;

    protected static $file_path = '/temp_file/excel/';
    protected static $spreadsheet;
    protected static $sheet;
    protected static $save_path;
    //当前请求时间
    public static $current_time=0;

    //忽略的文件 前缀
    public static $ignore_file = ['.'];


    public function __construct(App $app = null)
    {
        parent::__construct($app);

        self::$current_time = time();
        self::$file_path = self::$file_path.self::$current_time;
        self::$save_path = Env::get('root_path').'/public';
        self::$spreadsheet = new Spreadsheet();
        self::$sheet = self::$spreadsheet->getActiveSheet();
        //处理过期文件
        $this->_handleFileClear();
    }

    private function _handleFileClear()
    {
        $clear_path = self::$save_path.self::$file_path;
        $clear_path = substr($clear_path,0,-10);
        if(!empty($clear_path)) {
            $arr = scandir($clear_path);
            foreach ($arr as $vo) {
                $create_time = substr($vo,0,10);
                //文件前缀
                $prefix = substr($vo,0,1);
                if(!in_array($prefix,self::$ignore_file) && is_numeric($create_time) && self::$current_time-$create_time > self::expire_second) {
                    //删除文件
                    unlink($clear_path.$vo);
                }
            }
        }
    }

    //工作日志导出
    public function workLogs()
    {

        $company_id = $this->request->param('cid',$this->com_id,'intval');
        $user_id = $this->request->param('uid',0,'intval');

        $year = $this->request->request('year',0,'intval');
        $time[] = empty($year)?date('Y'):$year;
        $month = $this->request->request('month',0,'intval');
        $time[] = empty($month)?date('m'):$month;
        $time[] = '1';

        $year_month = implode('-',$time);
//
        $model = new \app\common\model\Users();
        $data = $model->workSignDetail($company_id,$year_month,$user_id);
        list($year,$month,$days,$week) = get_month_day($year_month);
        $month_title =  $year.'年'.$month.'月';
        //处理数据
        $result_data = [];
        foreach($data as $key=>$vo) {
            $arr = [++$key,$vo['name']];
            $every_day  =[];
            //'应出勤天数','请假次数','迟到次数','早退次数','实际出勤天数'
            $sure_day = $req_day = $late_times = $advance_times = $work_day = 0;
            $req_day = empty($vo['link_req_event_count']['req_times'])?0:$vo['link_req_event_count']['req_times'];

            for($i=1;$i<=2;$i++){
                $every_day_info =[];
                array_push($every_day_info,$i==1?'上午':'下午');
                foreach($days as $day){
                    $current_day = $year.'-'.$month.'-'.$day;
                    //打卡信息
                    $sign_str = $current_day.'-'.$i;

                    if(isset($vo['sign_data'][$sign_str])){
                       array_push($every_day_info,'✔');
                        $vo['sign_data'][$sign_str]['nss']==1 && $late_times++;
                        $vo['sign_data'][$sign_str]['nss']==2 && $advance_times++;
                        $work_day = $vo['sign_data'][$sign_str]['day']; //天数
                    }else{
                        array_push($every_day_info,'');
                    }
                }
                array_push($every_day,$every_day_info);
            }
            array_push($arr,$every_day);

            //打卡信息
            array_push($arr, $sure_day, $req_day, $late_times, $advance_times, $work_day);

            array_push($result_data,$arr);
        }


        $data = [
            '考勤表',
            ['序号','姓名',
                [
                    [$month_title],
                    array_merge(['时间'],$days),
                    array_merge(['星期'],$week),

                ],
                '应出勤天数','请假次数','迟到次数','早退次数','实际出勤天数'
            ],
        ];
        $data = array_merge($data,$result_data);
//        dump($data);exit;
        $this->handleSheetData($data);



        try{
            $writer = new Xlsx(self::$spreadsheet);
            $path = self::$file_path.$this->admin_id.'_'.$this->com_id.'_'.date('Y-m-d-H').rand(1000,9999).'.xlsx';
            $writer->save(self::$save_path.$path);
            return ['code'=>1,'msg'=>'获取成功','path'=>$path];
        }catch (\Exception $e){
            return ['code'=>0,'msg'=>'资源异常:'.$e->getMessage()];
        }
    }

    private $record_prev_data; //记录上一条记录

    private $record_row;    //保存当前处理行号
    //记录当前数据信息
    private $temp_col_step=0;      //临时列增量
    private $deep=1;
    private $arr_max_width=1;
    private $include_max_width=1;
    private $max_height=1;

    //
//    private $merge_deep = 0;
//    private $merge_width = 0;
//    private $merge_type = 0;


    //处理数据
    public  function handleSheetData(array $data,$index=0)
    {
        $current_row=1;
        foreach ($data as $key=>$vo) {
            $this->_setSheetInfo($vo,$current_row,1,true);
            $current_row+=($this->max_height>1?($this->max_height-1):1);
            //重置数据
            $this->temp_col_step=0;
            $this->deep=1;
            $this->arr_max_width=1;
            $this->include_max_width=1;
            $this->max_height=1;
        }
    }


    //添加单元格
    private function _setSheetInfo($data,$row,$col=1,$flush_info=true)
    {
        //记录行号
        if($flush_info){
            $this->record_row=$row;
            if(is_array($data)){
                list($this->deep,$this->arr_max_width,$this->include_max_width) = get_arr_deep($data);
            }
        }

        if(is_array($data)){
            foreach ($data as $key=>$vo) {
                $cur_col = $col+$key;
                if(is_array($vo)){
                    list($deep,$arr_max_width,$include_max_width)=$this->_setSpecialSheetInfo($vo,$this->record_row ,$cur_col);
                    $this->temp_col_step += $include_max_width-1;
                }else{
                    $this->_setSheetInfo($vo,$row ,$cur_col,false);
                }
            }
        }else{
            //设置单元格数据
            $col+=$this->temp_col_step;
            self::$sheet->setCellValueByColumnAndRow($col,$row,$data);
//            $temp_col=$col-1;
//            //处理上一条内容--是否合并单元格等信息
//            $this->_handleSheetMerge($temp_col,$index);
//            //记录当前记录单元格数据数据
//            $this->record_prev_data = [$temp_col, $temp_row, $this->deep,$this->arr_max_width,$this->include_max_width];

        }
    }
    //特殊数据特殊处理
    private function _setSpecialSheetInfo(array $data,$row,$col)
    {
        $init_height = 1;
        //获取当前数组基本信息
        list($deep,$arr_max_width,$include_max_width)=get_arr_deep($data);
        foreach ($data as $key=>$vo) {
            $init_height++;
            $this->_setSheetInfo($vo,$row ,$col,false);
            $row++;
        }
        $deep=$key+1;//处理深度
        //保存所占行数
        $init_height>$this->max_height && $this->max_height = $init_height;
        return [$deep,$arr_max_width,$include_max_width];
    }

    //处理单元格合并
    private function _handleSheetMerge($current_col=0,$current_row=0)
    {
        if(!$this->record_prev_data){
            return;
        }
        list($col,$row,$deep,$width,$type) = $this->record_prev_data;
        $handle_data = $this->original_data[$current_row];
        if(isset($handle_data[$current_col])){
            if(is_array($handle_data)&& $deep>1){
                //合并row
                self::$sheet->mergeCellsByColumnAndRow($col+1,$row+1,$col+1,($deep+$row+1));
            }
        }

//        if($type==1) {
//            //合并col
//            self::$sheet->mergeCellsByColumnAndRow($col,$row,($col+$width),$row);
//
//        }elseif($type==2) {
//            //合并row
//            self::$sheet->mergeCellsByColumnAndRow($col,$row,$col,($deep+$row));
//        }
    }







    public function test()
    {

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', '考勤表');

        $sheet->setCellValueByColumnAndRow(1,2,'abc');
        $sheet->mergeCellsByColumnAndRow(1,1,1,2);

        $writer = new Xlsx($spreadsheet);
        $writer->save('hello world.xlsx');
    }
}