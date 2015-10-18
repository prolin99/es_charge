<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/

//$xoopsOption['template_main'] = "es_index_tpl.html";
include_once "header.php";
$xoopsOption['template_main'] = set_bootstrap("es_index_tpl.html");
include_once XOOPS_ROOT_PATH."/header.php";

/*-----------function區--------------*/
//取得中文班名
$data['class_list_c'] = es_class_name_list_c('long')  ;



/*-----------執行動作判斷區----------*/

 if (!$xoopsUser)
  	redirect_header(XOOPS_URL,3, "需要登入，才能使用！");

//取得所在班級
$my_class_id =  get_my_class_id($xoopsUser->uid() ) ;

$class_id =$my_class_id ;

 	if (($class_id =='') and  !$isAdmin)
  		redirect_header(XOOPS_URL,3, "非級任，無法使用！");

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
			$students = get_class_students($_POST['class_id']  ,'class') ;


			$batch_value="";
			foreach($_POST['selected_stud'] as $key=>$value){
 				$sn=$value;
 				$stud_name=$students[$sn]['name'] ;
				$batch_value.="('','$sn','{$_POST['item_id']}'  ,'{$_POST['class_id']}' ,'$pay_sum'  ,'$stud_name'    ),";
			}

			$batch_value=substr($batch_value,0,-1);
			if  ($batch_value){
				$sql ="insert  INTO  " . $xoopsDB->prefix("charge_record") . "(record_id,student_sn,item_id ,class_id , dollars , rec_name )  values $batch_value ";
 				$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
 			}
		}

	}

	//開列全學年學生名單-----------------------------------------------------
	if ($_POST['act_grade']) {

		if ($_POST['admin_class_id']  )
		{
			//取得費用小計
			$pay_sum = get_class_need_pay_sum($_POST['item_id'] ,$_POST['class_id']) ;

			//開列過程中要檢查是否已開列過。
			$in_record_student = get_class_students_charge($_POST['item_id'] ,$_POST['class_id'] , 'grade') ;
			//取得全學年學生名單
			$students = get_class_students($_POST['admin_class_id']  ,'grade') ;
			//抓取選擇的班級學生
			$batch_value="";
			foreach($students as $key=>$stud){
				if (!$in_record_student[$key] ){		//是否已在記錄中
 					//$sn=$value;
 					$batch_value.="('','{$stud['stud_id']}','{$_POST['item_id']}'  ,'{$stud['class_id']}' ,'$pay_sum' ,'{$stud['name']}'  ),";
 				}
			}

			$batch_value=substr($batch_value,0,-1);
			if  ($batch_value) {
				$sql ="insert  INTO  " . $xoopsDB->prefix("charge_record") . "(record_id,student_sn,item_id ,class_id , dollars , rec_name )  values $batch_value ";
				$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, mysql_error());
			}
		}

	}

  	//移除全學年繳費名單 ---------------------------------------------------
	if ($_POST['act_grade_remove']) {
		if($_POST['item_id']<>'' AND $_POST['class_id']<>''  ) {
			class_del_item_record($_POST['class_id'] ,$_POST['item_id'] , 'grade') ;
		}
	}

	//檢查是否在期限內
	if  (item_in_time($item_id))  {		//是否在報名時間內
		$data['inTime'] = true ;
	}

	if ($isAdmin)
		$data['inTime'] = true ;

	//刪除多餘的記錄(在設定期間轉出)
	if ( ($_GET['mode']=='del') and $data['inTime']  ) {
		if (($_GET['class_id'] ==$my_class_id)  or  $isAdmin)
			student_del_item_record($_GET['class_id'] , $_GET['item_id'] ,$_GET['sn'] , $_GET['id']  ) ;
	}


//--------------------------------------------------------------------------------------------------------
	//取得目前可填收費表
	$data['item_list']=get_item_list('action') ;






	//管理者可以選取多班
	if($isAdmin){

		$data['admin'] = true ;
		//取得班級
		if ($_POST['admin_class_id'])
			$class_id=$_POST['admin_class_id'] ;
		elseif ( !$class_id)
			$class_id= '101' ;

		//班級名稱
		$data['class_list']=get_class_list() ;
	}

 	//取得該班的資料
	if  ( $class_id ) {
		$data['students']= get_class_students($class_id ) ;
		$data['students_count']=count($data['students']);
	}
	//如果有選擇項目，檢查班上學生是否已加入
	if  ( $item_id ) {
		if  ($data['item_list'][$item_id]) {		//是否在報名時間內
			$data['selected']=get_class_students_charge($item_id ,$class_id  ) ;
			$data['selected_count']= count($data['selected']) ;
		}else
 			$item_id='' ;

	}

//是否有在作業期間轉出要刪除的學生
$data['out_student']= chk_student_out($item_id , $class_id , 'class' ) ;

$data['seletc_item'] = $item_id  ;
$data['class_id'] = $class_id ;

/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "bootstrap" , get_bootstrap()) ;
$xoopsTpl->assign( "jquery" , get_jquery(true)) ;
$xoopsTpl->assign( "data" , $data ) ;

include_once XOOPS_ROOT_PATH.'/footer.php';

?>
