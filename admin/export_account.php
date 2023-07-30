<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
include_once "header.php";
include_once '../function.php';

require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel.php'; //引入 PHPExcel 物件庫
require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php'; //引入PHPExcel_IOFactory 物件庫

/*-----------function區--------------*/

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
    $objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
    $objActSheet->setTitle('郵局帳號清單');  //設定標題

    $row = 1;
    //標題行 //0年級	1班級代號	2座號	3學生姓名	4性別	5學號	6純特戶	7轉帳戶名	8轉帳戶身份證編號	9存款別	10立帳局號	11存簿帳號	12劃撥帳號	13電話號碼	14地址	15身份別
    //0年級	1班級代號	2座號	3學生姓名	4繳費 	5轉帳戶名	6轉帳戶身份證編號	7存款別(P/G)	8立帳局號	9存簿帳號	10劃撥帳號
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A'.$row, '年級')
        ->setCellValue('B'.$row, '班級代號')
        ->setCellValue('C'.$row, '座號')
        ->setCellValue('D'.$row, '學生姓名')
        ->setCellValue('E'.$row, '繳')
        ->setCellValue('F'.$row, '轉帳戶名')
        ->setCellValue('G'.$row, '轉帳戶身份證編號')
        ->setCellValue('H'.$row, '存款別')
        ->setCellValue('I'.$row, '立帳局號')
        ->setCellValue('J'.$row, '存簿帳號')
        ->setCellValue('K'.$row, '劃撥帳號')     ;

    $sql = ' SELECT a.* , b.class_id , b.class_sit_num , b.name  From '.$xoopsDB->prefix('charge_account').' as a , '
        .$xoopsDB->prefix('e_student').' as  b '.
        '  where a.stud_sn  = b.stud_id  order by class_id,class_sit_num  ';

    $result = $xoopsDB->query($sql);
    while ($stud = $xoopsDB->fetchArray($result)) {
        ++$row;
        $y = floor($stud['class_id'] / 100);
        $c = $stud['class_id']  - $y * 100;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$row, $y)
            ->setCellValue('B'.$row, $c)
            ->setCellValue('C'.$row, $stud['class_sit_num'])
            ->setCellValue('D'.$row, $stud['name'])
            ->setCellValue('E'.$row, 0)
            ->setCellValue('F'.$row, $stud['acc_name'])
            ->setCellValue('G'.$row, $stud['acc_person_id'])
            ->setCellValue('H'.$row, $stud['acc_mode'])
            ->setCellValue('I'.$row, $stud['acc_b_id'])
            ->setCellValue('J'.$row, $stud['acc_id'])
            ->setCellValue('K'.$row, $stud['acc_g_id'])    ;
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('I'.$row)->getNumberFormat()->setFormatCode('0000000');
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('J'.$row)->getNumberFormat()->setFormatCode('0000000');
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('K'.$row)->getNumberFormat()->setFormatCode('00000000000000');
    }

    //header('Content-Type: application/vnd.ms-excel');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename=account'.date('mdHi').'.xlsx');
    header('Cache-Control: max-age=0');
    ob_clean();

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
