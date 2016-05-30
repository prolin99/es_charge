<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/
include_once "header_admin.php";
//樣版
$xoopsOption['template_main'] = "es_admin_index_tpl.html";
include_once "header.php";


/*-----------function區--------------*/
//


/*-----------執行動作判斷區----------*/
//$op = empty($_REQUEST['op'])? "":$_REQUEST['op'];



//增加一項
if ($_POST['act_save'] )  {
	$creater = $xoopsUser->getVar('name') ;
	$sql = " INSERT INTO " . $xoopsDB->prefix("charge_item") .
		" (`item_id`,  `item_type`, `item`, `start_date`, `end_date`, `bank_date`, `comment`, `creater` ,`p_rec_num` ,`p_sum` ,`c_rec_num` ,`c_sum` )  " .
		"VALUES ('0','{$_POST['item_type']}'     ,'{$_POST['item']}'   , '{$_POST['start_date']}'  , '{$_POST['end_date']}'   ,  '{$_POST['bank_date']}'   , '{$_POST['comment']}'  , '$creater' ,0,0,0,0   )" ;
 	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

 	//快速新增細項
 	if ($_POST['formatted']) {
 		//取得最近所在 item_id
 		$sql = " SELECT MAX( item_id) as mid  FROM " . $xoopsDB->prefix("charge_item")  ;
 		$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
		while($date_list=$xoopsDB->fetchArray($result)){
 	 		$now_id= $date_list['mid'] ;

		}

		$formatted=explode("\r\n",$_POST['formatted']);
		foreach($formatted as $value)	{
				if ($value){
					$value=explode("_",$value);
					$detail_sort=$value[0];
					$detail=$value[1];
					$dollars=$value[2];
					$batch_value.="( '$now_id'  ,'$detail_sort','$detail','$dollars') ,";
				}
			}
			$batch_value=substr($batch_value,0,-1);
			$sql=" INSERT INTO " . $xoopsDB->prefix("charge_detail") . "  (`item_id`, `detail_sort`, `detail`, `dollars` ) values $batch_value ";
			//echo $sql ;
			$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

	}
}

//修改
if ($_POST['act_edit'] )  {
	$sql = " UPDATE  "  . $xoopsDB->prefix("charge_item") .
	     	   "  SET item_type = '{$_POST['item_type']}'  ,  `item`='{$_POST['item']}'  ,   `start_date`='{$_POST['start_date']}' , `end_date` ='{$_POST['end_date']}',   `bank_date` ='{$_POST['bank_date']}', `comment`='{$_POST['comment']}'  " .
	     	   "  WHERE  `item_id` = '{$_POST['item_id']}'  " ;
 	//echo 	$sql ;
	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
	$_GET['do']='edit' ;
	$_GET['item_id'] = $_POST['item_id'] ;

}
//刪除
if ($_POST['act_del'] )  {
	del_item($_POST['item_id']) ;

}

//正式開始，會清除已填報資料
if ($_POST['act_clear'] )  {
	clear_item_test_data($_POST['item_id']) ;
}

//-------------------------------------------------------------------
//編修模式
if ($_GET['do']=='edit' )  {
	$creater = $xoopsUser->getVar('name') ;
  	$p_data['edit_fg'] = true ;
	$sql =  "  SELECT *  FROM " . $xoopsDB->prefix("charge_item") .  " where item_id = '{$_GET['item_id']}'  and  creater='$creater'  " ;

	$result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

	while($date_list=$xoopsDB->fetchArray($result)){
 	 	$p_data['edit_list']= $date_list ;
	}
	//取得細項
	$p_data['item_list'] = get_item_detail_list($_GET['item_id']) ;
}






if ($_POST['btn_add'] )
  $p_data['add_fg'] = true ;


//取得所有收費表
 	$p_data['list'] =get_item_all_list() ;
 	$p_data['today']= date("Y-m-d" ) ;

 //取出常用細項
$p_data['detail_def']= $xoopsModuleConfig['es_charge_default_detail'] ;


/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "p_data" , $p_data ) ;


include_once 'footer.php';
?>
