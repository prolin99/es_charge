<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include_once "header.php";
include_once "../function.php";

/*-----------執行動作判斷區----------*/

if (!$DEF['bank_account_use']) {
	echo '未使用郵局扣款！' ;
	exit() ;
}

$item_id=empty($_REQUEST['item_id'])?"":$_REQUEST['item_id'];

if  ($item_id) {

	//細項名稱
	$detail_list=get_item_detail_list_name($item_id) ;

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;
	//有繳費的各班級
	$class_list = get_class_id_list($item_id) ;

	foreach ($class_list as $class_id=> $class) {
		//分別以各班計算每人要繳費，寫在資料庫 end_pay
		each_stud_pay_class($class_id , $item_id ,  $detail_list ,  $charge_array) ;
	}


	//輸出郵局文字檔
	export_data($item_id) ;
}


/*-----------函數區----------*/



//郵局格式
function export_data($item_id){
	global   $xoopsDB ,$DEF;

	//取得扣款日 YYYMMDD
	$date_pay = get_bank_date_cht($item_id) ;
	//扣款年月  YYYMM
	$month_pay = substr($date_pay,0,5) ;
	//區處站所代號 4 碼
	if ($DEF['poster_block'] )
		$poster_block= $DEF['poster_block']  ;
	else
		$poster_block = space_chr(4) ;


	//各人扣款 ，要有帳號及允許扣款
	//如果在  舊表中 charge_record sit_num 大小

	$sql = "  SELECT MAX(sit_num) as m  FROM  " . $xoopsDB->prefix("charge_record")
		. "  where item_id = '$item_id'    " ;
 	$result = $xoopsDB->queryF($sql)   ;
	while($row=$xoopsDB->fetchArray($result)){
		$mx_sit = $row['m'] ;
	}

	if ($mx_sit == 0 )
		$sql = " SELECT a.student_sn, a.end_pay , a.item_id  , b.* , c.class_id , c.class_sit_num as sit_num  , count(*) as ccn ,sum(end_pay) as sum_pay    From "
			. $xoopsDB->prefix("charge_record") . " as a "
			." inner JOIN  "
			. $xoopsDB->prefix("charge_account") .  " as  b  "
			."  on  a.student_sn  = b.stud_sn  "
			." LEFT JOIN   "
			.  $xoopsDB->prefix("e_student") .  " as  c   "
			."  on a.student_sn=c.stud_id  "
			."  where a.item_id = '$item_id'    and a.in_bank=1 "
			."  group by acc_mode, acc_b_id , acc_id , acc_g_id "
			."  ORDER BY class_id, sit_num " ;
	else
		$sql = " SELECT a.student_sn, a.end_pay , a.item_id , a.class_id, a.sit_num  , b.* ,  count(*) as ccn ,sum(end_pay) as sum_pay    From "
			. $xoopsDB->prefix("charge_record") . " as a , "
			. $xoopsDB->prefix("charge_account") .  " as  b  "
			."  where  a.student_sn  = b.stud_sn and  a.item_id = '$item_id'    and a.in_bank=1 "
			."  group by acc_mode, acc_b_id , acc_id , acc_g_id "
			."  ORDER BY class_id, sit_num " ;


	$result = $xoopsDB->queryF($sql)   ;

	$sum_rec=0 ;
	$sum_pay = 0  ;
	while($stud=$xoopsDB->fetchArray($result)){
		if ($stud['sum_pay'] <=0)        //無需繳費不用設扣款
            continue;

		$pay = $stud['sum_pay'] + $DEF['fee'] ;

		//學生代碼使用  account 中序號 a_id
		//$stud_show_id = sprintf("%03d",$stud['class_id']) . sprintf("%02d",$stud['sit_num']) ;
		$stud_show_id =  sprintf("%05d",$stud['a_id'])   ;

		//合併轉帳(同家長同扣款帳號)
		$do_sum =' ' ;
		if ($stud['ccn']>1)   $do_sum = '1'  ;

		if ($stud['acc_mode'] == 'P' )
			//存戶
			$data .= '1' .$stud['acc_mode'] . $DEF['school_id'] . $poster_block .   $date_pay.  space_chr(3)
				.  sprintf("%07d",$stud['acc_b_id']).sprintf("%07d",$stud['acc_id']).$stud['acc_person_id']
				. sprintf("%09d",$pay).'00'.   sprintf("%03d",$stud['class_id'])     .  sprintf("%03d",$stud['sit_num'])
				.$do_sum. space_chr(3)  . $stud_show_id   . '1 ' .  space_chr(3)  .'1' .  space_chr(5)   . $month_pay .  space_chr(5)  ."\r\n" ;
		else
			//劃撥戶
			$data .= '1' .$stud['acc_mode'] . $DEF['school_id']  .  $poster_block   .   $date_pay.  space_chr(3)
				.  sprintf("%014d",$stud['acc_g_id']) . $stud['acc_person_id']
				. sprintf("%09d",$pay).'00'.  sprintf("%03d",$stud['class_id'])  .  sprintf("%03d",$stud['sit_num'])
				.$do_sum . space_chr(3) . $stud_show_id . '1 ' .  space_chr(3)  .'1' .  space_chr(5)  . $month_pay . space_chr(5)  ."\r\n" ;

		//筆數、總金額
		$sum_rec++ ;
		$sum_pay +=  $pay ;
	}
	//最後總合
	$total_str = '2 ' . $DEF['school_id'] .  $poster_block .  $date_pay  . '000'
		. sprintf("%07d" , $sum_rec) .  sprintf("%011d",$sum_pay).'00'
		. sprintf("%08d",$DEF['school_accont']).  sprintf("%08d",$DEF['school_accont2'])
		.  sprintf("%020d",0)
		. space_chr(15);


	header('Content-Type: text/plain');
	header('Content-Disposition: attachment;filename=post001.dat.txt' );
	header('Cache-Control: max-age=0');

	ob_clean();
	echo $data .$total_str;

}
