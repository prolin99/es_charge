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
 
if  ($item_id) {
 
	//細項名稱
	$data['detail_list']=get_item_detail_list_name($item_id) ;

	$detail_id_array = array_keys($data['detail_list']) ; 

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;
	
	//取得班級
	$class_id_list= get_class_id_list($item_id) ; 
	
	foreach ($class_id_list as $class_id =>$class_id_name) {
	  	//各班級班級已填減免資料
		$data['decase_list'][$class_id_list]= get_decrease_list_item_array( $class_id ,  $item_id ) ;
	}	
	//各班要繳費
	
	//銀行扣款
	
	//自付
 
}
 $data['select_item'] = $item_id  ;


/*-----------秀出結果區--------------*/

$xoopsTpl->assign( "data" , $data ) ;

 
include_once 'footer.php';

?>