<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include_once "header.php";
include_once "../function.php";

$_GET['id'] ;
{
	$id_array = preg_split('/_/',$_GET['id'] ) ;
	$did = $id_array[2]  ;
	if ( $did >0 ) {
		del_detail($did) ;
		/*
		$sql  = " DELETE FROM  "  . $xoopsDB->prefix("charge_detail") .  "  WHERE  `detail_id`=$did" ;

     		$result = $xoopsDB->queryF($sql) or die($sql."<br>". $xoopsDB->error());
     		*/


     		echo "delete $did   " ;


     	}else {
     		echo "delete error " .   $_GET['id']  ;
     	}
}
