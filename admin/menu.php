<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
$i=0 ;
$adminmenu[$i]['title'] = '首頁';
$adminmenu[$i]['link'] = "admin/index.php";
$adminmenu[$i]['desc'] = '首頁' ;
$adminmenu[$i]['icon'] = 'images/admin/home.png' ;

$i++ ;
$adminmenu[$i]['title'] = '建立收費表';
$adminmenu[$i]['link'] = "admin/main.php";
$adminmenu[$i]['desc'] = '建立收費名稱' ;
$adminmenu[$i]['icon'] = 'images/admin/home.png' ;

$i++ ;
$adminmenu[$i]['title'] =  '減免名單';
$adminmenu[$i]['link'] = "admin/decrease.php";
$adminmenu[$i]['desc'] = '列出減免名單' ;
$adminmenu[$i]['icon'] = 'images/admin/main.png' ;

$i++ ;
$adminmenu[$i]['title'] =  '統計';
$adminmenu[$i]['link'] = "admin/sum.php";
 $adminmenu[$i]['desc'] = '各項統計' ;
$adminmenu[$i]['icon'] = 'images/admin/log_48.png' ;

$i++ ;
$adminmenu[$i]['title'] =  '報表';
$adminmenu[$i]['link'] = "admin/data_output.php";
$adminmenu[$i]['desc'] = '收據、報表' ;
$adminmenu[$i]['icon'] = 'images/admin/genadm.png' ;


$i++ ;
$adminmenu[$i]['title'] =  '郵局扣款作業';
$adminmenu[$i]['link'] = "admin/post_join.php";
$adminmenu[$i]['desc'] = '合併其他部份無單位名冊的學生扣款資料，對帳處理' ;
$adminmenu[$i]['icon'] = 'images/admin/poster2.png' ;

$i++ ;
$adminmenu[$i]['title'] =  '學生帳號管理';
$adminmenu[$i]['link'] = "admin/poster_list.php";
$adminmenu[$i]['desc'] = '學生扣款帳號管理' ;
$adminmenu[$i]['icon'] = 'images/admin/poster.png' ;

$i++ ;
$adminmenu[$i]['title'] =  '代收帳號管理';
$adminmenu[$i]['link'] = "admin/school_account.php";
$adminmenu[$i]['desc'] = '學校代收帳號管理' ;
$adminmenu[$i]['icon'] = 'images/admin/poster_s.png' ;


$i++ ;
$adminmenu[$i]['title'] = "關於";
$adminmenu[$i]['link'] = "admin/about.php";
$adminmenu[$i]['desc'] = '說明';
$adminmenu[$i]['icon'] = 'images/admin/about.png';

?>
