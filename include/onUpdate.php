<?php

function xoops_module_update_es_charge(&$module, $old_version) {
    GLOBAL $xoopsDB;
    
    if(!chk_add_cause()) go_update_add_cause();
    if(!chk_add_name()) go_update_add_name();    
    return true;
}

//------單項減免說明--------------------------------------------------
function chk_add_cause(){
  global $xoopsDB;
  $sql=" select cause_other  from ".$xoopsDB->prefix("charge_decrease");
  $result=$xoopsDB->query($sql);
  if(empty($result)) return false;
  return true;
}

function go_update_add_cause(){
  global $xoopsDB;

     $sql=" ALTER TABLE  ".$xoopsDB->prefix("charge_decrease") ."  ADD `cause_other` INT NOT NULL   ";
     $xoopsDB->queryF($sql)  ;

     $sql=" ALTER TABLE  ".$xoopsDB->prefix("charge_record") ." ADD `ps` VARCHAR( 200 ) NOT NULL   ";
     $xoopsDB->queryF($sql)  ;
}

//--------開列清單加姓名，方便判別是否已轉校------------------------------------------------
function chk_add_name(){
  global $xoopsDB;
  $sql=" select rec_name  from ".$xoopsDB->prefix("charge_record");
  $result=$xoopsDB->query($sql);
  if(empty($result)) return false;
  return true;
}

function go_update_add_name(){
  global $xoopsDB;

     $sql=" ALTER TABLE  ".$xoopsDB->prefix("charge_record") ."  ADD `rec_name` VARCHAR( 20 )   NOT NULL   ";
     $xoopsDB->queryF($sql)  ;

 }
?>
