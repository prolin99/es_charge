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
//取得中文班名
$class_list_c = es_class_name_list_c('long')  ;      

/*-----------執行動作判斷區----------*/
$item_id=empty($_REQUEST['item_id'])?"":$_REQUEST['item_id'];


if  ($item_id) {
 

	//細項名稱
	//$detail_list=get_item_detail_list_name($item_id) ;

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;
 
 
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
	$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
	$objActSheet->setTitle("各班收費");  //設定標題	
 	//設定框線
	$objBorder=$objActSheet->getDefaultStyle()->getBorders();
	$objBorder->getBottom()
          	->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
          	->getColor()->setRGB('000000'); 
	$objActSheet->getDefaultRowDimension()->setRowHeight(15);

	
	$row= 0  ;
	
	$row++ ;
       //標題行
      	$objPHPExcel->setActiveSheetIndex(0) 
            ->setCellValue('A' . $row, 'NO.')
            ->setCellValue('B' . $row, '班級')
            ->setCellValue('C' . $row, '項目金額')
            ->setCellValue('D' . $row, '應收人數')
             ->setCellValue('E' . $row, '項目總額')
              ->setCellValue('F' . $row, '減免總額')
              ->setCellValue('G' . $row, '應收總額')
              ;
 	
 	
	//有繳費的各班級(由紀錄表中)
	$class_list = get_class_id_list($item_id) ; 
	foreach ($class_list as $class_id=> $class) {
 
		//分別輸出各班 部份	
 		class_output($class_id , $item_id) ;
	}	
	
	//總和
	$row++ ;
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A' . $row, "總和")
            	->setCellValue('C' . $row, "=SUM(C2:C".($row-1).")") 
            	->setCellValue('D' . $row,"=SUM(D2:D".($row-1).")") 
             	->setCellValue('E' . $row,"=SUM(E2:E".($row-1).")") 
              	->setCellValue('F' . $row,"=SUM(F2:F".($row-1).")") 
              	->setCellValue('G' . $row,"=SUM(G2:G".($row-1).")") 
              	;
 		
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=class_sum_'.date("mdHi").'.xls' );
	header('Cache-Control: max-age=0');

	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;		
 
}
 
function class_output( $class_id , $item_id) {
 
	global    $charge_array , $objPHPExcel , $row ,$class_list_c ;
	

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
       //資料行
      	$objPHPExcel->setActiveSheetIndex(0) 
            ->setCellValue('A' . $row, $row-1)
            ->setCellValue('B' . $row,$class_list_c[$class_id])
            ->setCellValue('C' . $row, $class_sum['each'] )
            ->setCellValue('D' . $row,$class_sum['man'] )
             ->setCellValue('E' . $row,$class_sum['all'] )
              ->setCellValue('F' . $row, $class_sum_decase['all_sum'])
              ->setCellValue('G' . $row, $class_sum['all']  - $class_sum_decase['all_sum'] )
              ; 	
  

} 
 