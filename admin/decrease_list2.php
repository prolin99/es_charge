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
	$show_mode= 'all' ;
	if ($_GET['show']== 'only')
		$show_mode= 'only' ;

	//細項名稱
	$detail_list=get_item_detail_list_name($item_id) ;

	//$detail_id_array = array_keys($data['detail_list']) ;

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;

	//全部已填的減免資料
	$data['decase_list'] = get_all_decrease_list_item_kind_array( $item_id , $show_mode) ;

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
	$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
	$objActSheet->setTitle("依項目減免");  //設定標題
 	$objActSheet->getDefaultRowDimension()->setRowHeight(15);

      //標題行

        $objActSheet->setCellValue('A1', 'NO.');
        $objActSheet->setCellValue('B1', '類別');
        $objActSheet->setCellValue('C1', '班級');
        $objActSheet->setCellValue('D1', '座號');
      	$objActSheet->setCellValue('E1', '性別');
        $objActSheet->setCellValue('F1', '學生') ;
        $objActSheet->setCellValue('G1', '應收') ;
        $objActSheet->setCellValue('H1', '減免') ;
        $objActSheet->setCellValue('I1', '實付') ;
        $objActSheet->setCellValue('J1', '補助') ;
        $objActSheet->setCellValue('K1', '原因') ;


 	$row=1 ;
 	//var_dump ($data['decase_list'])  ;

        //資料區
        foreach ($data['decase_list'] as $stud_id => $stud )  {
        	$row++ ;
			//echo $row ;
        	$y = ($stud['curr_class_num'] /100)-1 ;

			$objActSheet->setCellValue('A'.$row,$row-1) ;
			$objActSheet->setCellValue('B'.$row, $detail_list[$stud['detail_id']]  ) ;
			$objActSheet->setCellValue('C'.$row ,$class_list_c[$stud['curr_class_num']]) ;
            $objActSheet->setCellValue('D'.$row ,$stud['class_sit_num']);
            $objActSheet->setCellValue('E'.$row, $stud['sex']);
            $objActSheet->setCellValue('F'.$row, $stud['name']) ;
            $s_pay =$charge_array[$stud['detail_id']][$y] ;
            $objActSheet->setCellValue('G'.$row, $s_pay) ;
            $objActSheet->setCellValue('H'.$row, $stud['decrease_dollar']+0) ;
            $pay = $s_pay-$stud['decrease_dollar'] ;
            $objActSheet->setCellValue('I'.$row, $pay) ;
			if ($stud['cause_chk'])
				$objActSheet->setCellValue('J'.$row , 'v' ) ;

              if  ($stud['cause_other'])	//有第二原因
                $objActSheet->setCellValue('k'.$row , $decrease_cause[$stud['cause_other']] )    ;
              else
	    		$objActSheet->setCellValue('k'.$row ,  $stud['cause_str']  ) ;

        }

	//header('Content-Type: application/vnd.ms-excel');
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename=dec_kind_'.$show_mode.date("mdHi").'.xlsx' );
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;

}
