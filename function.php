<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-02-16
// $Id:$
// ------------------------------------------------------------------------- //


//需要單位名稱模組(e_stud_import)1.9
if(!file_exists(XOOPS_ROOT_PATH."/modules/e_stud_import/es_comm_function.php")){
 redirect_header("http://campus-xoops.tn.edu.tw/modules/tad_modules/index.php?module_sn=33",3, '需要單位名稱模組(e_stud_import)1.9以上');
}
include_once XOOPS_ROOT_PATH."/modules/e_stud_import/es_comm_function.php";


/********************* 自訂函數 *********************/
//取得身份別
$decrease_cause = preg_split('/\r\n/' ,$xoopsModuleConfig['es_charge_decrease_cause']) ;
//$decrease_cause= array("無" , "低收入戶" ,"中低收入戶","家境貧困及家庭突遭變故(導師家訪認定)","原住民","重度以上身心障礙學生或身心障礙人士之子女" ,"中度以下身心障礙學生或身心障礙人士之子女") ;
//有郵局扣款帳號
$DEF['bank_account_use'] = $xoopsModuleConfig['es_c_bank_account'] ;
//扣款手續費
$DEF['fee'] = $xoopsModuleConfig['es_c_bank_pay'] ;
//學校帳號 8 碼
$DEF['school_accont'] = $xoopsModuleConfig['es_c_school_accont'] ;
//學校帳號--扣手續費帳號 8 碼
$DEF['school_accont2'] = $xoopsModuleConfig['es_c_school_accont2'] ;
//學校代號 3 碼
$DEF['school_id'] = $xoopsModuleConfig['es_c_school_id'] ;
//郵局區處站所代號 4 碼
$DEF['poster_block'] = $xoopsModuleConfig['es_c_poster_block'] ;

//把文字代號轉換成數字(年段、班級名)
$class2id = preg_split('/,/' ,$xoopsModuleConfig['es_c_other_class2id']) ;
//$DEF['es_c_other_class2id']= $xoopsModuleConfig['es_c_other_class2id'];
 foreach($class2id  as  $lid => $class_str) {
    $c2i = preg_split('/:/' ,$class_str) ;
    if (  is_numeric($c2i[1]  ) ) {
        $temp_class = strtoupper(trim($c2i[0])) ;
        $DEF['class2id'][$temp_class] = $c2i[1] +0 ;
        $DEF['es_c_other_class2id'].= $temp_class . ',' ;
    }
}
//var_dump($DEF['class2id']) ;





/********************* 預設函數 *********************/


//檢查群組權限是否訪客可以查看( true 代表權限設定是 ok，否則提醒)
function is_safe_chk() {
	global  $xoopsDB , $xoopsModule ;
	//取得目前代號
	$mod_id = $xoopsModule->getVar('mid')  ;
	$mod_name = $xoopsModule->getVar('name', 'n')  ;


	//檢查訪客、註冊者有無讀取權限，如果有出現訊息提醒
	$sql =  "  SELECT count(*) as cc  FROM " . $xoopsDB->prefix("group_permission") .
			" where   gperm_itemid =$mod_id and gperm_name='module_read' and   ( gperm_groupid =". XOOPS_GROUP_ANONYMOUS  ."  or   gperm_groupid =" . XOOPS_GROUP_USERS .")  " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($date_list=$xoopsDB->fetchArray($result)){
		$cc = $date_list['cc'] ;
	}
	if ($cc>0) {
		 redirect_header( XOOPS_URL ,3, $mod_name . '模組，使用權限設定不正確，請把訪客、註冊者權限移除');
		return false ;
	}else
		return true ;

}


//=================================================================================================
function get_class_list( ) {
	//取得全校班級列表
	global  $xoopsDB ;

		$sql =  "  SELECT  class_id  FROM " . $xoopsDB->prefix("e_student") . "   group by class_id   " ;

		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
		while($row=$xoopsDB->fetchArray($result)){

			$data[$row['class_id']]=$row['class_id'] ;

		}
	return $data ;

}


function get_class_students( $class_id , $mode='class') {
	//取得該班的學生姓名資料   $mode =class ,grade (全學年) , all (全校)
	global  $xoopsDB ;
	if ($mode =='all')
		$sql =  "  SELECT  *  FROM " . $xoopsDB->prefix("e_student") . "     ORDER BY class_id,  `class_sit_num`  " ;
	elseif ( $mode=='grade') {
		$grade = substr($class_id,0,1) ;
		$sql =  "  SELECT  *  FROM " . $xoopsDB->prefix("e_student") . "  where class_id like '$grade%'   ORDER BY class_id,  `class_sit_num`  " ;
	}else
		$sql =  "  SELECT  *  FROM " . $xoopsDB->prefix("e_student") . "   where class_id='$class_id'   ORDER BY  `class_sit_num`  " ;

		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
		while($stud=$xoopsDB->fetchArray($result)){

			$data[$stud['stud_id'] ]=$stud ;

		}
	return $data ;


}



function get_record_class_list( $item_id ) {
	//取得全校班級列表
	global  $xoopsDB ;

	//取得中文班名
	$class_list_c = es_class_name_list_c('long')  ;

		$sql =  "  SELECT  class_id  FROM " . $xoopsDB->prefix("charge_record") . "  where item_id='$item_id'  group by class_id   " ;

		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
		while($row=$xoopsDB->fetchArray($result)){

			$data[$row['class_id']]=$class_list_c[$row['class_id']] ;

		}
	return $data ;

}

function chk_student_out($item_id , $class_id , $mode= 'class' ) {
	//是否有在作業期間轉出要刪除的學生
	global  $xoopsDB ;
	if ($mode=='class')
		$sql =  "  SELECT  *   FROM " . $xoopsDB->prefix("charge_record") .  " WHERE  item_id ='$item_id'  and class_id ='$class_id'
			and  student_sn NOT  IN (   SELECT stud_id  FROM " . $xoopsDB->prefix("e_student") .  " )  " ;
	else
		$sql =  "  SELECT  *   FROM " . $xoopsDB->prefix("charge_record") .  " WHERE  item_id ='$item_id'
			and  student_sn NOT  IN (   SELECT stud_id  FROM " . $xoopsDB->prefix("e_student") .  " )  " ;
	//echo $sql ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($row=$xoopsDB->fetchArray($result)){
 		$data[]  = $row ;
	}

	return $data ;

}

