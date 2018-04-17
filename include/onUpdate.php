<?php

function xoops_module_update_es_charge(&$module, $old_version)
{
    global $xoopsDB;

    if (!chk_add_cause()) {
        go_update_add_cause();
    }
    if (!chk_add_name()) {
        go_update_add_name();
    }
    //郵局帳號資料表
    if (!chk_add_account()) {
        go_update_add_account();
    }
    //個人繳費小計
    if (!chk_add_pay_sum()) {
        go_update_add_pay_sum();
    }
    //扣款日、傳送、對帳筆數、金額
    if (!chk_add_pay_date()) {
        go_update_add_pay_date();
    }

    //合併郵局記錄表
    if (!chk_add_poster_data()) {
        go_update_add_poster_data();
    }


    //多個扣款帳號
    if (!chk_add_bank_account()) {
        go_update_add_bank_account();
    }

    //多個扣款帳號
    if (!chk_add_item_bank_id()) {
        go_update_add_bank_id();
    }

    mk_dir(XOOPS_ROOT_PATH."/uploads/es_charge");

    return true;
}

//建立目錄
function mk_dir($dir=""){
    //若無目錄名稱秀出警告訊息
    if(empty($dir))return;
    //若目錄不存在的話建立目錄
    if (!is_dir($dir)) {
        umask(000);
        //若建立失敗秀出警告訊息
        mkdir($dir, 0777);
    }
}
//  ---- 收費表多加扣款帳號 id

function chk_add_item_bank_id() {
  global $xoopsDB;
  $sql = ' select bank_id  from '.$xoopsDB->prefix('charge_item');
  $result = $xoopsDB->queryF($sql);

  if (empty($result)) {
      return false;
  }

  return true;
}

function go_update_add_bank_id() {

    global $xoopsDB;

    $sql = '  ALTER TABLE   '.$xoopsDB->prefix('charge_item').'
     ADD `bank_id` int(11) NOT NULL    ;
     ';

    $xoopsDB->queryF($sql);
}


// ------- 增加多個扣款帳號
function chk_add_bank_account()
{
    global $xoopsDB;

  $sql = 'select count(*) from '.$xoopsDB->prefix('charge_bank_account');
    $result = $xoopsDB->query($sql);
    if (empty($result)) {
        return false;
    }

    return true;
}

function go_update_add_bank_account()
{
    global $xoopsDB;

    $sql = ' CREATE TABLE  '.$xoopsDB->prefix('charge_bank_account')
      .' (
       `b_id` int(11) NOT NULL AUTO_INCREMENT,
      `account_name` varchar(80)  NOT NULL,
      `account1`  varchar(80)  NOT NULL,
      `account2`  varchar(80)  NOT NULL,
      `account_id`  varchar(10)  NOT NULL,
      `account_block_id`  varchar(10)  NOT NULL,
      `account_pay`  int(11) NOT NULL DEFAULT 0  ,
      `paper`  TEXT NOT NULL ,
      PRIMARY KEY (`b_id`)
      ) ENGINE=MyISAM   ';


    $xoopsDB->queryF($sql);
}

//----- 增加 charge_account 資料表 ------------------------------
function chk_add_poster_data()
{
    global $xoopsDB;
  //$sql=" select acc_personid  from ".$xoopsDB->prefix("charge_poster_data");
  $sql = 'select count(*) from '.$xoopsDB->prefix('charge_poster_data');
    $result = $xoopsDB->query($sql);
    if (empty($result)) {
        return false;
    }

    return true;
}

function go_update_add_poster_data()
{
    global $xoopsDB;

    $sql = ' CREATE TABLE  '.$xoopsDB->prefix('charge_poster_data')
      .'   (
     `item_id` int(11) NOT NULL,
     `t_id` varchar(20)  NOT NULL,
     `class_id` int(11) NOT NULL,
     `sit_num` int(11) NOT NULL,
     `st_name` varchar(30)  NOT NULL,
     `pay` int(11) NOT NULL,
     `acc_name` varchar(30)  NOT NULL,
     `acc_personid` varchar(20)  NOT NULL,
     `acc_mode` varchar(10)  NOT NULL,
     `acc_b_id` varchar(20)  NOT NULL,
     `acc_id` varchar(20)  NOT NULL,
     `acc_g_id`  varchar(20)  NOT NULL,
     `stud_else` int(11) NOT NULL,
     `cash` int(11) NOT NULL,
     `pay_fail` int(11) NOT NULL,
     PRIMARY KEY (`item_id`,`t_id`)
    ) ENGINE=MyISAM    ';

    $xoopsDB->queryF($sql);
}

