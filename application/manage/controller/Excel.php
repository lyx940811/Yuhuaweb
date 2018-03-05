<?php
/**
 * Created by PhpStorm.
 * @Author: luowangbao
 * @Date: 2016/6/1 10:25
 * @Function:Excel导出工具
 */
namespace app\manage\controller;
use think\request;
use think\Loader;
class Excel
{
    /**

     * excel表格导出

     * @param string $fileName 文件名称

     * @param array $headArr 表头名称

     * @param array $data 要导出的数据

     * @author static7  */

    public function exportExcel($expTitle,$expCellName,$expTableData,$Exce_name){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        //$fileName = $_SESSION['account'].date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $fileName =$expTitle.date('Y_m_d').$Exce_name;
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

//        extend("PHPExcel.PHPExcel");

        Loader::import('PHPExcel.PHPExcel');//手动引入PHPExcel.php
        Loader::import('PHPExcel.PHPExcel.IOFactory.PHPExcel_IOFactory');//引入IOFactory.php 文件里面的PHPExcel_IOFactory这个类
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        // $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

        exit;
    }
    public function excelExport($fileName = '', $headArr = [], $data = [],$name,$type) {

        $fileName .= "_" . date("Y_m_d", Request::instance()->time()) . ".xls";
        Loader::import('PHPExcel.PHPExcel');//手动引入PHPExcel.php
        Loader::import('PHPExcel.PHPExcel.IOFactory.PHPExcel_IOFactory');

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties();

        $key = ord("A"); // 设置表头

        foreach ($headArr as $v) {

            $colum = chr($key);

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

            $key += 1;

        }

        $column = 2;

        $objActSheet = $objPHPExcel->getActiveSheet();
        $a=0;
        foreach ($data as $key => $rows) { // 行写入

            $span = ord("A");
            if($type==1){
                unset($rows['id']);
                unset($rows['sn']);
            }elseif($type==2){
                unset($rows['userid']);
                unset($rows['majors']);
                unset($rows['id']);
                unset($rows['type']);
                unset($rows['paperid']);
            }
            if(!empty($rows['idcard'])){
                $rows['idcard'] .=  ' ';
            }
            foreach ($rows as $keyName => $value) { // 列写入
                $objActSheet->setCellValue(chr($span) . $column, $value);

                $span++;

            }

            $column++;

        }

        $fileName = iconv("utf-8", "gb2312", $fileName); // 重命名表

        $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表

        header('Content-Type: application/vnd.ms-excel');

        header("Content-Disposition: attachment;filename=$fileName");

        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('php://output'); // 文件通过浏览器下载

        exit();

    }
}
