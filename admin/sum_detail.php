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
 

/*-----------執行動作判斷區----------*/
$item_id=empty($_REQUEST['item_id'])?"":$_REQUEST['item_id'];


if  ($item_id) {
 

	//細項名稱
	$detail_list=get_item_detail_list_name($item_id) ;

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;
 
 
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
	$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
	$objActSheet->setTitle("各班收費");  //設定標題	
 

	
	$row= 0  ;
	

 	
 	
	//有繳費的各班級(由紀錄表中)
	$class_list = get_class_id_list($item_id) ; 
	foreach ($class_list as $class_id=> $class) {
 
		//分別輸出各班 部份	
 		class_output($class_id , $item_id) ;
	}	
	
 
 		
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=class_detail_'.date("mdHi").'.xls' );
	header('Cache-Control: max-age=0');

	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;		
 
}
 
function class_output( $class_id , $item_id) {
 
	global   $detail_list ,  $charge_array , $objPHPExcel , $row ;
	

	//本班各項應付
      	$y = ($class_id /100)-1 ;               
	foreach ($charge_array as $detail_id => $dollars) {
		$my_class_charge_array['pay'][$detail_id] = $dollars[$y] ;
		//$my_class_charge_array['decease'][$detail_id] = '一半:' .  $dollars[$y] /2  . '  or 全額:' .  $dollars[$y] ;
	}              
	
 	//班上應收總額及人數
 	$class_sum = get_class_source_pay_sum($item_id ,$class_id  , $my_class_charge_array)  ;
 	
 	//減免小計
 	$class_sum_decase  = get_class_decrease_sum($item_id ,$class_id) ;
 	
 	
 	$row++ ;
       //標題行(班級)
      	$objPHPExcel->setActiveSheetIndex(0) 
            ->setCellValue('A' . $row, $class_id  . "( {$class_sum['man']} 人)" )  ;
            
 	$row++ ;
       //標題行
      	$objPHPExcel->setActiveSheetIndex(0) 
            ->setCellValue('A' . $row, '收費細目')
            ->setCellValue('B' . $row, '項目金額')
            ->setCellValue('C' . $row, '班級金額')
            ->setCellValue('D' . $row, '減免人數')
             ->setCellValue('E' . $row, '減免金額')
              ->setCellValue('F' . $row, '應收金額') 
              ;
 	foreach   (  $detail_list   as $detail_id => $detail ) {
		$row++ ;
		//資料行
		$objPHPExcel->setActiveSheetIndex(0) 
			->setCellValue('A' . $row, $detail)
			->setCellValue('B' . $row, $charge_array[$detail_id][$y]  )
			->setCellValue('C' . $row, $class_sum['detail'][$detail_id] )
			->setCellValue('D' . $row, $class_sum_decase['man'][$detail_id]  )
			->setCellValue('E' . $row,  $class_sum_decase['sum'][$detail_id]  )
			->setCellValue('F' . $row,  $class_sum['detail'][$detail_id] - $class_sum_decase['sum'][$detail_id] )
			; 	
	}
	//總計
	$row++ ;
  	$objPHPExcel->setActiveSheetIndex(0) 
			->setCellValue('A' . $row, '總和')
			->setCellValue('F' . $row, $class_sum['all']  - $class_sum_decase['all_sum']   )
			;
	
	//班級導師
	$row++ ;
  	$objPHPExcel->setActiveSheetIndex(0) 
			->setCellValue('A' . $row, '班級導師簽章') ;
	
 
	//加入分頁
	$objPHPExcel->setActiveSheetIndex(0)->setBreak('A'.$row , PHPExcel_Worksheet::BREAK_ROW);
			

} 
 