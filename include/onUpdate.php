<?php

function xoops_module_update_es_charge(&$module, $old_version) {
    GLOBAL $xoopsDB;
    
    if(!chk_add_cause()) go_update_add_cause();
    return true;
}


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


?>
