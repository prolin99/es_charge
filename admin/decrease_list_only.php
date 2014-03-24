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
	$data['detail_list']=get_item_detail_list_name($item_id) ;

	$detail_id_array = array_keys($data['detail_list']) ; 

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;
	
	//全部已填的減免資料
	$data['decase_list'] = get_all_decrease_list_item_array( $item_id ,  'only') ;
 

	$objPHPExcel = new PHPExcel();

 	
      //標題行
      	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'NO.')
            ->setCellValue('B1', '班級')
            ->setCellValue('C1', '座號')
            ->setCellValue('D1', '性別')
            ->setCellValue('E1', '學生姓名') ;
            $col = 'E' ;
 
	foreach   (  $data['detail_list'] as $detail_id => $detail ) {
		$col++ ;
		$col_str =$col . '1' ;

		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str, $detail."減免") ;

	}	
 	$col++ ;
	$col_str = $col . '1' ;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str , '減免原因') ;

 
 	$row=1 ;
        //資料區
        foreach ($data['decase_list'] as $stud_id => $stud )  {
        	$row++ ;
        	//echo $col_str  ;
        	//$stud['sex']=1 ;
        	$y = ($stud['curr_class_num'] /100)-1 ; 
       	
       		$objPHPExcel->setActiveSheetIndex(0)
            		->setCellValue('A'.$row,$row-1)
            		->setCellValue('B'.$row , $stud['curr_class_num'])
            		->setCellValue('C'.$row ,$stud['class_sit_num'])
            		->setCellValue('D'.$row, $stud['sex'])
            		->setCellValue('E'.$row, $stud['name']) ;
         	$col = 'E' ;	
		foreach   (  $data['detail_list'] as $detail_id => $detail ) {
			$col++ ;
			$col_str = $col .$row ;

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$col_str", $stud['dollar'][ $detail_id]) ;
	
		}
		$col++ ;
		$col_str = $col .$row ;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str , $stud['cause']) ;		
		//echo  $stud['cause'] ;
 
	}
 
	$objPHPExcel->getActiveSheet()->setTitle('student_decrease_list');
 

	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=decrease_o_'.date("mdHi").'.xls' );
	header('Cache-Control: max-age=0');
 
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;		

}
