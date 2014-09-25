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
//data: { item_id: item , class_id :class_id  , detail_id:detail_id , stud_id : stud_id , sit_num:sit_num , money:money , cause: cause ,need:need , other:other },
$item_id = intval($_GET['item_id']) ;
$class_id = intval($_GET['class_id']) ;
$stud_id = intval($_GET['stud_id']) ;
$detail_id = intval($_GET['detail_id']) ;
$sit_num = intval($_GET['sit_num']) ;
$money = intval($_GET['money']) ;
$cause = intval($_GET['cause']) ;
$need = intval($_GET['need']) ;
$other = intval($_GET['other']) ;

if ($cause==0){
	$need=0 ;
	$other=0 ;
}

if ($other==$cause)
	$other=0 ;

if ($need==0 )
	$other=0 ;



/*-----------執行動作判斷區----------*/
//取得所在班級
$my_class_id =  get_my_class_id($xoopsUser->uid() ) ;
if (($my_class_id<>$class_id ) and  (!$isAdmin) ) 
	die ;
//是否在期限內
if ( item_in_time($item_id) or ($isAdmin)  ) {
	if  ($stud_id  and $item_id  and $detail_id  ) {
		if ($money >0 ){
			 $sql = " REPLACE  INTO   " . $xoopsDB->prefix("charge_decrease") . "  set   detail_id='$detail_id' , student_sn='$stud_id' ,curr_class_num ='$class_id' ,
		  		decrease_dollar='$money' , cause_chk ='$need'  , item_id='$item_id' , sit_num='$sit_num'  , cause_other='$other' " ;
		 	$result = $xoopsDB->queryF($sql) or die($sql."<br>". mysql_error()); 
		}else {
			$sql =  "  delete   FROM " . $xoopsDB->prefix("charge_decrease") . "   where detail_id='$detail_id' and student_sn='$stud_id' and  curr_class_num ='$class_id'   " ;
			$result = $xoopsDB->queryF($sql) or die($sql."<br>". mysql_error()); 
		}
		//echo $sql ;
	}  
}