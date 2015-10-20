<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include_once "header_admin.php";
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
		each_stud_pay_class($class_id , $item_id) ;
	}


	//輸出郵局文字檔
	export_data($item_id) ;
}


/*-----------函數區----------*/

//取得扣款日期，格式  中華年 YYYMMDD
function get_bank_date_cht($item_id) {
	global   $xoopsDB ,$DEF;
	$sql =  "  SELECT bank_date  FROM " . $xoopsDB->prefix("charge_item") .  " where item_id ='$item_id'     " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error());
	while($date_list=$xoopsDB->fetchArray($result)){
		$bank_date = $date_list['bank_date'] ;
	}

	//中文年月日  YYYMMDD
	$data_arr = split ('[/-]', $bank_date);
	return sprintf("%03d", $data_arr[0]-1911)  .sprintf("%02d", $data_arr[1]) .sprintf("%02d", $data_arr[2])  ;

}

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
	$sql = " SELECT a.student_sn, a.end_pay , a.item_id  , b.* , c.class_id , c.class_sit_num  , count(*) as ccn ,sum(end_pay) as sum_pay    From "
		. $xoopsDB->prefix("charge_record") . " as a , "
		. $xoopsDB->prefix("charge_account") .  " as  b  ,  "
		.  $xoopsDB->prefix("e_student") .  " as  c "
		."  where a.item_id = '$item_id'   and    a.student_sn  = b.stud_sn  and a.student_sn=c.stud_id and a.in_bank=1 "
		."  group by acc_mode, acc_b_id , acc_id , acc_g_id "
		."  ORDER BY class_id, class_sit_num " ;
	//echo $sql ;

	$result = $xoopsDB->queryF($sql)   ;

	$sum_rec=0 ;
	$sum_pay = 0  ;
	while($stud=$xoopsDB->fetchArray($result)){
		$pay = $stud['sum_pay'] + $DEF['fee'] ;

		//學生代碼使用班級+座號 5 碼
		$stud_show_id = sprintf("%03d",$stud['class_id']) . sprintf("%02d",$stud['class_sit_num']) ;

		//合併轉帳(同家長同扣款帳號)
		$do_sum =' ' ;
		if ($stud['ccn']>1)   $do_sum = '1'  ;

		if ($stud['acc_mode'] == 'P' )
			//存戶
			$data .= '1' .$stud['acc_mode'] . $DEF['school_id'] . $poster_block .   $date_pay.  space_chr(3)
				.  sprintf("%07d",$stud['acc_b_id']).sprintf("%07d",$stud['acc_id']).$stud['acc_person_id']
				. sprintf("%09d",$pay).'00'.   sprintf("%03d",$stud['class_id'])     .  sprintf("%03d",$stud['class_sit_num'])
				.$do_sum. space_chr(3)  . $stud_show_id   . '1 ' .  space_chr(3)  .'1' .  space_chr(5)   . $month_pay .  space_chr(5)  ."\n" ;
		else
			//劃撥戶
			$data .= '1' .$stud['acc_mode'] . $DEF['school_id']  .  $poster_block   .   $date_pay.  space_chr(3)
				.  sprintf("%014d",$stud['acc_g_id']) . $stud['acc_person_id']
				. sprintf("%09d",$pay).'00'.  sprintf("%03d",$stud['class_id'])  .  sprintf("%03d",$stud['class_sit_num'])
				.$do_sum . space_chr(3) . $stud_show_id . '1 ' .  space_chr(3)  .'1' .  space_chr(5)  . $month_pay . space_chr(5)  ."\n" ;

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
	header('Content-Disposition: attachment;filename=post001.dat' );
	header('Cache-Control: max-age=0');

	ob_clean();
	echo $data .$total_str;

}

//空白字元 $len 個
function space_chr($len){
	for ($i =0; $i <$len ; $i++ )
		$str  .=' ';
	return $str ;
}


//計算每人要繳的金額放入資料庫
function each_stud_pay_class( $class_id , $item_id) {

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