function item_in_time($item_id){
	//是否在期限內
	global  $xoopsDB ;
	$sql =  "  SELECT item_id  FROM " . $xoopsDB->prefix("charge_item") .  " where item_id ='$item_id' and ( start_date<= NOW()   and  end_date >= (NOW() - INTERVAL 1 DAY ) )order by item_id     " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($date_list=$xoopsDB->fetchArray($result)){
 		$f_item_id = $date_list['item_id'] ;
	}
	if ($f_item_id )
		return true ;
	else
		return false ;
}

function get_item_list($mode='action'){
	//取得收費表 action 可填報、 all 取得最近六項

	global  $xoopsDB ;

	if ($mode=='action')
		//可填報
		$sql =  "  SELECT *  FROM " . $xoopsDB->prefix("charge_item") .  " where ( start_date<= NOW()   and  end_date >= (NOW() - INTERVAL 1 DAY ) )order by item_id     " ;
	else
		$sql =  "  SELECT *  FROM " . $xoopsDB->prefix("charge_item") .  " order by item_id  desc   LIMIT 0 , 6   " ;

 	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());

 	$today = date('Y-m-d') ;
	$data[0]='選擇繳費項目' ;
	while($date_list=$xoopsDB->fetchArray($result)){

		if ($date_list['end_date'] < $today)
			$data[ $date_list['item_id']]= $date_list['item_type'] . '--' .  $date_list['item'] . '--******已過期！';
		else
			$data[ $date_list['item_id']]= $date_list['item_type'] . '--' .  $date_list['item'] . '--' .  $date_list['start_date'] .' ~ ' . $date_list['end_date'] ;
	}
	return $data ;
}

function get_item_all_list(){
	//取得所有收費表

	global  $xoopsDB ;
	$sql =  "  SELECT *  FROM " . $xoopsDB->prefix("charge_item") .  " order by item_id  desc   LIMIT 0 , 6  " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());

	while($date_list=$xoopsDB->fetchArray($result)){
 	 	$data[]= $date_list ;
	}

	return $data ;
}

function get_item_data($item_id ){
	//取得所有收費表的完整資料
	global  $xoopsDB ;
	$sql =  "  SELECT *  , (p_rec_num - c_rec_num)  as  f_rec_num  ,(p_sum-c_sum ) as f_sum  FROM " . $xoopsDB->prefix("charge_item") .  " where  item_id = '$item_id'    " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($date_list=$xoopsDB->fetchArray($result)){
		$data = $date_list ;
	}
	return $data ;
}

function get_item_detail_list( $item_id) {
	//取得收費表中的全部細目

	global  $xoopsDB ;

	$sql =  "  SELECT  *  FROM  " . $xoopsDB->prefix("charge_detail") .  "  where  item_id= '$item_id'  order by detail_id  " ;
 	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($date_list=$xoopsDB->fetchArray($result)){
 	 	$data[]= $date_list ;
	}

	return $data ;

}

function get_item_detail_list_name( $item_id) {
	//取得收費表中的全部細目名稱

	global  $xoopsDB ;

	$sql =  "  SELECT  detail_id, detail  FROM  " . $xoopsDB->prefix("charge_detail") .  "  where  item_id= '$item_id'  order by detail_id  " ;
 	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($date_list=$xoopsDB->fetchArray($result)){
 	 	$data[$date_list['detail_id']]= $date_list['detail'] ;
	}

	return $data ;
}

function check_deny_support($item_list) {

	//檢查是否可以申請補助
	foreach ($item_list as $k => $v ){
		if ((substr(trim($v),-1)  =='X' ) or  (substr(trim($v),-1)  =='x' ) )
			$data[$k] = 1 ;
	}
	return $data ;

}


function get_detail_charge_dollars( $item_id) {
	//取得收費表中的全部細目中各年段金額  傳回陣列：[detail_id][year-1]
	global  $xoopsDB ;

	$sql =  "  SELECT  detail_id, dollars  FROM  " . $xoopsDB->prefix("charge_detail") .  "  where  item_id= '$item_id'  order by detail_id  " ;
 	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($date_list=$xoopsDB->fetchArray($result)){
		$class_dollar =  explode(",",$date_list['dollars'] ) ;

 	 	$data[$date_list['detail_id']]= $class_dollar ;
	}

	return $data ;

}


//-------------------------------------------------------------------------------------------------------------------


function get_class_students_charge( $item_id , $class_id  , $mode='class' ) {
	//取得該班的要繳費名單
	global  $xoopsDB ,$decrease_cause ;
		if ($mode=='class')
 			$sql =  "  SELECT  *  FROM " . $xoopsDB->prefix("charge_record") . "   where class_id='$class_id'   and item_id='$item_id'   " ;
 		else {
 			$grade = substr($class_id,0,1) ;
 			$sql =  "  SELECT  *  FROM " . $xoopsDB->prefix("charge_record") . "   where class_id  like '$grade%'   and item_id='$item_id'  order by  class_id  " ;
 		}
 		//echo $sql ;
		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
		while($stud=$xoopsDB->fetchArray($result)){
 			$data[$stud['student_sn']]['selected']='1' ;
 			$data[$stud['student_sn']]['cause_id'] = $stud['cause']  ;
 			$data[$stud['student_sn']]['cause'] =$decrease_cause[ $stud['cause'] ] ;
			$data[$stud['student_sn']]['in_bank'] = $stud['in_bank']  ;
			$data[$stud['student_sn']]['ps'] = $stud['ps']  ;
			$data[$stud['student_sn']]['rec_name'] = $stud['rec_name']  ;
			$data[$stud['student_sn']]['class_id'] = $stud['class_id']  ;
			$data[$stud['student_sn']]['record_id'] = $stud['record_id']  ;
			$data[$stud['student_sn']]['item_id'] = $stud['item_id']  ;
		}
	return $data ;
}




