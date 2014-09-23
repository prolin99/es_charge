<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin  製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //

include_once "common/tool.php";
include_once "function.php";

//判斷是否對該模組有管理權限
$isAdmin=isAdmin();

//ini_set('display_errors', 'On');

$item_id=empty($_REQUEST['item_id'])?"":intval($_REQUEST['item_id']);
$interface_menu['繳費系統']="index.php?item_id=$item_id";
$interface_menu['減免大表']="decrease_table.php?item_id=$item_id";
$interface_menu['減免名單']="decrease.php?item_id=$item_id";
$interface_menu['收費報表']="report.php?item_id=$item_id";
$interface_menu['細目統計']="sum.php?item_id=$item_id";




if($isAdmin){
  $interface_menu[_TO_ADMIN_PAGE]="admin/index.php";
}


//給獨立模組用的登出按鈕
$interface_menu=logout_button($interface_menu);

//模組前台選單
$module_menu=toolbar($interface_menu);

//引入CSS樣式表檔案
$module_css="<link rel='stylesheet' type='text/css' media='screen' href='module.css' />";


// is_safe_chk()  ;	//檢查是否訪客有權限

?>