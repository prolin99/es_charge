<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include_once "header.php";

$_GET['id'] ;
{

	$id_array = preg_split('/_/',$_GET['id'] ) ;
	$did = $id_array[2]  ;
	if ( $did >0 ) {
		$sql  = " SELECT *  FROM  "  . $xoopsDB->prefix("charge_detail") .  "  WHERE  `detail_id`=$did" ;

     		$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
     		$row = $xoopsDB->fetchArray($result)  ;

     		echo "
     		<form method='post' name='editForm' id='editForm_{$row['detail_id']}' action='ajax_detail_submit'  >
     		<div class='span1'>
     			<input class='span11' name='detail_sort' type='text' id='detail_sort' value='{$row['detail_sort']}'  />
     		</div>
     		<div class='span4'>
     			<input class='span11' name='detail' type='text' id='detail' value='{$row['detail']}' />
		</div>

		<div class='span6'>
  			<input class='span11' name='dollars' type='text' id='dollars' value='{$row['dollars']}'  />
		</div>
		<div class='span1'>
			<input name='detail_id' type='hidden' value='{$row['detail_id']}' />
			<span class='ed'>save</span>
		</div>
		</form>
		" ;

     	}else {
     		echo "edit  error " .   $_GET['id']  ;
     	}
}
