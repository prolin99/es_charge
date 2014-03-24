<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/


include_once "header.php";
include_once XOOPS_ROOT_PATH."/header.php";
$xoopsOption['template_main'] = "es_sum_tpl.html";
/*-----------function區--------------*/


/*-----------執行動作判斷區----------*/

//取得所在班級
$class_id =get_my_class_id($xoopsUser->uid() ) ;

//取得該班的資料
/*
	if  ( $class_id ) {
		//有參加扣款
		$data['selected']=get_class_students_charge($item_id ,$class_id ) ;
		//全班名單
		$data['students']= get_class_students($class_id ) ;
	}	
*/

//取得目前可填收費表
$data['item_list']=get_item_list('all') ;

//細項名稱
$data['detail_list']=get_item_detail_list_name($item_id) ;

//$detail_id_array = array_keys($data['detail_list']) ; 



//取得全部細項的收費
$charge_array= get_detail_charge_dollars( $item_id) ;


//本班已填的全班各細項的減免資料
//$data['decase_list'] = get_decrease_list_item($class_id , $item_id ) ;
//$data['decase_list_array']=get_decrease_list_item_array($class_id , $item_id ) ;

//計算各人要繳的費用 
//$data['pay_list'] = count_class_stud_pay($class_id , $data['students'] , $data['selected'] , $charge_array , $data['decase_list']) ;


//$data['seletc_detail']= $detail_id ;
$data['seletc_item'] = $item_id ;
$data['class_id'] = $class_id  ;



//取得本班級要繳的各項費用
$y= ($data['class_id'] /100) -1 ;
foreach ($charge_array as $detail_id => $dollars) {
	$my_class_charge_array['pay'][$detail_id] = $dollars[$y] ;
	$my_class_charge_array['decease'][$detail_id] = '一半:' .  $dollars[$y] /2  . '  or 全額:' .  $dollars[$y] ;
}
$data['detail_dollar']= $my_class_charge_array;



//班級統計
//$data['class_sum'] = get_class_sum(($item_id ,$class_id ) ;
//減免人數、金額小計
$data['class_decrease'] = get_class_decrease_sum($item_id ,$class_id  ) ;
$data['class_source_pay'] = get_class_source_pay_sum($item_id ,$class_id  , $my_class_charge_array) ;
foreach ($charge_array as $detail_id =>$v)  {
	//echo $detail_id  .'--' . $data['class_source_pay']['detail'][$detail_id] .'--' .  $data['class_decrease']['sum'][$detail_id]   . '<br>'  ;
	$data['class_source_pay']['end_detail'][$detail_id] = $data['class_source_pay']['detail'][$detail_id] - $data['class_decrease']['sum'][$detail_id] ;
}
$data['class_source_pay']['end_sum'] = $data['class_source_pay']['all']  - $data['class_decrease']['all_sum']  ;

/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "bootstrap" , get_bootstrap()) ;
$xoopsTpl->assign( "jquery" , get_jquery(true)) ;
$xoopsTpl->assign( "data" , $data ) ;
 
include_once XOOPS_ROOT_PATH.'/footer.php';

?>