<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/


include_once "header.php";

/*-----------function區--------------*/
//data: { item_id: item , class_id :class_id  , stud_id : stud_id , cause: cause },

/*-----------執行動作判斷區----------*/
$item_id = intval($_POST['item_id']) ;
$class_id = intval($_POST['class_id']) ;
$stud_id = intval($_POST['stud_id']) ;

$mode = intval($_POST['mode']) ;

//取得所在班級
$my_class_id =  get_my_class_id($xoopsUser->uid() ) ;
if (($my_class_id<>$class_id ) and  (!$isAdmin) ) 
	die ;
//是否在期限內
if ( item_in_time($item_id) or ($isAdmin)  ) {

	if  ( $stud_id  and  $mode ==0  ) {
		$cause = intval($_POST['cause']) ;

		//更新
		$sql="update "  . $xoopsDB->prefix("charge_record") .   " set cause= '$cause'  where item_id='$item_id'  and student_sn='$stud_id'  ";
		$result = $xoopsDB->queryF($sql) or die($sql."<br>". mysql_error()); 
		//echo $sql ;
		if ($cause ==0 ){
			//去除申請補助的設定
			$sql="update "  . $xoopsDB->prefix("charge_decrease") .   " set cause_chk= 0  where item_id='$item_id'  and student_sn='$stud_id'  ";
			$result = $xoopsDB->queryF($sql) or die($sql."<br>". mysql_error()); 
		}
	}
	if  ( $stud_id  and  $mode ==99  ) {
		$myts =& MyTextSanitizer::getInstance();
		$ps =$myts->htmlspecialchars($myts->addSlashes ($_POST['cause'])  )  ;		
		//更新
		$sql="update "  . $xoopsDB->prefix("charge_record") .   " set ps = '$ps'  where item_id='$item_id'  and student_sn='$stud_id'  ";
		$result = $xoopsDB->queryF($sql) or die($sql."<br>". mysql_error()); 
		
	}
	//echo $sql ;
} 