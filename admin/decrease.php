<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
include_once "header_admin.php";
//樣版
$xoopsOption['template_main'] = "es_admin_dcrease_tpl.html";
include_once "header.php";


include_once "../function.php";
 
/*-----------function區--------------*/
//取得中文班名
$data['class_list_c'] = es_class_name_list_c('long')  ; 


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

	//$detail_id_array = array_keys($data['detail_list']) ; 

	//取得全部細項的收費
	//$charge_array= get_detail_charge_dollars( $item_id) ;
	
	//全部已填的減免資料
	$data['decase_list'] = get_all_decrease_list_item_array( $item_id ) ;
	
    	//$today = date("m-d") ;
    	$data['newsday'] =date("m-d", mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"))  );
	
 	$data['select_item'] = $item_id  ;
}



/*-----------秀出結果區--------------*/

$xoopsTpl->assign( "data" , $data ) ;

 
include_once 'footer.php';

?>