function get_class_spec_old_item($item_id , $class_id ) {
	//取得該班學生在舊表中有特殊身份的列表
	global  $xoopsDB ,$decrease_cause ;

    //	$sql =  "  SELECT  c.student_sn , c.cause , s.name , s.class_id , s.class_sit_num , c.item_id FROM " . $xoopsDB->prefix("charge_record") . " c , " . $xoopsDB->prefix("e_student") .  "  s " .
    //			"where   c.student_sn = s.stud_id and c.item_id < '$item_id'  and s.class_id='$class_id' and c.cause>0 group by  c.student_sn order by  class_sit_num , c.cause " ;
    $sql =  "  SELECT DISTINCT   c.student_sn , c.cause , s.name , s.class_id , s.class_sit_num , c.item_id FROM " . $xoopsDB->prefix("charge_record") . " c , " . $xoopsDB->prefix("e_student") .  "  s " .
    			"where   c.student_sn = s.stud_id and c.item_id < '$item_id'  and s.class_id='$class_id' and c.cause>0   order by  class_sit_num , c.cause " ;


	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($stud=$xoopsDB->fetchArray($result)){
        //只出現一次
        if (! $list[$stud['student_sn']] )
 		    $data .= $stud['class_sit_num'] . $stud['name']  . ' -- ' . $decrease_cause[ $stud['cause'] ] ." <br />\n" ;
        $list[$stud['student_sn']] = true ;
    }
	return $data ;

}

function get_class_pay_students(  $class_id , $item_id  ) {
	//取得該班的要繳費名單
	global  $xoopsDB ,$decrease_cause ;
 		$sql =  "  SELECT  c.*  , s.class_sit_num ,  s.name  FROM " . $xoopsDB->prefix("charge_record") . " c ,  " . $xoopsDB->prefix("e_student") . "  s  " .
 		 	" where  c.student_sn  =s.stud_id  and    c.class_id='$class_id'   and  c.item_id='$item_id'   "
 		 	;

		$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
		while($stud=$xoopsDB->fetchArray($result)){
 			$data[$stud['student_sn']] = $stud ;

		}
	return $data ;

}


function del_detail($detail_id){
	//刪除細項
	global  $xoopsDB ;
	//減免記錄
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_decrease") .
     	   	   "  WHERE  `detail_id` = '$detail_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());


	//細目
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_detail") .
     	   	   "  WHERE  `detail_id` = '$detail_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

}


function del_item($item_id){
	//刪除 報名項目
	global  $xoopsDB ;
	//減免記錄
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_decrease") .
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

	//學生記錄
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_record") .
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

	//細目
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_detail") .
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

     	//主項
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_item") .
     	   	   "  WHERE  `item_id` = '$item_id'  " ;

	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
}


function clear_item_test_data($item_id){
	//清除各班也寫入的資料
	global  $xoopsDB ;
	//減免記錄
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_decrease") .
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

	//學生記錄
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_record") .
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
}



function class_del_item_record($class_id , $item_id ,$mode='class'){
	//班上刪除 全部填入的資料 $mode =class 班級，grade 全學年
	global  $xoopsDB ;
	if ($mode =='grade') {
		//全學年
		$grade = substr($class_id,0,1) ;
		//減免記錄
		$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_decrease") .
     	   	   "  WHERE  `item_id` = '$item_id'  and curr_class_num like '$grade%'  " ;
     		$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

		//學生記錄
		$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_record") .
     	   	   "  WHERE  `item_id` = '$item_id'  and  class_id like '$grade%'  " ;
     		$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	}else {
		//班級
		//減免記錄
		$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_decrease") .
     	   	   "  WHERE  `item_id` = '$item_id'  and curr_class_num='$class_id'  " ;
     		$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

		//學生記錄
		$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_record") .
     	   	   "  WHERE  `item_id` = '$item_id'  and  class_id='$class_id'   " ;
     		$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

	}


}

function student_del_item_record($class_id , $item_id ,$stud_sn , $record_id  ){
	// 刪除 某一位學生填入的資料
	global  $xoopsDB ;


		//減免記錄
		$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_decrease") .
     	   	   "  WHERE  `item_id` = '$item_id'  and curr_class_num='$class_id'  and student_sn='$stud_sn'  " ;
     		$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

		//學生記錄
		$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_record") .
     	   	   "  WHERE  `item_id` = '$item_id'  and  class_id='$class_id' and student_sn='$stud_sn'   and record_id='$record_id'  " ;

     		$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
}



function get_all_decrease_list_item_array(  $item_id  , $getall= 'all'  ) {
	//取得 某收費單全部細項有減免的資料 -- 以各人為單位

	global  $xoopsDB,$decrease_cause ;


	$sql =  "  SELECT  c.*  , s.class_sit_num ,s.name,s.sex  , r.cause , r.ps   FROM " . $xoopsDB->prefix("charge_decrease") .  " c , "   . $xoopsDB->prefix("e_student") .  " s ,  " .    $xoopsDB->prefix("charge_record") .  "  r  " .
	               " where  s.stud_id =  c.student_sn    and r.student_sn =  c.student_sn   and   r.item_id =c.item_id  and  c.item_id='$item_id'  "  ;

	//僅列出有申請補助
	if  ($getall == 'only') {
		$sql = $sql .  "  and c.cause_chk ='1'    " ;
		//echo $sql ;
	}
	$sql = $sql .  "  ORDER BY   curr_class_num , class_sit_num ,   detail_id    " ;

	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($stud=$xoopsDB->fetchArray($result)){
 			$data[$stud['student_sn']]['dollar'][$stud['detail_id']] =$stud['decrease_dollar'] ;
 			$data[$stud['student_sn']]['cause_chk'][$stud['detail_id']] =$stud['cause_chk'] ;

 			$data[$stud['student_sn']]['other'][$stud['detail_id']] =$stud['cause_other'] ;
 			$data[$stud['student_sn']]['other_cause_str'][$stud['detail_id']] =$decrease_cause[$stud['cause_other']] ;

 			$data[$stud['student_sn']]['id'][$stud['detail_id']] =$stud['decrease_id'] ;
 			$data[$stud['student_sn']]['curr_class_num'] =$stud['curr_class_num'] ;
 			$data[$stud['student_sn']]['class_sit_num'] =$stud['class_sit_num'] ;
 			$data[$stud['student_sn']]['name'] =$stud['name'] ;
 			$data[$stud['student_sn']]['sex'] =$stud['sex'] ;
 			$data[$stud['student_sn']]['cause'] =$decrease_cause[$stud['cause']] ;
 			$data[$stud['student_sn']]['cause_id'] =$stud['cause'] ;
 			$data[$stud['student_sn']]['ps'] =$stud['ps'] ;
 			$data[$stud['student_sn']]['modify_time'] = substr($stud['modify_time'],5,5) ;

 	}

	return $data ;
}

