<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-02-16
// $Id:$
// ------------------------------------------------------------------------- //
//引入TadTools的函式庫
if(!file_exists(XOOPS_ROOT_PATH."/modules/tadtools/tad_function.php")){
 redirect_header("http://www.tad0616.net/modules/tad_uploader/index.php?of_cat_sn=50",3, _TAD_NEED_TADTOOLS);
}
include_once XOOPS_ROOT_PATH."/modules/tadtools/tad_function.php";

/********************* 自訂函數 *********************/
//取得身份別
//echo $xoopsModuleConfig['es_charge_decrease_cause'] ;
//$decrease_cause = explode( "\n" , $xoopsModuleConfig['es_charge_decrease_cause'] ) ;
$decrease_cause = preg_split('/\r\n/' ,$xoopsModuleConfig['es_charge_decrease_cause']) ;
//$decrease_cause= array("無" , "低收入戶" ,"中低收入戶","家境貧困及家庭突遭變故(導師家訪認定)","原住民","重度以上身心障礙學生或身心障礙人士之子女" ,"中度以下身心障礙學生或身心障礙人士之子女") ;


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
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 			
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
 
		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
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
 
		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
		while($stud=$xoopsDB->fetchArray($result)){
 
			$data[$stud['tn_id'] ]=$stud ;
	
		}		
	return $data ;		
	echo $sql ;
	
}



function get_record_class_list( $item_id ) {
	//取得全校班級列表 
	global  $xoopsDB ;
 
		$sql =  "  SELECT  class_id  FROM " . $xoopsDB->prefix("charge_record") . "  where item_id='$item_id'  group by class_id   " ;
 
		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
		while($row=$xoopsDB->fetchArray($result)){
 
			$data[$row['class_id']]=$row['class_id'] ;
	
		}		
	return $data ;		
	
}

function item_in_time($item_id){
	//是否在期限內
	global  $xoopsDB ;
	$sql =  "  SELECT item_id  FROM " . $xoopsDB->prefix("charge_item") .  " where item_id ='$item_id' and ( start_date<= NOW()   and  end_date >= (NOW() - INTERVAL 1 DAY ) )order by item_id     " ;
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
	
 	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
 	
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
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 

	while($date_list=$xoopsDB->fetchArray($result)){
 	 	$data[]= $date_list ;
	}
 
	return $data ;
}

function get_item_detail_list( $item_id) {
	//取得收費表中的全部細目
 
	global  $xoopsDB ;
	
	$sql =  "  SELECT  *  FROM  " . $xoopsDB->prefix("charge_detail") .  "  where  item_id= '$item_id'  order by detail_id  " ;
 	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	while($date_list=$xoopsDB->fetchArray($result)){
 	 	$data[]= $date_list ;
	}
 
	return $data ;
 
}

function get_item_detail_list_name( $item_id) {
	//取得收費表中的全部細目名稱
 
	global  $xoopsDB ;
	
	$sql =  "  SELECT  detail_id, detail  FROM  " . $xoopsDB->prefix("charge_detail") .  "  where  item_id= '$item_id'  order by detail_id  " ;
 	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	while($date_list=$xoopsDB->fetchArray($result)){
 	 	$data[$date_list['detail_id']]= $date_list['detail'] ;
	}
 
	return $data ;
}

function get_detail_charge_dollars( $item_id) {
	//取得收費表中的全部細目中各年段金額  傳回陣列：[detail_id][year-1]
	global  $xoopsDB ;
	
	$sql =  "  SELECT  detail_id, dollars  FROM  " . $xoopsDB->prefix("charge_detail") .  "  where  item_id= '$item_id'  order by detail_id  " ;
 	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
	while($date_list=$xoopsDB->fetchArray($result)){
		$class_dollar =  explode(",",$date_list['dollars'] ) ;
		  
 	 	$data[$date_list['detail_id']]= $class_dollar ;
	}
 
	return $data ;	
	
}	


//-------------------------------------------------------------------------------------------------------------------
	

function get_class_students_charge( $item_id , $class_id ) {
	//取得該班的要繳費名單
	global  $xoopsDB ,$decrease_cause ;
 		$sql =  "  SELECT  *  FROM " . $xoopsDB->prefix("charge_record") . "   where class_id='$class_id'   and item_id='$item_id'   " ;
 
		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
		while($stud=$xoopsDB->fetchArray($result)){
 			$data[$stud['student_sn']]['selected']='1' ;
 			$data[$stud['student_sn']]['cause'] =$decrease_cause[ $stud['cause'] ] ;
			$data[$stud['student_sn']]['in_bank'] = $stud['in_bank']  ;
	
		}		
	return $data ;		
	
}
	
