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
if  ($_GET['id']  ) {
 
 		$arr = explode("_", $_GET['id'] );
 
 		$item_id = $arr[1] ;
 		$stud_id = $arr[2] ;

 		//是否在期限內
 		if ( item_in_time($item_id)  or ($isAdmin)  ) { 		
			$sql = " DELETE FROM "  . $xoopsDB->prefix("charge_decrease") .  
			" where item_id= '$item_id' and student_sn='$stud_id'  
			" ; 
			$result = $xoopsDB->queryF($sql) or die($sql."<br>". mysql_error()); 
			//echo $sql  ;
		}	
} 