function get_all_decrease_list_item_kind_array(  $item_id  , $getall= 'all'  ) {
	//取得 某收費單全部細項有減免的資料 -- 依類別

	global  $xoopsDB,$decrease_cause ;


	$sql =  "  SELECT  * , s.class_sit_num ,s.name ,s.sex , r.cause   FROM " . $xoopsDB->prefix("charge_decrease") .  " c , "   . $xoopsDB->prefix("e_student") .  " s ,  " .    $xoopsDB->prefix("charge_record") .  "  r  " .
	               " where  s.stud_id =  c.student_sn    and r.student_sn =  c.student_sn   and   r.item_id =c.item_id  and  c.item_id='$item_id'  " ;

	//僅列出有申請補助
	if  ($getall == 'only') {
		$sql = $sql .  "  and c.cause_chk ='1'    " ;
		//echo $sql ;
	}

	$sql = $sql . "  ORDER BY  detail_id , curr_class_num , class_sit_num        " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($stud=$xoopsDB->fetchArray($result)){
		$stud['cause_str']=$decrease_cause[$stud['cause']] ;

 		$data[] =$stud ;
 	}

	return $data ;
}


//-----------------------------
function get_class_id_list( $item_id) {
	//取得班級列表
	global  $xoopsDB ;
	$sql =  "  SELECT  class_id   FROM " . $xoopsDB->prefix("charge_record")  .
			" where  item_id = '$item_id'     group by   class_id  " ;


	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($data_row=$xoopsDB->fetchArray($result)){
				$data[$data_row['class_id'] ] = $data_row['class_id'] ;
	}

	return $data ;

}


function get_class_need_pay_sum($item_id , $class_id) {
	//取得班上應繳的各人(未減免前的小計)
	global  $xoopsDB ;
	$y= ($class_id/100) -1 ;
	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;
	foreach ($charge_array as $detail_id => $dollars) {
		$sum += $dollars[$y] ;
	}
	return $sum ;
	//$data['detail_dollar']= $charge_array[$detail_id][$y];

}

function get_decrease_list($class_id , $item_id ,$detail_id ) {
	//取得班上在某細項上有減免的人名
	global  $xoopsDB ;
	$sql =  "  SELECT  a.* , b.class_sit_num , b.name  FROM " . $xoopsDB->prefix("charge_decrease") . "  a , " . $xoopsDB->prefix("e_student") . "  b
	                where a.student_sn= b.stud_id  and  curr_class_num='$class_id'   and  item_id='$item_id' and  detail_id = '$detail_id'
	                ORDER BY  b.class_sit_num  " ;

	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($stud=$xoopsDB->fetchArray($result)){
 			$data[]=$stud ;
	}
	return $data ;
}



function get_decrease_list_item($class_id , $item_id   ) {
	//取得班上在某收費單全部細項有減免的資料
	global  $xoopsDB ;
	$sql =  "  SELECT  *  FROM " . $xoopsDB->prefix("charge_decrease") .
	               " where curr_class_num='$class_id'   and  item_id='$item_id'
	                ORDER BY  item_id , sit_num , detail_id    " ;

	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($stud=$xoopsDB->fetchArray($result)){
 			$data[]=$stud ;
	}
	return $data ;
}

function get_decrease_list_item_array($class_id , $item_id   ) {
	//取得班上在某收費單全部細項有減免的資料

	global  $xoopsDB ,$decrease_cause ;

	$sql =  "  SELECT  * , s.class_sit_num  FROM " . $xoopsDB->prefix("charge_decrease") .  " c , "   . $xoopsDB->prefix("e_student") .  " s " .
	               " where  s.stud_id =  c.student_sn        and  curr_class_num='$class_id'   and  item_id='$item_id'
	               ORDER BY class_sit_num ,   detail_id    " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($stud=$xoopsDB->fetchArray($result)){
 			$data[$stud['student_sn']]['dollar'][$stud['detail_id']] =$stud['decrease_dollar'] ;
 			$data[$stud['student_sn']]['cause_chk'][$stud['detail_id']] =$stud['cause_chk'] ;

 			$data[$stud['student_sn']]['other'][$stud['detail_id']] =$stud['cause_other'] ;
 			$data[$stud['student_sn']]['other_cause_str'][$stud['detail_id']] =$decrease_cause[$stud['cause_other']] ;

 			$data[$stud['student_sn']]['id'][$stud['detail_id']] =$stud['decrease_id'] ;
 	}

	return $data ;
}


//計算班上每一位同學要付的各項費用
function count_class_stud_pay($class_id , $stud , $stud_sel  , $charge_array , $decase_list ) {
	$y= ($class_id /100) -1 ;


	//每一要付費的學生(預設值
	foreach ($stud as $stud_key => $student ) {
		$stud_id = $student['stud_id'] ;

		//有付費者
		if ( $stud_sel[$stud_id] )  {
			//各細項
			foreach ($charge_array as $detail_id =>$dollar ) {
 				$pay[$stud_id][$detail_id]=  $dollar[$y] ;

			}
		}

	}


	//有減免的部份
	foreach ($decase_list as $d_key =>$d_val) {
		$stud_id = $d_val['student_sn'] ;
		$detail_id = $d_val['detail_id'] ;
		//$decrease_dollar = $d_val['decrease_dollar'] ;
		$pay[$stud_id][$detail_id] -= $d_val['decrease_dollar'] ;
	}


	//再次統計學生陣列加入小計、總計
	foreach ($pay as $stud_id =>$p_val) {
		foreach ($p_val as $detail_id =>$pay_val) {
			$pay[$stud_id]['each'] +=$pay_val ;
		}
	}

	return $pay ;

}