function get_class_spec_old_item($item_id , $class_id ) {
	//取得該班學生在舊表中有特殊身份的列表
	global  $xoopsDB ,$decrease_cause ;
	$sql =  "  SELECT  c.student_sn , c.cause , s.name , s.class_id , s.class_sit_num  FROM " . $xoopsDB->prefix("charge_record") . " c , " . $xoopsDB->prefix("e_student") .  "  s " .
			"where   c.student_sn = s.tn_id and c.item_id <'$item_id'  and s.class_id='$class_id' and c.cause>0 order by  c.cause " ;
			//echo $sql ;
		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
		while($stud=$xoopsDB->fetchArray($result)){
 			$data .=$stud['class_sit_num'] . $stud['name']  . '--減免身份：' . $decrease_cause[ $stud['cause'] ] ." <br>\n" ;
		}		
	return $data ;				
	
}	

function get_class_pay_students(  $class_id , $item_id  ) {
	//取得該班的要繳費名單
	global  $xoopsDB ,$decrease_cause ;
 		$sql =  "  SELECT  c.*  , s.class_sit_num ,  s.name  FROM " . $xoopsDB->prefix("charge_record") . " c ,  " . $xoopsDB->prefix("e_student") . "  s  " .
 		 	" where  c.student_sn  =s.tn_id  and    c.class_id='$class_id'   and  c.item_id='$item_id'   "   
 		 	;

		$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());	

     	
	//細目
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_detail") .  
     	   	   "  WHERE  `detail_id` = '$detail_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());		
	
}	


function del_item($item_id){
	//刪除 報名項目
	global  $xoopsDB ;
	//減免記錄
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_decrease") .  
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());	
     	
	//學生記錄
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_record") .  
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());	
     	
	//細目
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_detail") .  
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());	
     	
     	//主項
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_item") .  
     	   	   "  WHERE  `item_id` = '$item_id'  " ;
 
	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());		
}	


function clear_item_test_data($item_id){
	//清除各班也寫入的資料
	global  $xoopsDB ;
	//減免記錄
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_decrease") .  
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());	
     	
	//學生記錄
	$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_record") .  
     	   	   "  WHERE  `item_id` = '$item_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());	
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
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());	
     	
		//學生記錄
		$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_record") .  
     	   	   "  WHERE  `item_id` = '$item_id'  and  class_id like '$grade%'  " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());	
	}else {
		//班級
		//減免記錄
		$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_decrease") .  
     	   	   "  WHERE  `item_id` = '$item_id'  and curr_class_num='$class_id'  " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());	
     	
		//學生記錄
		$sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_record") .  
     	   	   "  WHERE  `item_id` = '$item_id'  and  class_id='$class_id'   " ;
     	$result = $xoopsDB->queryF($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());			
		
	}	
    	

}


function get_all_decrease_list_item_array(  $item_id  , $getall= 'all'  ) {
	//取得 某收費單全部細項有減免的資料 -- 以各人為單位
	
	global  $xoopsDB,$decrease_cause ;   
 
 
	$sql =  "  SELECT  c.*  , s.class_sit_num ,s.name,s.sex  , r.cause   FROM " . $xoopsDB->prefix("charge_decrease") .  " c , "   . $xoopsDB->prefix("e_student") .  " s ,  " .    $xoopsDB->prefix("charge_record") .  "  r  " . 
	               " where  s.tn_id =  c.student_sn    and r.student_sn =  c.student_sn   and   r.item_id =c.item_id  and  c.item_id='$item_id'  "  ;
	               
	//僅列出有申請補助               
	if  ($getall == 'only') {	               
		$sql = $sql .  "  and c.cause_chk ='1'    " ;
		//echo $sql ;
	}	
	$sql = $sql .  "  ORDER BY   curr_class_num , class_sit_num ,   detail_id    " ; 
	
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
	while($stud=$xoopsDB->fetchArray($result)){
 			$data[$stud['student_sn']]['dollar'][$stud['detail_id']] =$stud['decrease_dollar'] ;
 			$data[$stud['student_sn']]['cause_chk'][$stud['detail_id']] =$stud['cause_chk'] ;
 			$data[$stud['student_sn']]['other'][$stud['detail_id']] =$stud['cause_other'] ;
 			$data[$stud['student_sn']]['id'][$stud['detail_id']] =$stud['decrease_id'] ;
 			$data[$stud['student_sn']]['curr_class_num'] =$stud['curr_class_num'] ;
 			$data[$stud['student_sn']]['class_sit_num'] =$stud['class_sit_num'] ;
 			$data[$stud['student_sn']]['name'] =$stud['name'] ;
 			$data[$stud['student_sn']]['sex'] =$stud['sex'] ;
 			$data[$stud['student_sn']]['cause'] =$decrease_cause[$stud['cause']] ;
 			$data[$stud['student_sn']]['cause_id'] =$stud['cause'] ;
 			$data[$stud['student_sn']]['modify_time'] = substr($stud['modify_time'],5,5) ;
 
 	}	
 
	return $data ;
}

