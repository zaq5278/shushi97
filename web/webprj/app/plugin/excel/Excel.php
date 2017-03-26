<?php
namespace Plugin\Excel;

class Excel{
    public function getExcel($datas,$titlename,$filename) {
        require_once(APP_DIR.'/plugin/excel/PHPExcel.php');
        require_once(APP_DIR.'/plugin/excel/PHPExcel/Writer/Excel2007.php');
        require_once(APP_DIR.'/plugin/excel/PHPExcel/Writer/Excel5.php');
        include_once(APP_DIR.'/plugin/excel/PHPExcel/IOFactory.php');
        $objExcel = new \PHPExcel();
        //设置属性 (这段代码无关紧要，其中的内容可以替换为你需要的)
        $objExcel->getProperties()->setCreator("andy");
        $objExcel->getProperties()->setLastModifiedBy("andy");
        $objExcel->getProperties()->setTitle("Office 2003 XLS Test Document");
        $objExcel->getProperties()->setSubject("Office 2003 XLS Test Document");
        $objExcel->getProperties()->setDescription("Test document for Office 2003 XLS, generated using PHP classes.");
        $objExcel->getProperties()->setKeywords("office 2003 openxml php");
        $objExcel->getProperties()->setCategory("Test result file");
        $objExcel->setActiveSheetIndex(0);
        $i=0;
        //表头
        $colm = 'a';
        foreach($titlename as $k => $v){
            $objExcel->getActiveSheet()->setCellValue(($colm++).'1', "$v");
        }
        //debug($links_list);
        $links_list = $datas;
        $ex = '';
        /*----------写入内容-------------*/
        foreach($links_list as $k=>$v) {
            $colmd = 'a';
            $u1=$i+2;
            foreach($v as $key => $value){
                $objExcel->getActiveSheet()->setCellValue(($colmd++).$u1, $value);
            }
            $i++;
        }
        // 高置列的宽度
        /*$objExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $objExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
        $objExcel->getActiveSheet()->getColumnDimension('C')->setWidth(70);
        $objExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);*/
        $objExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&BPersonal cash register&RPrinted on &D');
        $objExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objExcel->getProperties()->getTitle() . '&RPage &P of &N');
        // 设置页方向和规模
        $objExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objExcel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objExcel->setActiveSheetIndex(0);
        $timestamp = time();
        if($ex == '2007') { //导出excel2007文档
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="links_out'.$timestamp.'.xlsx"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
            $objWriter->save('php://output');
        } else { //导出excel2003文档
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
            $objWriter->save('php://output');
        }
    }
}