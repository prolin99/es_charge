<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/


include_once "header.php";

include_once XOOPS_ROOT_PATH."/header.php";
$xoopsOption['template_main'] = "es_index_tpl.html";
/*-----------function區--------------*/


 
 
/*-----------執行動作判斷區----------*/
//$op=empty($_REQUEST['op'])?"":$_REQUEST['op'];
 
//取得所在班級
$my_class_id =  get_my_class_id($xoopsUser->uid() ) ;
 
  	$class_id =$my_class_id ;

  	//移除本班繳費名單 ---------------------------------------------------
	if ($_POST['act_remove']) {
		
		//class_del_item($_POST['class_id'] ,$_POST['item_id'] ) ;
		if($_POST['item_id']<>'' AND $_POST['class_id']<>''  ) {
			class_del_item_record($_POST['class_id'] ,$_POST['item_id'] ) ;
  
		}
	}
	
	//開列學生名單-----------------------------------------------------
	if ($_POST['act_add']) {
	
		if ($_POST['item_id']<>'' AND $_POST['class_id']<>''  and  $_POST['selected_stud']  )
		{
			//取得費用小計
			$pay_sum = get_class_need_pay_sum($_POST['item_id'] ,$_POST['class_id']) ;
			
			//抓取選擇的班級學生
			$batch_value="";
			foreach($_POST['selected_stud'] as $key=>$value){
 				$sn=$value;
				$batch_value.="('','$sn','{$_POST['item_id']}'  ,'{$_POST['class_id']}' ,'$pay_sum'   ),";
			}
			
			$batch_value=substr($batch_value,0,-1);
			$sql ="insert  INTO  " . $xoopsDB->prefix("charge_record") . "(record_id,student_sn,item_id ,class_id , dollars )  values $batch_value ";

			$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
		}
 
	}
	
//--------------------------------------------------------------------------------------------------------	
	//取得目前可填收費表
	$data['item_list']=get_item_list('action') ;

	//如果有選擇項目，檢查班上學生是否已加入
	if  ( $item_id ) {	
		if  ($data['item_list'][$item_id]) 		//是否在報名時間內
			$data['selected']=get_class_students_charge($item_id ,$class_id ) ;
		else 
 			$item_id='' ;
		
	}	

//取得該班的資料
	if  ( $class_id ) {
		$data['students']= get_class_students($class_id ) ;
	}		
 

      	
 

	
 $data['seletc_item'] = $item_id  ;
 $data['class_id'] = $class_id ;

/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "bootstrap" , get_bootstrap()) ;
$xoopsTpl->assign( "jquery" , get_jquery(true)) ;
$xoopsTpl->assign( "data" , $data ) ;
 
include_once XOOPS_ROOT_PATH.'/footer.php';

?>