<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2018-03-30
// $Id:$
// ------------------------------------------------------------------------- //

/*-----------引入檔案區--------------*/

//樣版
$xoopsOption['template_main'] = "es_admin_sch_acc_tpl.html";
include_once "header.php";
include_once "../function.php";


/*-----------function區--------------*/
//


/*-----------執行動作判斷區----------*/

if ($_POST['act_edit']) {
  $sql = " UPDATE  "  . $xoopsDB->prefix("charge_bank_account") .
	     	   "  SET `account_name`= '{$_POST['account_name']}' ,
            `account1`= '{$_POST['account1']}' ,
            `account2`= '{$_POST['account2']}' ,
            `account_id`= '{$_POST['account_id']}' ,
            `account_block_id`= '{$_POST['account_block_id']}' ,
            `account_pay`= '{$_POST['account_pay']}' ,
            `paper`= '{$_POST['paper']}'
            where b_id = '{$_POST['b_id']}' " ;
  $result = $xoopsDB->query($sql) ;//or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
}

if ($_POST['act_del']) {
  $sql = " DELETE FROM  "  . $xoopsDB->prefix("charge_bank_account") .
     	   	   "  WHERE  `b_id` = '{$_POST['b_id']}' " ;
  $result = $xoopsDB->query($sql) ;//or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
}

//封面頁樣版
$def_data='
<h1 style="text-align: center;">委託郵局媒體轉帳代收總數表</h1>
<p style="text-align: center;">押碼值：XXXXXXXX</p>
<p>受託局名： XXXX郵局   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 單位代號：XXX </p>
<p>受託局號： XXXXXXXX  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  區處代號：</p>

<p>&nbsp;</p>

<table  style="border:3px"  border="1" width="600">

  <tr >
    <td>&nbsp;&nbsp;&nbsp;轉帳代收日期 &nbsp;&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;&nbsp;總件數	&nbsp;&nbsp;&nbsp;</td>
    <td>&nbsp;&nbsp;&nbsp;總金額 &nbsp;&nbsp;&nbsp;</td>
  </tr>
  <tr >
    <td>&nbsp;&nbsp;&nbsp;{{pay_date}}</td>
    <td>&nbsp;&nbsp;&nbsp;{{pay_count}}	</td>
    <td>&nbsp;&nbsp;&nbsp;{{pay_money}} </td>
  </tr>
</table>
<p >&nbsp;</p>
<p>學校名稱：XXXXXXXXXXXXXXXXX </p>
<p>學校地址：XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</p>
<p >出納：XXXXXXXXXXXXXXXXXXX</p>
<p>電話：XXXXXXXXXXXXXXXXXXXXXXXX</p>
<p>行動電話：XXXXXXX</p>
<p>傳真機號碼：XXXXXXXXXXXXXXXXXXX</p>
<p>學校蓋章處：</p>
<p></p><p></p>
<p>中華郵政公司儲匯處行銷科委辦帳務股：</P>
<p>地址：106 台北市愛國東路216號520室</P>
<p>電話號碼：(02) 2391-4024</p>
<p>(02) 2393-1261 分機 3397、3560</p>
<p>傳真機號碼：(02) 2321-7770</p>
<p>(02) 2356-0964</P>
';


if ($_POST['btn_add']) {
  $add_fg = true;


  include_once XOOPS_ROOT_PATH . "/modules/tadtools/ck.php";
  $ck = new CKEditor("editor_paper", "paper", $def_data);
  $ck->setHeight(350);
  $editor = $ck->render();
}

if ($_POST['act_add']) {
  $sql = ' INSERT INTO ' . $xoopsDB->prefix("charge_bank_account") .
  " (`b_id`,  `account_name`, `account1`, `account2`, `account_id`, `account_block_id`, `account_pay` , `paper`)  " .
  "VALUES ('0','{$_POST['account_name']}'    ,'{$_POST['account1']}'   , '{$_POST['account2']}'  ,'{$_POST['account_id']}'   ,  '{$_POST['account_block_id']}' , '{$_POST['account_pay']}' ,'{$_POST['paper']}'  )" ;
  $result = $xoopsDB->query($sql) ;//or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
}

$account_list = get_school_account() ;



if (count($account_list)==0 and $DEF['school_accont'] ) {
  //第一次更新，由原在偏好中的設定值 移入到資料表中
  $sql = ' INSERT INTO ' . $xoopsDB->prefix("charge_bank_account") .
  " (`b_id`,  `account_name`, `account1`, `account2`, `account_id`, `account_block_id`, `account_pay`  , `paper` )  " .
  "VALUES ('0','學校'     ,'{$DEF['school_accont']}'   , '{$DEF['school_accont2']}'  , '{$DEF['school_id']}'   ,  '{$DEF['poster_block']}'  , '{$DEF['fee']}'  , '$def_data'  )" ;
  $result = $xoopsDB->queryF($sql) ; //or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());

  $account_list = get_school_account() ;

  //把偏好清空
  $sql=  "update   " . $xoopsDB->prefix("config") ." set conf_value='' where conf_name='es_c_school_accont' "  ;
	$result = $xoopsDB->queryF($sql) ;// or redirect_header($_SERVER['PHP_SELF'],3, $xoopsDB->error());
}



//編輯模式
if ($_GET['do']=='edit'){
  $edit_fg = true;
  $now_id = $_GET['b_id']+0 ;
  include_once XOOPS_ROOT_PATH . "/modules/tadtools/ck.php";
  $ck = new CKEditor("editor_paper", "paper", $account_list[$now_id]['paper']);
  $ck->setHeight(350);
  $editor = $ck->render();
}




/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "account_list" , $account_list ) ;
$xoopsTpl->assign( "edit_fg" , $edit_fg ) ;
$xoopsTpl->assign( "add_fg" , $add_fg ) ;
$xoopsTpl->assign( "now_id" , $now_id ) ;
$xoopsTpl->assign( "editor" , $editor ) ;

include_once 'footer.php';
?>
