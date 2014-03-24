<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/


include_once "header.php";
include_once XOOPS_ROOT_PATH."/header.php";
$xoopsOption['template_main'] = "es_report_tpl.html";
/*-----------function區--------------*/


/*-----------執行動作判斷區----------*/

//取得所在班級
$class_id =get_my_class_id($xoopsUser->uid() ) ;

//取得該班的資料
	if  ( $class_id ) {
		//有參加扣款
		$data['selected']=get_class_students_charge($item_id ,$class_id ) ;
		//全班名單
		$data['students']= get_class_students($class_id ) ;
	}	


//取得目前可填收費表
$data['item_list']=get_item_list('all') ;

//細項名稱
$data['detail_list']=get_item_detail_list_name($item_id) ;

//$detail_id_array = array_keys($data['detail_list']) ; 

//檢查是否在期限內
if  (item_in_time($item_id))  {		//是否在報名時間內	
	$data['inTime'] = true ;
}

//取得全部細項的收費
$charge_array= get_detail_charge_dollars( $item_id) ;


//本班已填的全班各細項的減免資料
$data['decase_list'] = get_decrease_list_item($class_id , $item_id ) ;
$data['decase_list_array']=get_decrease_list_item_array($class_id , $item_id ) ;

//計算各人要繳的費用 
$data['pay_list'] = count_class_stud_pay($class_id , $data['students'] , $data['selected'] , $charge_array , $data['decase_list']) ;


//取得先前自行繳費的名單
$data['self_pay']= get_class_self_pay($class_id , $item_id ) ;
 

//$data['seletc_detail']= $detail_id ;
$data['seletc_item'] = $item_id ;
$data['class_id'] = $class_id  ;
$y= ($data['class_id'] /100) -1 ;
$data['detail_dollar']= $charge_array[$detail_id][$y];
//$data['decease_dollar'] =  $charge_array[$detail_id][$y]  /2  . '  or ' .  $charge_array[$detail_id][$y] ;


/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "bootstrap" , get_bootstrap()) ;
$xoopsTpl->assign( "jquery" , get_jquery(true)) ;
$xoopsTpl->assign( "data" , $data ) ;
//$xoopsTpl->display("es_report_tpl.html") ;
include_once XOOPS_ROOT_PATH.'/footer.php';
//include_once 'footer.php';
?>