function get_all_decrease_list_item_kind_array(  $item_id  , $getall= 'all'  ) {
	//取得 某收費單全部細項有減免的資料 -- 依類別
	
	global  $xoopsDB,$decrease_cause ;   
 
 
	$sql =  "  SELECT  * , s.class_sit_num ,s.name ,s.sex , r.cause   FROM " . $xoopsDB->prefix("charge_decrease") .  " c , "   . $xoopsDB->prefix("e_student") .  " s ,  " .    $xoopsDB->prefix("charge_record") .  "  r  " . 
	               " where  s.tn_id =  c.student_sn    and r.student_sn =  c.student_sn   and   r.item_id =c.item_id  and  c.item_id='$item_id'  " ;
	               
	//僅列出有申請補助               
	if  ($getall == 'only') {	               
		$sql = $sql .  "  and c.cause_chk ='1'    " ;
		//echo $sql ;
	}
	
	$sql = $sql . "  ORDER BY  detail_id , curr_class_num , class_sit_num        " ; 
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
	$sql =  "  SELECT  class_id  FROM " . $xoopsDB->prefix("charge_record")  . 
			" where  item_id = '$item_id'     group by   class_id  " ;
 
	
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
	                where a.student_sn= b.tn_id  and  curr_class_num='$class_id'   and  item_id='$item_id' and  detail_id = '$detail_id' 
	                ORDER BY  b.class_sit_num  " ;

	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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

	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
	while($stud=$xoopsDB->fetchArray($result)){
 			$data[]=$stud ;
	}	
	return $data ;
}

function get_decrease_list_item_array($class_id , $item_id   ) {
	//取得班上在某收費單全部細項有減免的資料
	
	global  $xoopsDB ;
 
	$sql =  "  SELECT  * , s.class_sit_num  FROM " . $xoopsDB->prefix("charge_decrease") .  " c , "   . $xoopsDB->prefix("e_student") .  " s " . 
	               " where  s.tn_id =  c.student_sn        and  curr_class_num='$class_id'   and  item_id='$item_id' 
	               ORDER BY class_sit_num ,   detail_id    " ; 
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
	while($stud=$xoopsDB->fetchArray($result)){
 			$data[$stud['student_sn']]['dollar'][$stud['detail_id']] =$stud['decrease_dollar'] ;
 			$data[$stud['student_sn']]['cause_chk'][$stud['detail_id']] =$stud['cause_chk'] ;
 			$data[$stud['student_sn']]['other'][$stud['detail_id']] =$stud['cause_other'] ;
 			$data[$stud['student_sn']]['id'][$stud['detail_id']] =$stud['decrease_id'] ;
 	}	
 
	return $data ;
}


//計算班上每一位同學要付的各項費用
function count_class_stud_pay($class_id , $stud , $stud_sel  , $charge_array , $decase_list ) {
	$y= ($class_id /100) -1 ;
 
 
	//每一要付費的學生(預設值
	foreach ($stud as $stud_key => $student ) {
		$stud_id = $student['tn_id'] ;
 
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
 
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
 
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
	while($data_row=$xoopsDB->fetchArray($result)){
		$pre_item_id =$data_row['item_id'] ;
	}
	if  ($pre_item_id) {
		$sql =  "  SELECT  student_sn  FROM " . $xoopsDB->prefix("charge_record") . 
			" where in_bank= '0' and  class_id='$class_id'  and item_id ='$pre_item_id'      " ;
	
		$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
 
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
 

		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
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
 
	
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
 
	$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
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
 

		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
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

 
?>