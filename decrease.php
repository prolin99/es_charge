<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
//ALTER TABLE `charge_decrease` ADD `cause_other` INT NOT NULL ;
//ALTER TABLE `charge_record` ADD `ps` VARCHAR( 200 ) NOT NULL ;
// ------------------------------------------------------------------------- //
use XoopsModules\Tadtools\Utility;


/*-----------引入檔案區--------------*/
//$xoopsOption['template_main'] = "es_decrease_tpl.html";
include_once 'header.php';
$xoopsOption['template_main'] = 'es_decrease.tpl';
include_once XOOPS_ROOT_PATH.'/header.php';

/*-----------function區--------------*/
 if (!$xoopsUser) {
     redirect_header(XOOPS_URL, 3, '需要登入，才能使用！');
 }

/*-----------執行動作判斷區----------*/
//取得中文班名
$data['class_list_c'] = es_class_name_list_c('long');

//新增一筆
if ($_POST['act_add'] and $_POST['stud']) {
    $arr = explode('_', $_POST['stud']);
    $stud_sn = intval($arr[0]);
    $sit_num = intval($arr[1]);
    //ps
    $myts = &MyTextSanitizer::getInstance();
    $ps = $myts->htmlspecialchars($xoopsDB->escape($_POST['ps']));
    //減免原因
        $sql = ' UPDATE  '.$xoopsDB->prefix('charge_record').
                    " SET  cause='{$_POST['cause_id']}'  , ps = '$ps'  WHERE student_sn = '$stud_sn'   ";
    $result = $xoopsDB->query($sql) or redirect_header($_SERVER['PHP_SELF'], 3, $xoopsDB->error());

    foreach ($_POST['dollars'] as $detail_id => $dollar) {
        //各項減免
        if ($dollar > 0) {
            if ($_POST['cause_id'] > 0) {    //有特殊身份，才能申請補助
               $cause = $_POST['decrease_sel'][$detail_id] +0 ;   //強迫轉為數值
                $other = $_POST['other'][$detail_id] +0 ;        //其他身份別
            } else {
                $cause = 0;
                $other = 0;
            }

            $sql = ' insert into   '.$xoopsDB->prefix('charge_decrease').
                 " (`item_id`, `detail_id`, `student_sn`, `curr_class_num`, sit_num ,`decrease_dollar`, `cause_chk` ,cause_other  )
	    	   	VALUES(   '{$_POST['item_id']}'  ,  '$detail_id'  ,  '$stud_sn'  ,  '{$_POST['class_id']}'  , '$sit_num' ,  '$dollar'  ,  '$cause'   ,'$other' )
	    	   	";


            $result = $xoopsDB->query($sql) ; //or redirect_header($_SERVER['PHP_SELF'], 3,  $xoopsDB->error());

        }
    }
}

/*----------取得資料區--------------*/
    //取得目前可填收費表
    $data['item_list'] = get_item_list('all');

    //取得所在班級
    $class_id = get_my_class_id($xoopsUser->uid());

     if (($class_id == '') and  !$isAdmin) {
         redirect_header(XOOPS_URL, 3, '非級任，無法使用！');
     }

        //管理者可以選取多班
        if ($isAdmin) {
            $data['admin'] = true;

            //有收費的班級名稱
            $data['class_list'] = get_record_class_list($item_id);

            //取得班級
            if ($_POST['admin_class_id']) {
                $class_id = $_POST['admin_class_id'];
            } elseif (!$class_id) {
                $class_id = array_shift(array_keys($data['class_list']));
            }
        }

    if ($item_id) {

        //取得該班的資料
        if ($class_id) {

            //有繳費
            $data['selected'] = get_class_students_charge($item_id, $class_id);
            $data['selected_count'] = count($data['selected']);
            //學生名冊
            $data['students'] = get_class_students($class_id);
            $data['students_count'] = count($data['students']);

            //取得舊項有特殊身份者
            $data['spec_list'] = get_class_spec_old_item($item_id, $class_id);
        }

        //檢查是否在期限內
        if (item_in_time($item_id)) {        //是否在報名時間內
            $data['inTime'] = true;
        }

        if ($isAdmin) {
            $data['inTime'] = true;
        }
    }

//細項名稱
$data['detail_list'] = get_item_detail_list_name($item_id);
//是否可申請補助的檢查
$data['dent_support'] = check_deny_support($data['detail_list']);

$detail_id_array = array_keys($data['detail_list']);

//取得全部細項的收費
$charge_array = get_detail_charge_dollars($item_id);

//班上已填的減免資料
$data['decase_list'] = get_decrease_list_item_array($class_id, $item_id);

//是否有在作業期間轉出要刪除的學生
$data['out_student'] = chk_student_out($item_id, $class_id, 'class');

$data['seletc_detail'] = $detail_id;
$data['seletc_item'] = $item_id;
$data['class_id'] = $class_id;

//本年級各項繳費金額
$y = ($data['class_id'] / 100) - 1;
foreach ($charge_array as $detail_id => $dollars) {
    $my_class_charge_array['pay'][$detail_id] = $dollars[$y];
    $my_class_charge_array['decease'][$detail_id] = '一半:'.$dollars[$y] / 2;
}
$data['detail_dollar'] = $my_class_charge_array;

//取出說明
$data['ps'] = $xoopsModuleConfig['es_charge_ps'];

/*-----------秀出結果區--------------*/
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu));

$xoopsTpl->assign('data', $data);
$xoopsTpl->assign('decrease_cause', $decrease_cause);


include_once XOOPS_ROOT_PATH.'/footer.php';
