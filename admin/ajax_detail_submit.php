<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include_once "header.php";

if  ($_POST['detail_id'] )  {

		$sql = " UPDATE  "  . $xoopsDB->prefix("charge_detail") .
	     	   "  SET detail_sort = '{$_POST['detail_sort']}'  ,  `detail`='{$_POST['detail']}'  ,   `dollars`='{$_POST['dollars']}' " .
	     	   "  WHERE  `detail_id` = '{$_POST['detail_id']}'  " ;
     		$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());

     		echo '<div class="span1"><span class="badge badge-info">' . $_POST['detail_sort'] .'</span></div>
			<div class="span4"><span class="edit"><i class="icon-pencil"></i></span>' .$_POST['detail'] .'</div>
			<div class="span6"><span class="del"><i class="icon-trash"></i> </span>'. $_POST['dollars'] .'</div> ' ;

}
