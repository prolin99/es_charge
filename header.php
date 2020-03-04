<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin  製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //

//載入XOOPS主設定檔（必要）
include_once "../../mainfile.php";
//載入自訂的共同函數檔
include_once "function.php";

//判斷是否對該模組有管理權限
$isAdmin=false;
if ($xoopsUser) {
  $module_id = $xoopsModule->getVar('mid');
  $isAdmin=$xoopsUser->isAdmin($module_id);
}

//回模組首頁
$interface_menu[_TAD_TO_MOD]="index.php";
$interface_icon[_TAD_TO_MOD]="fa-chevron-right";

$item_id=empty($_REQUEST['item_id'])?"":intval($_REQUEST['item_id']);

$interface_menu['總表減免']="decrease_table.php?item_id=$item_id";
$interface_menu['單人減免']="decrease.php?item_id=$item_id";
$interface_menu['報表']="report.php?item_id=$item_id";
$interface_menu['統計']="sum.php?item_id=$item_id";
$interface_menu['社團報名轉檔']="kw_club_join.php";




//模組後台
if($isAdmin){
  $interface_menu[_TAD_TO_ADMIN]="admin/index.php";
  $interface_icon[_TAD_TO_ADMIN]="fa-chevron-right";
}


?>