//----- 增加  扣款日 ------------------------------
function chk_add_pay_date()
{
    global $xoopsDB;
    $sql = ' select bank_date  from '.$xoopsDB->prefix('charge_item');
    $result = $xoopsDB->queryF($sql);

    if (empty($result)) {
        return false;
    }

    return true;
}

function go_update_add_pay_date()
{
    global $xoopsDB;

    $sql = '  ALTER TABLE   '.$xoopsDB->prefix('charge_item').'   ADD  `bank_date` date DEFAULT NULL  ,
     ADD `p_rec_num` INT NOT NULL ,
     ADD `p_sum` INT NOT NULL ,
     ADD `c_rec_num` INT NOT NULL ,
     ADD `c_sum` INT NOT NULL ;
     ';

    $xoopsDB->queryF($sql);
}

//----- 增加 charge_account 資料表 ------------------------------
function chk_add_account()
{
    global $xoopsDB;
    $sql = ' select acc_person_id  from '.$xoopsDB->prefix('charge_account');
    $result = $xoopsDB->query($sql);
    if (empty($result)) {
        return false;
    }

    return true;
}

function go_update_add_account()
{
    global $xoopsDB;

    $sql = ' CREATE TABLE  '.$xoopsDB->prefix('charge_account').'   (
       `a_id` bigint(20) NOT NULL AUTO_INCREMENT,
       `stud_sn` bigint(20) NOT NULL,
       `stud_name` varchar(30) NOT NULL,
       `acc_name` varchar(30) NOT NULL,
       `acc_person_id` varchar(12) NOT NULL,
       `acc_mode` char(1) NOT NULL,
       `acc_b_id` varchar(20) NOT NULL,
       `acc_id` varchar(20) NOT NULL,
       `acc_g_id` varchar(20) NOT NULL,
       PRIMARY KEY (`stud_sn`),
       KEY `a_id` (`a_id`)
     ) ENGINE=MyISAM     ';
    $xoopsDB->queryF($sql);
}

//----- 增加 charge_record  資料表  end_pay , pay_ok  欄位-----------------------
function chk_add_pay_sum()
{
    global $xoopsDB;
    $sql = ' select sit_num  from '.$xoopsDB->prefix('charge_record');
    $result = $xoopsDB->query($sql);
    if (empty($result)) {
        return false;
    }

    return true;
}

function go_update_add_pay_sum()
{
    global $xoopsDB;

    $sql = ' ALTER TABLE  '.$xoopsDB->prefix('charge_record').'  ADD `end_pay` int(11) NOT NULL,  ADD  `pay_ok` int(11) NOT NULL  , ADD `sit_num` INT NOT NULL  ';
    $xoopsDB->queryF($sql);
}

//------單項減免說明--------------------------------------------------
function chk_add_cause()
{
    global $xoopsDB;
    $sql = ' select cause_other  from '.$xoopsDB->prefix('charge_decrease');
    $result = $xoopsDB->query($sql);
    if (empty($result)) {
        return false;
    }

    return true;
}

function go_update_add_cause()
{
    global $xoopsDB;

    $sql = ' ALTER TABLE  '.$xoopsDB->prefix('charge_decrease').'  ADD `cause_other` INT NOT NULL   ';
    $xoopsDB->queryF($sql);

    $sql = ' ALTER TABLE  '.$xoopsDB->prefix('charge_record').' ADD `ps` VARCHAR( 200 ) NOT NULL   ';
    $xoopsDB->queryF($sql);
}

//--------開列清單加姓名，方便判別是否已轉校------------------------------------------------
function chk_add_name()
{
    global $xoopsDB;
    $sql = ' select rec_name  from '.$xoopsDB->prefix('charge_record');
    $result = $xoopsDB->query($sql);
    if (empty($result)) {
        return false;
    }

    return true;
}

function go_update_add_name()
{
    global $xoopsDB;

    $sql = ' ALTER TABLE  '.$xoopsDB->prefix('charge_record').'  ADD `rec_name` VARCHAR( 20 )   NOT NULL   ';
    $xoopsDB->queryF($sql);
}
