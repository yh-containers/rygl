<?php
namespace app\admin\controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\App;

class HandleExcel extends Common
{
    protected static $spreadsheet;
    protected static $sheet;
//    protected static


    public function __construct(App $app = null)
    {
        parent::__construct($app);


        self::$spreadsheet = new Spreadsheet();
        self::$sheet = self::$spreadsheet->getActiveSheet();
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

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');//告诉浏览器输出07Excel文件
//header(‘Content-Type:application/vnd.ms-excel‘);//告诉浏览器将要输出Excel03版本文件
        header('Content-Disposition: attachment;filename="'.$month_title.'.xlsx"');//告诉浏览器输出浏览器名称
        header('Cache-Control: max-age=0');//禁止缓存
        $writer = new Xlsx(self::$spreadsheet);
        $writer->save('php://stdout');

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
        dump($current_row);
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