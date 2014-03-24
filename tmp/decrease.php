<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/


include_once "header.php";
include_once XOOPS_ROOT_PATH."/header.php";
$xoopsOption['template_main'] = "es_decrease_tpl.html";
/*-----------function區--------------*/


/*-----------執行動作判斷區----------*/

//取得所在班級
$_GET['class_id'] =  get_my_class_id($xoopsUser->uid() ) ;
//取得該班的資料
	if  ( $_GET['class_id'] ) {
		$data['selected']=get_class_students_charge($item_id ,$_GET['class_id'] ) ;
		$data['students']= get_class_students($_GET['class_id'] ) ;
	}	


//取得目前可填收費表
$data['item_list']=get_item_list('action') ;

//細項名稱
$data['detail_list']=get_item_detail_list_name($item_id) ;

$detail_id_array = array_keys($data['detail_list']) ; 
$detail_id=empty($_REQUEST['detail_id'])?$detail_id_array[0]:$_REQUEST['detail_id'];

//取得全部細項的收費
$charge_array= get_detail_charge_dollars( $item_id) ;


//已填的減免資料
$data['decase_list'] = get_decrease_list($_GET['class_id'] , $item_id , $detail_id) ;


$data['seletc_detail']= $detail_id ;
$data['seletc_item'] = $item_id ;
$data['class_id'] = $_GET['class_id'] ;
$y= ($data['class_id'] /100) -1 ;
$data['detail_dollar']= $charge_array[$detail_id][$y];
$data['decease_dollar'] =  $charge_array[$detail_id][$y]  /2  . '  or ' .  $charge_array[$detail_id][$y] ;

/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "bootstrap" , get_bootstrap()) ;
$xoopsTpl->assign( "jquery" , get_jquery(true)) ;
$xoopsTpl->assign( "data" , $data ) ;
 
include_once XOOPS_ROOT_PATH.'/footer.php';

?>