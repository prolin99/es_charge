<?php
/*-----------引入檔案區--------------*/
include_once "../header.php";
if(!isAdmin())header("location:../index.php");
/*-----------function區--------------*/

//偏好設定表單
function setup_form($config=""){
  $xoopsModulesConfig=get_xoopsModulesConfig();
  
  foreach($config as $i=>$set){
    eval("\$title={$set['title']};");
    eval("\$description={$set['description']};");
    $input="";
    $col_name=$set['name'];
    $value=(empty($xoopsModulesConfig[$col_name]))?$set['default']:$xoopsModulesConfig[$col_name];
    if($set['formtype']=="textbox"){
      $input="<input type='text' name='setup[{$col_name}]' value='{$value}' style='width:100%;'>";
    }elseif($set['formtype']=="select"){
      if(is_array($set['options'])){
        $options="";
        foreach($set['options'] as $val=>$key){
          $selected=($value==$key)?"selected":"";
          $options.="<option value='{$key}' $selected>$val</option>";
        }
      }
      $input="<select name='setup[{$col_name}]'>$options</select>";
    }elseif($set['formtype']=="textarea"){
      $input="<textarea name='setup[{$col_name}]' style='width:100%;height:60px;'>{$value}</textarea>";
    }
    
    $data.="<tr><td><b>{$title}</b>
    <div>{$description}</div></td><td>{$input}</td></tr>";
  }
  
  $main="<form action='{$_SERVER['PHP_SELF']}' method='post'>
  <table>
  $data
  </table>
  <p align='center'>
  <input type='hidden' name='op' value='save_config'>
  <input type='submit' value='儲存設定'>
  </p>
  </form>";
  return $main;
}

//儲存設定
function save_config(){
  global $xoopsDB;
  if(!mysql_table_exists('ck2mod_config')) mk_config_table();
  $sql="delete from `ck2mod_config`";
  $xoopsDB->query($sql);
    
  foreach($_POST['setup'] as $name=>$value){
    $sql="insert into `ck2mod_config` (config_name,config_value,config_type) values('$name','$value','module');";
    $xoopsDB->query($sql);
  }
}


/*-----------執行動作判斷區----------*/
$op=(empty($_REQUEST['op']))?"":$_REQUEST['op'];
switch($op){
	case "save_config":
	save_config();
	header("location:../index.php");
	break;

	default:
	$main=setup_form($modversion['config']);
	break;
}

/*-----------秀出結果區--------------*/
module_footer($main,$module_menu);

?>