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
	$detail_list=get_item_detail_list_name($item_id) ;

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;

 
 
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
	$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
	$objActSheet->setTitle("各班清單");  //設定標題	
	
 
	//設定框線
	$objBorder=$objActSheet->getDefaultStyle()->getBorders();
	$objBorder->getBottom()
          	->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
          	->getColor()->setRGB('000000'); 
	$objActSheet->getDefaultRowDimension()->setRowHeight(15);
	
	$row= 0  ;

 	
 	
	//有繳費的各班級
	$class_list = get_class_id_list($item_id) ; 
	foreach ($class_list as $class_id=> $class) {
		//分別輸出各班 部份	
 
		class_output($class_id , $item_id) ;
	}	
 
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=bank'.date("mdHi").'.xlsx' );
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;		
 
}
 
function class_output( $class_id , $item_id) {
 
	global   $detail_list , $charge_array , $objPHPExcel , $row ,$class_list_c ;
	
	$row++ ;
	//班級
	$objPHPExcel->setActiveSheetIndex(0) 
            ->setCellValue('A' . $row,  $class_list_c[$class_id] . '收費清冊' ) ;


          	
	$row++ ;
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
 	
 	
 	
	//取得班上要繳費的人員資料
	$class_students= get_class_pay_students($class_id  , $item_id) ;
 
	//取得班上 有減免的資料
	$class_decase_list = get_decrease_list_item_array($class_id , $item_id) ;
 
 	unset($no_bank);
	$no_bank = array();
        //資料區
        $i=0 ;
        foreach ( $class_students  as $stud_id => $stud )  {
		if  ($stud['in_bank'] ) {
			$i++ ;
			//銀行扣款
			$row++ ;
	
			$y = ($class_id /100)-1 ; 
		
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$row,$i)
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
			$in_bank_man++ ;
		}else {
			//自行繳費
			$no_bank[$stud_id]=  $stud ;
			$no_bank_man ++ ;
		}	
	}

	//自行繳費----------------------------------------------
	if  ($no_bank_man  ){
		$row++ ;
		//班級
		$objPHPExcel->setActiveSheetIndex(0) 
		->setCellValue('A' . $row,    '自行繳費' ) ;
		$row++ ;
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

		$i=0 ;
		foreach ( $no_bank  as $stud_id => $stud )  {
				$i++ ;
	
				//自行繳費
				$row++ ;
		
				$y = ($class_id /100)-1 ; 
			
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A'.$row,$i)
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
		
		}	

	}	
	//加入分頁
	$objPHPExcel->setActiveSheetIndex(0)->setBreak('A'.$row , PHPExcel_Worksheet::BREAK_ROW);

} 
 