Function get_class_teacher_list() {
	//取得全部級任名冊
	global  $xoopsDB ;
	$sql =  "  SELECT  t.uid, t.class_id , u.name  FROM " . $xoopsDB->prefix("e_classteacher") .'  t  , ' .   $xoopsDB->prefix("users")  .'  u    ' .
	               " where t.uid= u.uid    " ;

	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($data_row=$xoopsDB->fetchArray($result)){
 			$class_id[$data_row['class_id']] = $data_row['name'] ;
	}
	return $class_id  ;
}

function get_my_class_id($uid   ) {
	//取得$uid 的任教班級
	global  $xoopsDB ;
	$sql =  "  SELECT  class_id  FROM " . $xoopsDB->prefix("e_classteacher") .
	               " where uid= '$uid'   " ;

	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($data_row=$xoopsDB->fetchArray($result)){
 			$class_id = $data_row['class_id'] ;
	}
	return $class_id  ;
}



//取得先前自行繳費的名單
function get_class_self_pay($class_id , $item_id ) {

	global  $xoopsDB ;
	//取得上一期

	$sql =  "  SELECT  item_id  FROM " . $xoopsDB->prefix("charge_item") .
			" where  item_id < '$item_id'    order by item_id DESC  LIMIT 0 , 1   " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($data_row=$xoopsDB->fetchArray($result)){
		$pre_item_id =$data_row['item_id'] ;
	}
	if  ($pre_item_id) {
		$sql =  "  SELECT  student_sn  FROM " . $xoopsDB->prefix("charge_record") .
			" where in_bank= '0' and  class_id='$class_id'  and item_id ='$pre_item_id'      " ;

		$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
		while($data_row=$xoopsDB->fetchArray($result)){
				$data[$data_row['student_sn'] ] = $data_row['student_sn'] ;
		}
	}
	return $data ;
}


function get_class_decrease_sum($item_id ,$class_id ) {
	global  $xoopsDB ;
	//取得班級減免資料統計
	$sql =  "  SELECT detail_id , count(*) as man ,  sum(decrease_dollar) as detail_sum   FROM " . $xoopsDB->prefix("charge_decrease") .
	               " where curr_class_num='$class_id'   and  item_id='$item_id'              GROUP BY detail_id   " ;
	               // echo $sql ;

	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($stud=$xoopsDB->fetchArray($result)){
 			$decrease['sum'][$stud['detail_id']]= $stud['detail_sum']  ; 		//各項目減免小計
 			$decrease['man'][$stud['detail_id']]= $stud['man']  ;			//各項目減免人數
 			$decrease['all_sum'] += $stud['detail_sum']  ;					//總減免小計
	}

	return $decrease ;

}

function  get_class_source_pay_sum($item_id ,$class_id  , $my_class_charge_array) {
	global  $xoopsDB ;
	//取得應繳人數、應繳總和 及各項目的應繳小計
 		$sql =  "  SELECT  count(*) as man , sum(dollars) as pay_sum   FROM " . $xoopsDB->prefix("charge_record") .
 		             "   where class_id='$class_id'   and item_id='$item_id'   " ;


		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
		while($stud=$xoopsDB->fetchArray($result)){
			$man = $stud['man']  ;
			$pay_sum = $stud['pay_sum']  ;
		}

		foreach ($my_class_charge_array['pay'] as $detail_id => $dollar) {
			//echo $dollar ;
			$class_sum['detail'][$detail_id]  =$dollar * $man  ;
			$each_pay += $dollar ;
		}
		$class_sum['all']= $pay_sum ;
		$class_sum['man']= $man ;
		$class_sum['each']= $each_pay ;
		return $class_sum ;

}



// ------------------------------------------------------------------------------------------
function get_grade_id_list( $item_id) {
	//取得學年列表
	global  $xoopsDB ;

	$sql =  "  SELECT  SUBSTR( class_id, 1, 1 ) AS y   FROM " . $xoopsDB->prefix("charge_record")  .
			" where  item_id = '$item_id'     GROUP BY y  order by y  " ;


	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($data_row=$xoopsDB->fetchArray($result)){
				$data[$data_row['y'] ] = $data_row['y'] ;
	}

	return $data ;

}


function get_grade_decrease_sum($item_id ,$class_id ) {
	global  $xoopsDB ;
	//取得年級減免資料統計
	$sql =  "  SELECT detail_id , count(*) as man ,  sum(decrease_dollar) as detail_sum   FROM " . $xoopsDB->prefix("charge_decrease") .
	               " where curr_class_num like '$class_id%'   and  item_id='$item_id'              GROUP BY detail_id   " ;
	               // echo $sql ;

	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($stud=$xoopsDB->fetchArray($result)){
 			$decrease['sum'][$stud['detail_id']]= $stud['detail_sum']  ; 		//各項目減免小計
 			$decrease['man'][$stud['detail_id']]= $stud['man']  ;			//各項目減免人數
 			$decrease['all_sum'] += $stud['detail_sum']  ;					//總減免小計
	}

	return $decrease ;

}

function  get_grade_source_pay_sum($item_id ,$class_id  , $my_class_charge_array) {
	global  $xoopsDB ;
	//取得年級 應繳人數、應繳總和 及各項目的應繳小計
 		$sql =  "  SELECT  count(*) as man , sum(dollars) as pay_sum   FROM " . $xoopsDB->prefix("charge_record") .
 		             "   where class_id like '$class_id%'   and item_id='$item_id'   " ;


		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
		while($stud=$xoopsDB->fetchArray($result)){
			$man = $stud['man']  ;
			$pay_sum = $stud['pay_sum']  ;
		}

		foreach ($my_class_charge_array['pay'] as $detail_id => $dollar) {
			//echo $dollar ;
			$class_sum['detail'][$detail_id]  =$dollar * $man  ;
			$each_pay += $dollar ;
		}
		$class_sum['all']= $pay_sum ;
		$class_sum['man']= $man ;
		$class_sum['each']= $each_pay ;
		return $class_sum ;

}



