<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
use XoopsModules\Tadtools\Utility;

//$xoopsOption['template_main'] = "es_sum_tpl.html";
include_once "header.php";
$xoopsOption['template_main'] = "es_sum.tpl";
include_once XOOPS_ROOT_PATH."/header.php";

/*-----------function區--------------*/

 if (!$xoopsUser)
  	redirect_header(XOOPS_URL,3, "需要登入，才能使用！");

/*-----------執行動作判斷區----------*/
//取得中文班名
$data['class_list_c'] = es_class_name_list_c('long')  ;



//取得所在班級
$class_id =get_my_class_id($xoopsUser->uid() ) ;


//取得目前可填收費表
$data['item_list']=get_item_list('all') ;

if  ( $item_id and $class_id) {
	//細項名稱
	$data['detail_list']=get_item_detail_list_name($item_id) ;



	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;

	//檢查是否在期限內
	if  (item_in_time($item_id))  {		//是否在報名時間內
		$data['inTime'] = true ;
	}



	$data['seletc_item'] = $item_id ;
	$data['class_id'] = $class_id  ;

		//取得該班的資料
	if  ( $class_id  and $item_id) {
		//有參加扣款
		$data['selected']=get_class_students_charge($item_id ,$class_id ) ;
		$data['selected_count']= count($data['selected']) ;
		//全班名單
		$data['students']= get_class_students($class_id ) ;
		$data['students_count']=count($data['students']);
	}

	//取得本班級要繳的各項費用
	$y= ($data['class_id'] /100) -1 ;
	foreach ($charge_array as $detail_id => $dollars) {
		$my_class_charge_array['pay'][$detail_id] = $dollars[$y] ;
	}
	$data['detail_dollar']= $my_class_charge_array;


	//是否有在作業期間轉出要刪除的學生
	$data['out_student']= chk_student_out($item_id , $class_id , 'class' ) ;

	//減免人數、金額小計
	$data['class_decrease'] = get_class_decrease_sum($item_id ,$class_id  ) ;
	$data['class_source_pay'] = get_class_source_pay_sum($item_id ,$class_id  , $my_class_charge_array) ;
	foreach ($charge_array as $detail_id =>$v)  {
		$data['class_source_pay']['end_detail'][$detail_id] = $data['class_source_pay']['detail'][$detail_id] - $data['class_decrease']['sum'][$detail_id] ;
	}
	$data['class_source_pay']['end_sum'] = $data['class_source_pay']['all']  - $data['class_decrease']['all_sum']  ;
}

/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , Utility::toolbar_bootstrap($interface_menu)) ;

$xoopsTpl->assign( "data" , $data ) ;



include_once XOOPS_ROOT_PATH.'/footer.php';

?>
