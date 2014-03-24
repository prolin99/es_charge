<?php
session_start();
include_once "mainfile.php";

include_once XOOPS_ROOT_PATH."/language/{$xoopsConfig['language']}/modinfo.php";
include_once XOOPS_ROOT_PATH."/xoops_version.php";
include_once XOOPS_ROOT_PATH."/language/{$xoopsConfig['language']}/main.php";
include_once XOOPS_ROOT_PATH."/common/default_language.php";
include_once XOOPS_ROOT_PATH."/common/class/xoopsModule.php";
include_once XOOPS_ROOT_PATH."/common/class/xoopsDB.php";
include_once XOOPS_ROOT_PATH."/common/class/textsanitizer.php";


$xoopsModule=new xoopsModule();
$xoopsDB=new xoopsDB();


$xoopsModuleConfig=get_xoopsModulesConfig();
if(empty($xoopsModuleConfig)) $xoopsModuleConfig=mkXoopsModuleConfig($modversion);

if($_REQUEST['op']=="login_chk"){
  login_chk($_POST['login_id'],$_POST['login_pass']);
  header("location:".XOOPS_URL."/index.php");
}elseif($_REQUEST['op']=="logout"){
  logout();
}


$module_login=(isAdmin())?"<a href='".XOOPS_URL."/admin/index.php'>後台管理</a> | <a href='".XOOPS_URL."/common/setup.php'>偏好設定</a>":login_form();
$module_title=(empty($xoopsModuleConfig['fp_title']))?$modversion['name']:$xoopsModuleConfig['fp_title'];
$module_url=XOOPS_URL;


//判斷是否為管理員
function isAdmin(){
  if($_SESSION['login_id']==ADMIN_ID and $_SESSION['login_pass']==ADMIN_PASSWD){
    return true;
  }
  return false;
}

//登出按鈕
function logout_button($interface_menu=array()){
  if(!empty($_SESSION['login_id'])){
    $interface_menu["登出"]="index.php?op=logout";
  }
  return $interface_menu;
}

//登出
function logout(){
  $_SESSION['login_id']=$_SESSION['login_pass']="";
  $_SESSION=array();
	header("location: index.php");
  return;
}

//管理員登入表單
function login_form(){
  $form="<form action='{$_SERVER['PHP_SELF']}' method='post'>
  管理帳號：<input type='text' name='login_id' size=10>
  管理密碼：<input type='password' name='login_pass' size=10>
  <input type='hidden' name='op' value='login_chk'>
  <input type='submit' value='登入'>
  </form>";
  return $form;
}

//登入檢查
function login_chk($uname,$pwd){
  if(!empty($uname) and !empty($pwd)){
    if($uname==ADMIN_ID and $pwd==ADMIN_PASSWD){
      $_SESSION['login_id']=$uname;
      $_SESSION['login_pass']=$pwd;
      return true;
    }
  }
  return false;
}


//模組前台頁尾
function module_footer($main=""){
  global $blocks,$main,$module_css,$module_menu;

  include_once(XOOPS_ROOT_PATH.'/common/tbs/tbs_class_php5.php');
  $TBS =new clsTinyButStrong ;

  $TBS->LoadTemplate(XOOPS_ROOT_PATH."/common/themes/".WEB_THEME."/index.html",False) ;
  $TBS->Show() ;
}

//模組後台頁尾
function module_admin_footer($main="",$n=""){
  global $blocks,$main,$module_css,$module_menu;

  include_once(XOOPS_ROOT_PATH.'/common/tbs/tbs_class_php5.php');
  $TBS =new clsTinyButStrong ;

  $TBS->LoadTemplate(XOOPS_ROOT_PATH."/common/themes/".WEB_THEME."/index.html",False) ;
  $TBS->Show() ;
}

//轉向
function redirect_header($url, $time = 3, $message = '', $addredirect = true, $allowExternalLink = false){
  header("location:{$url}");
}

//整理 $xoopsModuleConfig
function mkXoopsModuleConfig($modversion=""){
  if(!is_array($modversion['config']))return;
  foreach($modversion['config'] as $n =>$item){
    $key=$item['name'];
    $xoopsModuleConfig[$key]=$modversion['config'][$n]['default'];
  }
  return $xoopsModuleConfig;
}


//取得$xoopsModulesConfig
function get_xoopsModulesConfig($config_type='module'){
  global $xoopsDB;
  if(!mysql_table_exists('ck2mod_config')) mk_config_table();
  $sql="select config_sn,config_name,config_value from `ck2mod_config` where config_type='$config_type'";
  $result=$xoopsDB->query($sql);
  while(list($config_sn,$config_name,$config_value)=$xoopsDB->fetchRow($result)){
    $xoopsModulesConfig[$config_name]=$config_value;
  }
  return $xoopsModulesConfig;
}



//建立設定表格
function mk_config_table(){
  global $xoopsDB;
  $sql="CREATE TABLE `ck2mod_config` (
  `config_sn` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT '設定編號',
  `config_name` VARCHAR( 255 ) NOT NULL COMMENT '欄位名稱',
  `config_value` TEXT NOT NULL COMMENT '欄位值',
  `config_type` VARCHAR( 255 ) NOT NULL COMMENT '設定類型'
  ) ENGINE = MYISAM ;";

  $xoopsDB->query($sql);

}

//檢查資料表在不在
function mysql_table_exists($tableName=""){
  global $xoopsDB;
  $sql="SHOW TABLES FROM ".XOOPS_DB_NAME;
  $tablesResult=$xoopsDB->query($sql);
  $tables = array();
  while ($row = @mysql_fetch_row($tablesResult)) $tables[] = $row[0];
  return(in_array($tableName, $tables));
}
?>