function get_class_no_account($class_id ='all') {
    global  $xoopsDB ;
    //all or 班級無扣款帳號的資料，傳回學生 stud_id
    if ($class_id =='all')
        $sql = " SELECT a.class_id, a.class_sit_num ,a.name, a.stud_id , b.* FROM  ". $xoopsDB->prefix("e_student")
            . "  as a LEFT JOIN " . $xoopsDB->prefix("charge_account")
            . " as b on a.stud_id =b.stud_sn  WHERE acc_person_id IS NULL  "
            . "  order by  a.class_id, a.class_sit_num  "  ;
    else
        $sql = " SELECT a.class_id, a.class_sit_num ,a.name, a.stud_id , b.* FROM  ". $xoopsDB->prefix("e_student")
            . "  as a LEFT JOIN " . $xoopsDB->prefix("charge_account")
            . " as b on a.stud_id =b.stud_sn  WHERE acc_person_id IS NULL  and a.class_id = '$class_id'   "
            . "  order by  a.class_id, a.class_sit_num  "  ;

    $result = $xoopsDB->query($sql)   ;

    while($stud=$xoopsDB->fetchArray($result)){
        $data[$stud['stud_id']] = $stud['stud_id']  ;
    }
    return $data ;
}



//計算每人要繳的金額放入資料庫 班級、付費代號，各細項名，各單項費
function each_stud_pay_class( $class_id , $item_id , $detail_list , $charge_array) {

	global   $xoopsDB  ;

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

//取得需要繳費人數
function get_need_pay_stud_num($item_id) {
	global   $xoopsDB  ;
    $sql = " select count(*)  as num from   " . $xoopsDB->prefix("charge_record") . "    where item_id='$item_id'  "  ;
    $result = $xoopsDB->queryF($sql) ;
    while($stud=$xoopsDB->fetchArray($result)){
        $data = $stud['num']  ;
    }
    return $data ;
}

//取得統計人數、總計 需要在郵局資料中在校生及外部扣款生
function get_poster_stud_num($item_id) {
	global   $xoopsDB  ;
    $sql = " select  stud_else ,cash  , count(*)  as num  , sum(pay) as spay from   " . $xoopsDB->prefix("charge_poster_data") . "    where item_id='$item_id'   group  by stud_else  ,cash "  ;
    //echo $sql ;
    $result = $xoopsDB->queryF($sql) ;
    while($stud=$xoopsDB->fetchArray($result)){

        $data['num'][$stud['stud_else']][$stud['cash']] = $stud['num']  ;
        $data['num'][$stud['stud_else']]['all'] += $stud['num']  ;
        $data['num']['all'] += $stud['num']  ;
        $data['num']['pm'][$stud['cash']] += $stud['num']  ;
        $data['pay'][$stud['stud_else']][$stud['cash']]  = $stud['spay'] ;
        $data['pay'][$stud['stud_else']]['all'] += $stud['spay']  ;
        $data['pay']['pm'][$stud['cash']]  += $stud['spay']  ;
        $data['pay']['all'] += $stud['spay'] ;
        $data['pay_sum'] += $stud['spay'] ;
    }
    return $data ;
}

//取得 合併扣款筆數(同帳號做合併)
function get_poster_chare_num($item_id) {
	global   $xoopsDB  ;
    //合併後要扣款的筆數
    $sql = " SELECT  *  ,count(*) as ccn , sum(pay) as do_pay   From "
  			. $xoopsDB->prefix("charge_poster_data")
  			."  where  item_id='$item_id'  and  cash='0'  "
  			."  group by acc_mode, acc_b_id , acc_id , acc_g_id "
        ."  having  do_pay>0  "
  			."  ORDER BY class_id, sit_num " ;
  	$result = $xoopsDB->queryF($sql)   ;

    $charge_rec =   $xoopsDB->getRowsNum($result) ;

    return $charge_rec ;

}


//取得  扣款失敗學生數
function get_poster_chare_fail($item_id) {
	global   $xoopsDB  ;
    //合併後要扣款的筆數
    $sql = " SELECT  count(*) as stud_num  , sum(pay) as pay_sum    From "
  			. $xoopsDB->prefix("charge_poster_data")
  			."  where  item_id='$item_id' and pay >0  and  pay_fail<>'0'   "  ;
  	$result = $xoopsDB->queryF($sql)   ;

    while($date_list=$xoopsDB->fetchArray($result)){
		$data = $date_list ;
	}
    return $data ;
}

//取得扣款失敗學生名冊，匯出 xlsx
function export_fail($item_id){
    global   $xoopsDB  ;

    include_once "../../tadtools/PHPExcel.php";
    require_once '../../tadtools/PHPExcel/IOFactory.php';

    /*-----------function區--------------*/

    	$objPHPExcel = new PHPExcel();
    	$objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
    	$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
    	$objActSheet->setTitle("郵局帳號清單");  //設定標題

    	$row=1 ;
    	//標題行 //0年級	1班級代號	2座號	3學生姓名	4性別	5學號	6純特戶	7轉帳戶名	8轉帳戶身份證編號	9存款別	10立帳局號	11存簿帳號	12劃撥帳號	13電話號碼	14地址	15身份別
    	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $row, 'num')
            ->setCellValue('B' . $row, '班級')
            ->setCellValue('C' . $row, '座號')
            ->setCellValue('D' . $row, '學生姓名')
    		->setCellValue('E' . $row, '費用')
    		->setCellValue('F' . $row, '轉帳戶名')
    		->setCellValue('G' . $row, '轉帳戶身份證編號')
    		->setCellValue('H' . $row, '存款別')
    		->setCellValue('I' . $row, '立帳局號')
    		->setCellValue('J' . $row, '存簿帳號')
    		->setCellValue('K' . $row, '劃撥帳號')
    		->setCellValue('L' . $row, '外部')
    		->setCellValue('M' . $row, '失敗代碼')  ;


    //合併後要扣款的筆數
    $sql = " SELECT  *    From ". $xoopsDB->prefix("charge_poster_data")
		."  where  item_id='$item_id'  and  pay>0 and  pay_fail<>'0'   order by class_id , sit_num "  ;

	$result = $xoopsDB->queryF($sql)   ;
	while($stud=$xoopsDB->fetchArray($result)){

        $row++ ;
        $objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$row,  ($row-1) )
            ->setCellValue('B'.$row,  $stud['class_id'])
			->setCellValue('C'.$row ,$stud['sit_num'])
			->setCellValue('D'.$row, $stud['st_name'])
            ->setCellValue('E'.$row, $stud['pay'])
			->setCellValue('F'.$row, $stud['acc_name'])
			->setCellValue('G'.$row, $stud['acc_personid'])
			->setCellValue('H'.$row, $stud['acc_mode'])
			->setCellValue('I'.$row, $stud['acc_b_id'])
			->setCellValue('J'.$row, $stud['acc_id'])
			->setCellValue('K'.$row, $stud['acc_g_id'])
			->setCellValue('L'.$row, $stud['stud_else'] )
			->setCellValue('M'.$row,$stud['pay_fail']  ) ;
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$row)->getNumberFormat()->setFormatCode('000') ;
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('I'.$row)->getNumberFormat()->setFormatCode('0000000') ;
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('J'.$row)->getNumberFormat()->setFormatCode('0000000') ;
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('K'.$row)->getNumberFormat()->setFormatCode('00000000000000') ;

	}

    ob_clean();
    //header('Content-Type: application/vnd.ms-excel');
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename=fail'.date("mdHi").'.xlsx' );
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;

}

