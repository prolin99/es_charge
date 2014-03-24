<?php
define("TADTOOLS_PATH",XOOPS_ROOT_PATH."/modules/tadtools");
define("TADTOOLS_URL",XOOPS_URL."/modules/tadtools");

//判斷是否為管理員
function isAdmin(){
  global $xoopsUser,$xoopsModule;
  $isAdmin=false;
  if ($xoopsUser) {
    $module_id = $xoopsModule->getVar('mid');
    $isAdmin=$xoopsUser->isAdmin($module_id);
  }
  return $isAdmin;
}

//登出按鈕
function logout_button($interface_menu=array()){
  return $interface_menu;
}

//模組頁尾
function module_footer($main=""){
  global $xoopsTpl,$module_css,$module_menu,$xoopsModuleConfig,$xoopsUser;
  include_once XOOPS_ROOT_PATH."/header.php";
  $xoopsTpl->assign( "css" , $module_css) ;
  $xoopsTpl->assign( "toolbar" , $module_menu) ;
  $xoopsTpl->assign( "content" , $main) ;
  include_once XOOPS_ROOT_PATH.'/include/comment_view.php';
  include_once XOOPS_ROOT_PATH.'/footer.php';
}

//模組頁尾
function module_admin_footer($main="",$n=""){
  xoops_cp_header();
  admin_toolbar($n);
  echo "<link rel='stylesheet' type='text/css' media='screen' href='../module.css' />";
  echo $main;
  xoops_cp_footer();
}
?>