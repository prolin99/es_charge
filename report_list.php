<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/


include_once "header.php";

require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel.php'; //引入 PHPExcel 物件庫
require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php'; //引入PHPExcel_IOFactory 物件庫

/*-----------function區--------------*/
//取得中文班名
$class_list_c = es_class_name_list_c('long')  ;

/*-----------執行動作判斷區----------*/
if  ($item_id) {
	//取得所在班級
	$class_id =get_my_class_id($xoopsUser->uid() ) ;
	if ($_GET['class_id']  and $isAdmin )
		$class_id = $_GET['class_id'] ;
	//細項名稱
	$detail_list=get_item_detail_list_name($item_id) ;

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;


 	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
	$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
	$objActSheet->setTitle("收據");  //設定標題
  	//設定框線
	$objBorder=$objActSheet->getDefaultStyle()->getBorders();
	$objBorder->getBottom()
          	->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
          	->getColor()->setRGB('000000');
	$objActSheet->getDefaultRowDimension()->setRowHeight(15);


	$row= 1 ;
       //標題行
      	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $row, 'NO.')
            ->setCellValue('B' . $row, '班級')
            ->setCellValue('C' . $row, '座號')
            ->setCellValue('D' . $row, '學生姓名') ;
            $col = 'D' ;

 	//項目
	foreach   (  $detail_list  as $detail_id => $detail ) {
		$col++ ;
		$col_str =$col .$row ;
 		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str, $detail ) ;
	}
	$col++ ;
	$col_str =$col .  $row ;
 	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str, '小計' ) ;

	$col++ ;
	$col_str =$col .  $row ;
 	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str, '繳費方式' ) ;

	$col++ ;
	$col_str =$col .  $row ;
 	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str, '身分別' ) ;

	$col++ ;
	$col_str =$col .  $row ;
 	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str, '註記' ) ;

	//取得班上要繳費的人員資料
	$class_students= get_class_pay_students($class_id  , $item_id) ;

	//取得班上 有減免的資料
	$class_decase_list = get_decrease_list_item_array($class_id , $item_id) ;


        //資料區
        foreach ( $class_students  as $stud_id => $stud )  {
        	$row++ ;

        	$y = ($class_id /100)-1 ;

       		$objPHPExcel->setActiveSheetIndex(0)
            		->setCellValue('A'.$row,$row-1)
            		->setCellValue('B'.$row , $class_list_c[$stud['class_id']])
            		->setCellValue('C'.$row ,$stud['class_sit_num'])
            		->setCellValue('D'.$row, $stud['name']) ;
         	$col = 'D' ;
         	$stud_pay=0 ;  //學生小計
		foreach   (  $detail_list   as $detail_id => $detail ) {

			$col++ ;
			$col_str = $col .$row ;
			//應收
			$s_pay =$charge_array[$detail_id][$y] ;
			//實付
			$pay = $charge_array[$detail_id][$y] -$class_decase_list[$stud_id]['dollar'][ $detail_id] ;
			$stud_pay += $pay ;

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$col_str",$pay) ;
		}
  		$col++ ;
		$col_str = $col .$row ;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$col_str",$stud_pay) ;

  		$col++ ;
		$col_str = $col .$row ;
		if  ($stud['in_bank'] ==0)
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$col_str",'自行繳款') ;

		$col++ ;
		$col_str = $col .$row ;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$col_str",$decrease_cause[$stud['cause']]) ;

		$col++ ;
		$col_str = $col .$row ;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$col_str",$stud['ps']) ;

	}










	//header('Content-Type: application/vnd.ms-excel');
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename=receipt'.date("mdHi").'.xlsx' );
	header('Cache-Control: max-age=0');
	ob_clean();

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');

	exit;


}
?>
