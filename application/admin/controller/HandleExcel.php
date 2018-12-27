<?php
namespace app\admin\controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HandleExcel extends Common
{
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
//        $model = new \app\common\model\Users();
//        $data = $model->workSignDetail($company_id,$year_month,$user_id);
        list($year,$month,$days,$week) = get_month_day($year_month);
        $month_title =  $year.'年'.$month.'月';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $current_row = 1;
        $sheet->setCellValueByColumnAndRow($current_row,1, '考勤表');
        $current_row++;
        $data = ['序号','姓名',
            [
                [$month_title],
                array_merge(['时间'],$days),
                array_merge(['星期'],$week),

            ],
            '应出勤天数','请假天数','迟到次数','早退次数','实际出勤天数'
        ];
        list($deep,$arr_max_width) = get_arr_deep($data);
        dump($data);exit;
        $req_space = 0;
        foreach ($data as $key=>$vo){
            $current_col = ++$key;
            if(is_array($vo)){
                foreach($vo as $k=>$item){
                    $temp_current_row = $current_row+$k;

                    if(is_array($item)){
                        foreach ($item as $sk=>$ship){
                            $tem_current_col = $current_col+$sk;
                            $sheet->setCellValueByColumnAndRow($tem_current_col,$temp_current_row,$ship);
                        }
                    }
                }
                $req_space = $arr_max_width;
            }else{
                $tem_current_col = $current_col+$req_space;
                $current_deep = $current_row+$deep-1;
                $sheet->setCellValueByColumnAndRow($tem_current_col,$current_row,$vo);
                $sheet->mergeCellsByColumnAndRow($tem_current_col,$current_row,$tem_current_col,$current_deep);
            }
        }


        $writer = new Xlsx($spreadsheet);
        $writer->save('hello world.xlsx');
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