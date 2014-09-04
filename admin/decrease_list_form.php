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

	//$detail_id_array = array_keys($data['detail_list']) ; 

	//取得全部細項的收費
	//$charge_array= get_detail_charge_dollars( $item_id) ;
	
	//全部已填的減免資料
	$data['decase_list'] = get_all_decrease_list_item_array( $item_id ,  'only') ;
	
	//取得導師名冊
	$teacher_list = get_class_teacher_list() ;
 

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
	$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
	$objActSheet->setTitle("減免整理表");  //設定標題		
	$objActSheet->getDefaultRowDimension()->setRowHeight(15);
 	
      //標題行
      	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'NO.')
            ->setCellValue('B1', '班級')
            ->setCellValue('C1', '導師')
            ->setCellValue('D1', '學生姓名')
            ->setCellValue('E1', '性別') ;
            $col = 'E' ;
 	//身份別
 	foreach   ( $decrease_cause as $d_id => $cause_str ) {
		if ($d_id>0) {
			$col++ ;
			$col_str =$col . '1' ;
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str , $cause_str ) ;
			//echo $cause_str  ;
		}

	}	
 	
 	//項目名
	foreach   (  $data['detail_list'] as $detail_id => $detail ) {
		$col++ ;
		$col_str =$col . '1' ;

		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str, $detail."減免") ;

	}	
	/*
 	$col++ ;
	$col_str = $col . '1' ;
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str , '減免原因') ;
	*/
 
 	$row=1 ;
        //資料區
        foreach ($data['decase_list'] as $stud_id => $stud )  {
        	$row++ ;
 
       	
       		$objPHPExcel->setActiveSheetIndex(0)
            		->setCellValue('A'.$row,$row-1)
            		->setCellValue('B'.$row , $stud['curr_class_num'])
            		->setCellValue('C'.$row ,$teacher_list[$stud['curr_class_num']])
            		->setCellValue('D'.$row, $stud['name'])
            		->setCellValue('E'.$row, $stud['sex']);
            	
            	//身份	
            	//echo $stud['cause_id']   ;
         	$col =chr(ord( 'E') + $stud['cause_id'] ) ;	
         	$col_str = $col .$row ;
         	//echo $col ;
         	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$col_str",  'v' ) ;
         	
         	//減免  
         	//$col = 'K' ;	
         	$col = chr(ord( 'E')  + count($decrease_cause) -1);
		foreach   (  $data['detail_list'] as $detail_id => $detail ) {
			$col++ ;
			$col_str = $col .$row ;

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$col_str", $stud['dollar'][ $detail_id]) ;
			//如果另一個身份別代號
			if ($stud['other'][ $detail_id]) {
				$col =chr(ord( 'E') + $stud['other'][ $detail_id] ) ;	
         				$col_str = $col .$row ;
         				//echo $col ;
         				$objPHPExcel->setActiveSheetIndex(0)->setCellValue("$col_str",  'v' ) ;
			}

		}
		/*
		$col++ ;
		$col_str = $col .$row ;
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col_str , $stud['cause']) ;		
		//echo  $stud['cause'] ;
 		*/
	}
 
	$objPHPExcel->getActiveSheet()->setTitle('student_decrease_list');
 
 
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=decrease_frm_'.date("mdHi").'.xls' );
	header('Cache-Control: max-age=0');
 
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;		
 
}
