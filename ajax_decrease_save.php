<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/


include_once "header.php";
include_once "function.php";
/*-----------function區--------------*/


/*-----------執行動作判斷區----------*/
if  ($_POST['stud'] and $_POST['dollars']     ) {
		//取得該學生是否有特殊身份
		$sql =" select cause  FROM "  . $xoopsDB->prefix("charge_record") .  
	     	   	" where item_id=  '{$_POST['item_id']}'   and student_sn='{$_POST['stud']}'      	   " ; 
	     	$result = $xoopsDB->query($sql) ;
	     	while($stud=$xoopsDB->fetchArray($result)){
			$get_cause = $stud['cause'] ;
	     	}	
		
 		//先清除該學生的減免資料
 		$sql = " DELETE FROM "  . $xoopsDB->prefix("charge_decrease") .  
	     	   " where item_id= '$item_id' and student_sn='{$_POST['stud']}'      	   " ; 
 
	     	$result = $xoopsDB->query($sql) ;
	     	   
		foreach ($_POST['dollars']   as $detail_id  =>$d ) {
			
			if ( $get_cause )	//有特殊身份才可補助
				$chk = $_POST['decrease_sel'][$detail_id] ;
			else 
				$chk=0 ;
		 	
		 	//回覆的值
		 	if  ($chk  and $d>0 ) 
 				$item_str .= " <div class='span1' id='dollar_{$_POST['item_id']}_{$_POST['stud']}_$detail_id' data='$d' need='checked'  ><span class='label label-info' title='申請補助'>$d</span></div> " ;
 			else 
        			$item_str .= "<div class='span1' id='dollar_{$_POST['item_id']}_{$_POST['stud']}_$detail_id' data='$d'  need='' >$d</div> " ;
 	 	
		 	if ($d>0) {
 				//增加各減免
		 		$sql = " insert into   "  . $xoopsDB->prefix("charge_decrease") .  
	     	   			" (`item_id`, `detail_id`, `student_sn`, `curr_class_num`, `decrease_dollar` , cause_chk   ) 
	     	   			VALUES(   '{$_POST['item_id']}'  ,  '$detail_id'  ,  '{$_POST['stud']}'  ,  '{$_POST['class_id']}'  ,  '$d'  ,  '$chk'   )
	     	   			" ; 
 		
     				$result = $xoopsDB->query($sql) ;
     				//$n_id = $xoopsDB->getInsertId() ;
			}
		}
 
     		echo  "
     		<div class='span1'  id='sit_{$_POST['item_id']}_{$_POST['stud']}' data='{$_POST['sit_str']}'  ><span class='edit'><i class='icon-pencil'></i></span>{$_POST['sit_str']}</div>
      		<div class='span1' id='name_{$_POST['item_id']}_{$_POST['stud']}' data='{$_POST['name']}' >{$_POST['name']}</div>
      		$item_str
      		<div class='span2' id='cause_{$_POST['item_id']}_{$_POST['stud']}' data='{$_POST['cause_str']}'><span class='del'><i class='icon-trash'></i></span>{$_POST['cause_str']}</div>
 		 " ;
 
} 
 