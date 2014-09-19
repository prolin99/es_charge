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
	$objActSheet->setTitle("細目統計");  //設定標題	
 	//設定框線
	$objBorder=$objActSheet->getDefaultStyle()->getBorders();
	$objBorder->getBottom()
          	->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
          	->getColor()->setRGB('000000'); 
	$objActSheet->getDefaultRowDimension()->setRowHeight(15);
 
	//有繳費的各學年(由紀錄表中)
	$grade_list = get_grade_id_list($item_id) ; 
 	$min_grade = $grade_list[0] ;		//最低年級
	$row= 0  ;

	$all_sum_data= '' ;


	foreach ($grade_list as $y=> $grade) {
 		//分別輸出各年級 部份	
 		class_output($y , $item_id) ;
	}	

	//全校總和
	all_sum_output() ;
	
 
 
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=year_detail_'.date("mdHi").'.xls' );
	header('Cache-Control: max-age=0');

	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;		
 
 
}
 
function class_output( $y , $item_id) {
 
	global   $detail_list ,  $charge_array , $objPHPExcel , $row  , $min_grade , $all_sum_data  ;
	

	//本年級各項應付
         
	foreach ($charge_array as $detail_id => $dollars) {
		$my_class_charge_array['pay'][$detail_id] = $dollars[$y-1] ;
 	 
	}              
	
 	//班上應收總額及人數
 	$class_sum = get_grade_source_pay_sum($item_id ,$y  , $my_class_charge_array)  ;
 	
 	//減免小計
 	$class_sum_decase  = get_grade_decrease_sum($item_id ,$y) ;
 	
 	$col = ($y-$min_grade)*5+1 ;
 	$row++ ;
       //標題行(班級)
      	$objPHPExcel->setActiveSheetIndex(0) 
            ->setCellValue('A' . $row, $y .'年級'   . "( {$class_sum['man']} 人)"  )  ;
	$all_sum_data['man'] += $class_sum['man'] ;
 
            
 	$row++ ;
       //標題行
      	$objPHPExcel->setActiveSheetIndex(0) 
            ->setCellValue('A' . $row, '收費細目')
            ->setCellValue('B' . $row, '項目金額')
            ->setCellValue('C' . $row, '年級金額')
            ->setCellValue('D' . $row, '減免人數')
             ->setCellValue('E' . $row, '減免金額')
              ->setCellValue('F' . $row, '應收金額') 
              ;	 	

 	foreach   (  $detail_list   as $detail_id => $detail ) {
		$row++ ;
		//資料行
		$objPHPExcel->setActiveSheetIndex(0) 
			->setCellValue('A' . $row, $detail)
			->setCellValue('B' . $row, $charge_array[$detail_id][$y-1]  )
			->setCellValue('C' . $row, $class_sum['detail'][$detail_id] )
			->setCellValue('D' . $row, $class_sum_decase['man'][$detail_id]  )
			->setCellValue('E' . $row,  $class_sum_decase['sum'][$detail_id]  )
			->setCellValue('F' . $row,  $class_sum['detail'][$detail_id] - $class_sum_decase['sum'][$detail_id] )
			; 	
		$all_sum_data['detail'][$detail_id]   +=	$class_sum['detail'][$detail_id] - $class_sum_decase['sum'][$detail_id]  ;
	}
	//總計
	$row++ ;
  	$objPHPExcel->setActiveSheetIndex(0) 
			->setCellValue('A' . $row, '總和')
			->setCellValue('F' . $row, $class_sum['all']  - $class_sum_decase['all_sum']   )
			;
	$all_sum_data['all_sum'] += ( $class_sum['all']  - $class_sum_decase['all_sum'] )  ;

	//加入分頁
	$objPHPExcel->setActiveSheetIndex(0)->setBreak('A'.$row , PHPExcel_Worksheet::BREAK_ROW);
			

} 

function all_sum_output( ) {
 
	global   $detail_list ,  $charge_array , $objPHPExcel , $row  , $min_grade , $all_sum_data  ;
	

    
 	$col = ($y-$min_grade)*5+1 ;
 	$row++ ;
       //標題行(班級)
      	$objPHPExcel->setActiveSheetIndex(0) 
            ->setCellValue('A' . $row,  '全校繳費 ('   . $all_sum_data['man'] . "人)"  )  ;
	
 
            
 	$row++ ;
       //標題行
      	$objPHPExcel->setActiveSheetIndex(0) 
            ->setCellValue('A' . $row, '收費細目')
            ->setCellValue('B' . $row, '項目金額')
            ->setCellValue('C' . $row, '年級金額')
            ->setCellValue('D' . $row, '減免人數')
             ->setCellValue('E' . $row, '減免金額')
              ->setCellValue('F' . $row, '應收金額') 
              ;	 	

 	foreach   (  $detail_list   as $detail_id => $detail ) {
		$row++ ;
		//資料行
		$objPHPExcel->setActiveSheetIndex(0) 
			->setCellValue('A' . $row, $detail)
 			->setCellValue('F' . $row,  $all_sum_data['detail'][$detail_id]   )
			; 	
 
	}
	//總計
	$row++ ;
  	$objPHPExcel->setActiveSheetIndex(0) 
			->setCellValue('A' . $row, '總和')
			->setCellValue('F' . $row, $all_sum_data['all_sum']  )
			;
 

	//加入分頁
	$objPHPExcel->setActiveSheetIndex(0)->setBreak('A'.$row , PHPExcel_Worksheet::BREAK_ROW);
			

} 
 
