<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
include_once "header_admin.php";


include_once "../function.php";

include_once "../../tadtools/PHPExcel.php";
require_once '../../tadtools/PHPExcel/IOFactory.php';
/*-----------function區--------------*/




	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
	$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
	$objActSheet->setTitle("郵局帳號清單");  //設定標題

	$row=1 ;
	//標題行 //0年級	1班級代號	2座號	3學生姓名	4性別	5學號	6純特戶	7轉帳戶名	8轉帳戶身份證編號	9存款別	10立帳局號	11存簿帳號	12劃撥帳號	13電話號碼	14地址	15身份別
	$objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A' . $row, '年級')
        ->setCellValue('B' . $row, '班級代號')
        ->setCellValue('C' . $row, '座號')
        ->setCellValue('D' . $row, '學生姓名')
		->setCellValue('E' . $row, '性別')
		->setCellValue('F' . $row, '學號')
		->setCellValue('G' . $row, '6純特戶')
		->setCellValue('H' . $row, '轉帳戶名')
		->setCellValue('I' . $row, '轉帳戶身份證編號')
		->setCellValue('J' . $row, '存款別')
		->setCellValue('K' . $row, '立帳局號')
		->setCellValue('L' . $row, '存簿帳號')
		->setCellValue('M' . $row, '劃撥帳號')
		->setCellValue('N' . $row, '電話號碼')
		->setCellValue('O' . $row, '地址')
		->setCellValue('P' . $row, '身份別')		 ;

	$sql = " SELECT a.* , b.class_id , b.class_sit_num , b.name  From "  . $xoopsDB->prefix("charge_account") . " as a , "
		. $xoopsDB->prefix("e_student") .  " as  b " .
		"  where a.stud_sn  = b.stud_id  order by class_id,class_sit_num  " ;

	$result = $xoopsDB->query($sql)   ;
	while($stud=$xoopsDB->fetchArray($result)){
		$row++ ;
		$y = floor($stud['class_id'] / 100) ;
		$c = $stud['class_id']  - $y*100  ;
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$row, $y)
			->setCellValue('B'.$row , $c )
			->setCellValue('C'.$row ,$stud['class_sit_num'])
			->setCellValue('D'.$row, $stud['name'])
			->setCellValue('E'.$row, $stud['sex'])
			->setCellValue('F'.$row, $stud['stud_id'])
			->setCellValue('G'.$row, 0)
			->setCellValue('H'.$row, $stud['acc_name'])
			->setCellValue('I'.$row, $stud['acc_person_id'])
			->setCellValue('J'.$row, $stud['acc_mode'])
			->setCellValue('K'.$row, $stud['acc_b_id'])
			->setCellValue('L'.$row, $stud['acc_id'])
			->setCellValue('M'.$row, $stud['acc_g_id'])
			->setCellValue('N'.$row, '')
			->setCellValue('O'.$row, '')
			->setCellValue('P'.$row, '')	;
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('K'.$row)->getNumberFormat()->setFormatCode('0000000') ;
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('L'.$row)->getNumberFormat()->setFormatCode('0000000') ;
	}



	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=account'.date("mdHi").'.xlsx' );
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;