// ---------------這次繳費 EXCEL
function export_poster_data_excel($item_id){
    global   $xoopsDB  ;

    include_once "../../tadtools/PHPExcel.php";
    require_once '../../tadtools/PHPExcel/IOFactory.php';

    /*-----------function區--------------*/

    	$objPHPExcel = new PHPExcel();
    	$objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
    	$objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
    	$objActSheet->setTitle("郵局帳號清單");  //設定標題

    	$row=1 ;
    	//標題行 //0年級	1班級代號	2座號	3學生姓名	4性別	5學號	6純特戶	7轉帳戶名	8轉帳戶身份證編號	9存款別	10立帳局號	11存簿帳號	12劃撥帳號	13電話號碼	14地址	15身份別
    	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $row, 'num')
            ->setCellValue('B' . $row, '班級')
            ->setCellValue('C' . $row, '座號')
            ->setCellValue('D' . $row, '學生姓名')
    		->setCellValue('E' . $row, '費用')
    		->setCellValue('F' . $row, '轉帳戶名')
    		->setCellValue('G' . $row, '轉帳戶身份證編號')
    		->setCellValue('H' . $row, '存款別')
    		->setCellValue('I' . $row, '立帳局號')
    		->setCellValue('J' . $row, '存簿帳號')
    		->setCellValue('K' . $row, '劃撥帳號')
    		->setCellValue('L' . $row, '外部')
    		->setCellValue('M' . $row, '自行繳費')  ;


    //合併後要扣款的筆數
    $sql = " SELECT  *    From ". $xoopsDB->prefix("charge_poster_data")
		."  where  item_id='$item_id'    order by class_id , sit_num "  ;

	$result = $xoopsDB->queryF($sql)   ;
	while($stud=$xoopsDB->fetchArray($result)){

        $row++ ;
        $objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$row,  ($row-1) )
            ->setCellValue('B'.$row,  $stud['class_id'])
			->setCellValue('C'.$row ,$stud['sit_num'])
			->setCellValue('D'.$row, $stud['st_name'])
            ->setCellValue('E'.$row, $stud['pay'])
			->setCellValue('F'.$row, $stud['acc_name'])
			->setCellValue('G'.$row, $stud['acc_personid'])
			->setCellValue('H'.$row, $stud['acc_mode'])
			->setCellValue('I'.$row, $stud['acc_b_id'])
			->setCellValue('J'.$row, $stud['acc_id'])
			->setCellValue('K'.$row, $stud['acc_g_id'])
			->setCellValue('L'.$row, $stud['stud_else'] )
			->setCellValue('M'.$row, $stud['cash']  ) ;
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$row)->getNumberFormat()->setFormatCode('000') ;
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('I'.$row)->getNumberFormat()->setFormatCode('0000000') ;
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('J'.$row)->getNumberFormat()->setFormatCode('0000000') ;
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('K'.$row)->getNumberFormat()->setFormatCode('00000000000000') ;

	}

    ob_clean();
    //header('Content-Type: application/vnd.ms-excel');
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename=paylist'.date("mdHi").'.xlsx' );
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;

}




// ---------------------------------------   郵局報表  -----------------------------------------------------------------
//空白字元 $len 個
function space_chr($len){
	for ($i =0; $i <$len ; $i++ )
		$str  .=' ';
	return $str ;
}


//取得扣款日期，格式  中華年 YYYMMDD
function get_bank_date_cht($item_id) {
	global   $xoopsDB ,$DEF;
	$sql =  "  SELECT bank_date  FROM " . $xoopsDB->prefix("charge_item") .  " where item_id ='$item_id'     " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
	while($date_list=$xoopsDB->fetchArray($result)){
		$bank_date = $date_list['bank_date'] ;
	}

	//中文年月日  YYYMMDD
	$data_arr = preg_split ('/[\/-]/', $bank_date);

	return sprintf("%03d", $data_arr[0]-1911)  .sprintf("%02d", $data_arr[1]) .sprintf("%02d", $data_arr[2])  ;

}


