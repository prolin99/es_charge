<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
if (!$DEF['bank_account_use']) {
	echo '未使用郵局扣款！' ;
	exit() ;
}
/*-----------引入檔案區--------------*/
include_once "header_admin.php";
include_once "../function.php";

/*-----------執行動作判斷區----------*/
$item_id=empty($_REQUEST['item_id'])?"":$_REQUEST['item_id'];


if  ($item_id) {

	//細項名稱
	$detail_list=get_item_detail_list_name($item_id) ;

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;
	//有繳費的各班級
	$class_list = get_class_id_list($item_id) ;

	foreach ($class_list as $class_id=> $class) {
		//分別輸出各班 部份
		class_output($class_id , $item_id) ;
	}

	export_data($item_id) ;
}


/*-----------函數區----------*/


function export_data($item_id){
	global   $xoopsDB ,$DEF;


	$sql = " SELECT a.student_sn, a.end_pay , a.item_id  , b.* , c.class_id , c.class_sit_num  , count(*) as ccn ,sum(end_pay) as sum_pay    From "  . $xoopsDB->prefix("charge_record") . " as a , "
		. $xoopsDB->prefix("charge_account") .  " as  b  ,  " .  $xoopsDB->prefix("e_student") .  " as  c "
		."  where a.item_id = '$item_id'   and    a.student_sn  = b.stud_sn  and a.student_sn=c.stud_id  "
		."  group by acc_mode, acc_b_id , acc_id , acc_g_id "
		."  ORDER BY class_id, class_sit_num " ;
	//echo $sql ;

	$result = $xoopsDB->queryF($sql)   ;

	$date_pay = '1041108' ;
	$month_pay = substr($date_pay,0,5) ;
	while($stud=$xoopsDB->fetchArray($result)){
		$pay = $stud['sum_pay'] + $DEF['fee'] ;

		//合併轉帳
		$do_sum =' ' ;
		if ($stud['ccn']>1)   $do_sum = '1'  ;

		if ($stud['acc_mode'] == 'P' )
			$data .= '1' .$stud['acc_mode'] . $DEF['school_id'] .'    '.$date_pay. '   '
				.  sprintf("%07d",$stud['acc_b_id']).sprintf("%07d",$stud['acc_id']).$stud['acc_person_id']
				. sprintf("%09d",$pay).'00'.$stud['class_id'].  sprintf("%03d",$stud['class_sit_num'])
				.$do_sum. '   ' . substr($stud['student_sn'],1,5) . '1 ' .  '   '  .'1' .'     ' . $month_pay .'     ' ."\n" ;
		else
			$data .= '1' .$stud['acc_mode'] . $DEF['school_id'] .'    '.$date_pay. '   '
				.  sprintf("%014d",$stud['acc_g_id']) . $stud['acc_person_id']
				. sprintf("%09d",$pay).'00'.$stud['class_id'].  sprintf("%03d",$stud['class_sit_num'])
				.$do_sum . '    ' . substr($stud['student_sn'],1,5) . '1 ' .  '   '  .'1' .'     ' . $month_pay .'     ' ."\n" ;
	}


	header('Content-Type: text/plain');
	header('Content-Disposition: attachment;filename=post001.dat' );
	header('Cache-Control: max-age=0');

	ob_clean();
	echo $data ;

}




//計算放入資料庫
function class_output( $class_id , $item_id) {

	global   $xoopsDB, $detail_list , $charge_array ;

	//取得班上要繳費的人員資料
	$class_students= get_class_pay_students($class_id  , $item_id) ;

	//取得班上 有減免的資料
	$class_decase_list = get_decrease_list_item_array($class_id , $item_id) ;


    //資料區
    foreach ( $class_students  as $stud_id => $stud )  {
    	$y = ($class_id /100)-1 ;
		$stud_pay=0 ;  //學生小計
		foreach   (  $detail_list   as $detail_id => $detail ) {
			$s_pay =$charge_array[$detail_id][$y] ;
			//實付
			$pay = $charge_array[$detail_id][$y] -$class_decase_list[$stud_id]['dollar'][ $detail_id] ;
			$stud_pay += $pay ;		//總額
		}

		//寫入紀錄：
		$sql = " UPDATE  " . $xoopsDB->prefix("charge_record") . "   SET  end_pay = '$stud_pay' where item_id='$item_id' and  student_sn=	'$stud_id'  ;  " ;

		$result = $xoopsDB->queryF($sql) ;
	}
}

/*
//結合帳號資料

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

*/
/*
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename=account'.date("mdHi").'.xlsx' );
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;
*/
