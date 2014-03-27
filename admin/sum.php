<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
include_once "header_admin.php";
//樣版
$xoopsOption['template_main'] = "es_admin_sum_class_tpl.html";
include_once "header.php";


include_once "../function.php";
 
/*-----------function區--------------*/


/*-----------執行動作判斷區----------*/
$item_id=empty($_REQUEST['item_id'])?"":$_REQUEST['item_id'];
 



/*----------取得資料區--------------*/
 

//取得目前可填收費表
$data['item_list']=get_item_list('all') ;
 

 if  (!$item_id) {
	//選定最近的工作表
	$key = array_keys($data['item_list'])  ;
	$item_id=$key[1] ;
 }	

 $data['select_item'] = $item_id  ;


/*-----------秀出結果區--------------*/

$xoopsTpl->assign( "data" , $data ) ;

 
include_once 'footer.php';

?>