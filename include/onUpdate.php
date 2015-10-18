<?php

function xoops_module_update_es_charge(&$module, $old_version) {
    GLOBAL $xoopsDB;

    if(!chk_add_cause()) go_update_add_cause();
    if(!chk_add_name()) go_update_add_name();

    if(!chk_add_account()) go_update_add_account();

    if(!chk_add_pay_sum()) go_update_add_pay_sum();


    return true;
}


//----- 增加 charge_account 資料表 ------------------------------
function chk_add_account(){
  global $xoopsDB;
  $sql=" select cause_other  from ".$xoopsDB->prefix("charge_account");
  $result=$xoopsDB->query($sql);
  if(empty($result)) return false;
  return true;
}

function go_update_add_account(){
  global $xoopsDB;

     $sql=" CREATE TABLE  ".$xoopsDB->prefix("charge_account") ."   (
       `stud_sn` bigint(20) NOT NULL,
       `stud_name` varchar(30) NOT NULL,
       `acc_name` varchar(30) NOT NULL,
       `acc_person_id` varchar(12) NOT NULL,
       `acc_mode` char(1) NOT NULL,
       `acc_b_id` varchar(20) NOT NULL,
       `acc_id` varchar(20) NOT NULL,
       `acc_g_id` varchar(20) NOT NULL,
       PRIMARY KEY (`stud_sn`)
     ) ENGINE=MyISAM     ";
     $xoopsDB->queryF($sql)  ;
}


//----- 增加 charge_record  資料表  end_pay , pay_ok  欄位-----------------------
function chk_add_pay_sum(){
  global $xoopsDB;
  $sql=" select end_pay  from ".$xoopsDB->prefix("charge_record");
  $result=$xoopsDB->query($sql);
  if(empty($result)) return false;
  return true;
}

function go_update_add_pay_sum(){
  global $xoopsDB;

     $sql=" ALTER TABLE  ".$xoopsDB->prefix("charge_record") ."  ADD `end_pay` int(11) NOT NULL,  ADD  `pay_ok` int(11) NOT NULL   ";
     $xoopsDB->queryF($sql)  ;

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
