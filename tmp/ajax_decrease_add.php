<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/


include_once "header.php";

/*-----------function區--------------*/


/*-----------執行動作判斷區----------*/
if  ($_POST['stud'] and $_POST['dollars']     ) {
 
 		$stud_arr = explode("_", $_POST['stud']);
 		$stud_sn = $stud_arr[0] ;
 		$class_sit_num = $stud_arr[1] ;
 		$stud_name = $stud_arr[2] ;
 		
		$sql = " insert into   "  . $xoopsDB->prefix("charge_decrease") .  
	     	   " (`item_id`, `detail_id`, `student_sn`, `curr_class_num`, `decrease_dollar`, `cause`  ) 
	     	   VALUES(   '{$_POST['item_id']}'  ,  '{$_POST['detail_id']}'  ,  '$stud_sn'  ,  '{$_POST['class_id']}'  ,  '{$_POST['dollars']}'  ,  '{$_POST['cause']}'   )
	     	   " ; 
     		$result = $xoopsDB->query($sql) ;
     		if ($result ) {
     		//$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
     		$n_id = $xoopsDB->getInsertId() ;
     		//echo $sql ;

     		echo  "
		<div class='row-fluid' id='div_{$_POST['item_id']}_{$_POST['detail_id']}_$n_id'>
		<div class='span1'>$class_sit_num</div>
		<div class='span2'><span class='del'><i class='icon-trash'></i> $stud_name</div>
		<div class='span2'>{$_POST['dollars']}</div>
		<div class='span2'>{$_POST['cause']}</div>
		<div class='span2'></span>$today</div>
		</div> " ;
	}else {
		echo 'fail' ;
	}

} 