//郵局格式  (純文字檔)
function export_poster_data($item_id){
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



	$sql = " SELECT  *  ,count(*) as ccn , sum(pay) as do_pay   From "
			. $xoopsDB->prefix("charge_poster_data")
			."  where  item_id='$item_id'  and  cash='0'  "
			."  group by acc_mode, acc_b_id , acc_id , acc_g_id "
            ."  having  do_pay>0  "
			."  ORDER BY class_id, sit_num " ;
	$result = $xoopsDB->queryF($sql)   ;


	$sum_rec=0 ;
	$sum_pay = 0  ;
	while($stud=$xoopsDB->fetchArray($result)){
        if ($stud['do_pay'] <=0)        //無需繳費不用設扣款
            continue;

		$pay = $stud['do_pay'] + $DEF['fee'] ;

		//學生代碼使用 班級3碼 + 座號 2 碼
		$stud_show_id = sprintf("%03d",$stud['class_id']) . sprintf("%02d",$stud['sit_num']) ;

		//合併轉帳(同家長同扣款帳號)
		$do_sum = ' ' ;
		if ($stud['ccn']>1)   $do_sum = '1'  ;

		if (strtoupper($stud['acc_mode']) == 'P' )
			//存戶
			$data .= '1' .$stud['acc_mode'] . $DEF['school_id'] . $poster_block .   $date_pay.  space_chr(3)
				.  sprintf("%07d",$stud['acc_b_id']).sprintf("%07d",$stud['acc_id']).$stud['acc_personid']
				. sprintf("%09d",$pay).'00'.   sprintf("%03d",$stud['class_id'])     .  sprintf("%03d",$stud['sit_num'])
				.$do_sum. space_chr(3)  . $stud_show_id   . '1 ' .  space_chr(3)  .'1' .  space_chr(5)   . $month_pay .  space_chr(5)  ."\r\n" ;
		else
			//劃撥戶
			$data .= '1' .$stud['acc_mode'] . $DEF['school_id']  .  $poster_block   .   $date_pay.  space_chr(3)
				.  sprintf("%014d",$stud['acc_g_id']) . $stud['acc_personid']
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

    //把總筆數及總扣款數寫入
    $sql = " update     ".  $xoopsDB->prefix("charge_item")
            ." SET  `p_rec_num`= '$sum_rec'  ,`p_sum`= '$sum_pay'  "
			."  where  item_id='$item_id'     " ;
	$result = $xoopsDB->queryF($sql)   ;

	header('Content-Type: text/plain');
	header('Content-Disposition: attachment;filename=post001.dat.txt' );
	header('Cache-Control: max-age=0');
    ob_clean();

	echo $data .$total_str;
}




function get_school_account( ) {
	//取得全部代收帳號
	global  $xoopsDB ;
	$sql =  "  SELECT  *  FROM " . $xoopsDB->prefix("charge_bank_account") . "   " ;

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($row=$xoopsDB->fetchArray($result)){
		$data[$row['b_id']]=$row ;
	}
	return $data ;
}
function get_school_account_name( ) {
	//取得全部代收帳號的名稱
	global  $xoopsDB ;
	$sql =  "  SELECT  *  FROM " . $xoopsDB->prefix("charge_bank_account") . "   " ;

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($row=$xoopsDB->fetchArray($result)){
		$data[$row['b_id']]=$row['account_name'] ;
	}
  if (count($data)==0 )
    $data[1]='僅填表，不使用代收扣款' ;

	return $data ;
}


function change_account($item_id){
  global  $xoopsDB , $DEF;
	$sql =  "  SELECT  B.*  FROM " . $xoopsDB->prefix("charge_bank_account") . " B , " . $xoopsDB->prefix("charge_item") . " I  where I.item_id='$item_id' and  B.b_id= I.bank_id ";

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($row=$xoopsDB->fetchArray($result)){
		$DEF['fee'] = $row['account_pay'] ;
    //學校帳號 8 碼
    $DEF['school_accont'] = $row['account1'] ;
    //學校帳號--扣手續費帳號 8 碼
    $DEF['school_accont2'] = $row['account2'] ;
    //學校代號 3 碼
    $DEF['school_id'] = $row['account_id'] ;
    //郵局區處站所代號 4 碼
    $DEF['poster_block'] = $row['account_block_id'] ;

	}
}

function show_poster_paper($item_id){
  global  $xoopsDB  ;
	$sql =  "  SELECT  I.* , B.paper  FROM " . $xoopsDB->prefix("charge_bank_account") . " B , " . $xoopsDB->prefix("charge_item") . " I  where  I.item_id='$item_id' and  B.b_id= I.bank_id     " ;
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($row=$xoopsDB->fetchArray($result)){
    $paper= $row['paper'] ;
    $patterns = array();
    $patterns[0] = '/{{pay_date}}/';
    $patterns[1] = '/{{pay_count}}/';
    $patterns[2] = '/{{pay_money}}/';
    $replacements = array();
    $replacements[2] = $row['bank_date'];
    $replacements[1] = $row['p_rec_num'];
    $replacements[0] = $row['p_sum'];
    echo preg_replace($patterns, $replacements, $paper);

	}
}


function chk_post_list(){
  //檢查學生帳號是否身份証、帳號是相同的
  global  $xoopsDB  ;
  //帳號相同
  $sql =  "  SELECT   count(*) as cc , acc_person_id, acc_mode , acc_b_id ,acc_id , acc_g_id FROM " . $xoopsDB->prefix("charge_account")  . " group by acc_mode, acc_b_id , acc_id , acc_g_id  having cc>1   " ;

  //echo $sql .'<br>';
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($row=$xoopsDB->fetchArray($result)){
    $ind = $row['acc_mode'].' '. $row['acc_b_id'] .' '.$row['acc_id'] .' '.$row['acc_g_id'];
    $first[$ind] = $row['acc_person_id'] ;

  }

  $sql =  "  SELECT   count(*) as cc , acc_person_id, acc_mode , acc_b_id ,acc_id , acc_g_id FROM " . $xoopsDB->prefix("charge_account")  . " group by acc_person_id , acc_mode, acc_b_id , acc_id , acc_g_id  having cc>1 " ;
  //echo $sql .'<br>';
  $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	while($row=$xoopsDB->fetchArray($result)){
    $ind = $row['acc_mode'].' '.$row['acc_b_id'] .' '.$row['acc_id'] .' '.$row['acc_g_id'];
    $second[$ind] = $row['acc_person_id'] ;
  }

  if (count($first) <>count($second) ){
    $err.='帳號和身份証不相符:(請檢查帳號比對檔，搜尋帳號會有多筆，檢查各筆的身份証號是否都相同。)<br/>' ;
    foreach ($first as $k => $v )  {
      //echo $k .' '. $v .'---' . $second[$k] ;
      if ($v <> $second[$k]) {
        //echo $k .' '. $v .'---' . $second[$k] ;
        $err.= '帳號：' .$k .' 身份証號：'. $v  .' <br />' ;
      }
    }
  }
  return $err ;



}
