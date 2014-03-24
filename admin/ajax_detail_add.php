<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include_once "header_admin.php";
//echo 'aaaa'   . $_POST['item_id']  ;
if  ($_POST['item_id'] )  {
 
 
		$sql = " INSERT INTO   "  . $xoopsDB->prefix("charge_detail") .  
		 	   "	(  `item_id`, `detail_sort`, `detail`, `dollars`) 
		 	   VALUES (  '{$_POST['item_id']}'  ,      '{$_POST['detail_sort']}'  ,   '{$_POST['detail']}'     ,'{$_POST['dollars']}'    )" ;
      		$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
      		
      		$detail_id = $xoopsDB->getInsertId() ;
      		
     		//取得最新筆的記錄 detail_id
     		//$sql = " select  max(detail_id)  as mid  from     "  . $xoopsDB->prefix("charge_detail")   
     		//$result = $xoopsDB->query($sql) or die($sql."<br>". mysql_error()); 
     		
     		echo '<div class="row" id="div_'. $_POST['item_id'] . '_' . $detail_id . '">
     			<div class="span1"><span class="badge badge-info">' . $_POST['detail_sort'] .'</span></div>
			<div class="span4"><span class="edit"><i class="icon-pencil"></i></span>' .$_POST['detail'] .'</div>
			<div class="span6"><span class="del"><i class="icon-trash"></i> </span>'. $_POST['dollars'] .'</div> 
			</div>
			' ;
 
     		
}