<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/

$xoopsOption['template_main'] = "es_a_post_join_tpl.html";
include_once "header.php";

include_once "../function.php";
/*
if (!$DEF['bank_account_use']) {
    echo '未使用郵局扣款！' ;
    exit() ;
}
*/

//把  ONLY_FULL_GROUP_BY 移除
$sql = " SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')); "  ;
$xoopsDB->queryF($sql)   ;


// 郵局傳檔
if ($_POST["do_key"] =='export') {
    //更換這次的扣款帳號相關資料
    change_account($_POST['item_id']) ;

    export_poster_data($_POST['item_id']) ;
    exit() ;
}
// 傳真封面檔
if ($_POST["do_key"] =='paper') {
  show_poster_paper($_POST['item_id']) ;
  exit() ;
}

// 資料轉 EXCEL 這次撽費清單
if ($_POST["do_key"] =='export_excel') {
    export_poster_data_excel($_POST['item_id']) ;
    exit() ;
}

// 失敗的清單
if ($_POST["do_key2"] =='result_stud') {
    export_fail($_POST['item_id']) ;
    exit() ;
}


include_once "../../tadtools/PHPExcel.php";
require_once '../../tadtools/PHPExcel/IOFactory.php';
/*-----------function區--------------*/

//把內部需要要繳費的資料轉放到 郵局的記錄檔案
function add_from_charge($item_id)
{
    global   $xoopsDB , $err_message  ;
    //細項名稱
    $detail_list=get_item_detail_list_name($item_id) ;

    //取得全部細項的收費
    $charge_array= get_detail_charge_dollars($item_id) ;
    //有繳費的各班級
    $class_list = get_class_id_list($item_id) ;

    foreach ($class_list as $class_id=> $class) {
        //分別以各班計算每人要繳費，寫在資料庫 end_pay
        each_stud_pay_class($class_id, $item_id, $detail_list, $charge_array) ;
    }

    //把資料轉入     xx_charge_poster_data
    //取出資料

    $sql_out = " SELECT a.student_sn, a.end_pay , a.item_id , a.class_id, a.sit_num , a.rec_name ,  a.in_bank , b.*    From "
        . $xoopsDB->prefix("charge_record") . " as a  "
        ." LEFT JOIN   "
        . $xoopsDB->prefix("charge_account") .  " as  b  "
        ."  on a.student_sn=b.stud_sn  "
        ."  where    a.item_id = '$item_id'  "
        ."  ORDER BY class_id, sit_num " ;

    //echo $sql_out ;
    $result_out = $xoopsDB->queryF($sql_out)   ;

    while ($stud=$xoopsDB->fetchArray($result_out)) {
        //自交或無帳號
        if (($stud['in_bank']==1) and   (! is_null($stud['acc_person_id']))) {
            $cash_fg = 0  ;
        } else {
            $cash_fg =  1  ;
        }

        //寫入紀錄：
        $sql = " INSERT INTO " .  $xoopsDB->prefix("charge_poster_data")
            ." (`item_id`, `t_id`, `class_id`, `sit_num`, `st_name`, `pay`, `acc_name`, `acc_personid`, `acc_mode`, `acc_b_id`, `acc_id`, `acc_g_id` , stud_else ,cash , pay_fail  )  "
            ."  VALUES ( '$item_id' , '{$stud[student_sn]}'  , '{$stud[class_id]}' , '{$stud[sit_num]}' , '{$stud[rec_name]}'  , '{$stud[end_pay]}'   "
            ." , '{$stud[acc_name]}'   , '{$stud[acc_person_id]}'    , '{$stud[acc_mode]}'   , '{$stud[acc_b_id]}'    , '{$stud[acc_id]}'    , '{$stud[acc_g_id]}'  , '0' , '$cash_fg' ,0  ) ;   " ;
        $result = $xoopsDB->queryF($sql)  or  $err_message .=   $sql .'<br />' .$xoopsDB->error()."(應該為班級座號重覆或扣款帳號內容不正確引號)<br />"  ;

        //如果有錯誤，寫到記錄檔中
        if ($err_message) {
            $file = XOOPS_ROOT_PATH."/uploads/es_charge/" . $item_id .'-err.log' ;
            file_put_contents($file, $err_message);
        }
    }
}

//額外線費學生資料 匯入判別  ---------------------------------------------------------------------------------
function import_else_data($item_id)
{
    if ($_FILES['userdata']['name']) {
        $file_up = XOOPS_ROOT_PATH."/uploads/" .$_FILES['userdata']['name'] ;
        copy($_FILES['userdata']['tmp_name'], $file_up);
        $main="開始匯入" . $file_up .'<br>';

        //副檔名
        $file_array= preg_split('/[.]/', $_FILES['userdata']['name']) ;

        $ext= strtoupper(array_pop($file_array)) ;

        if ($ext=='XLS') {
            import_excel($item_id, $file_up) ;
        }
        if ($ext=='XLSX') {
            import_excel($item_id, $file_up, 2007) ;
        }
        //刪除上傳的檔。
        unlink($file_up)  ;
    }
    return $main;
}

//excel 格式 額外線費學生資料
function import_excel($item_id, $file_up, $ver=5)
{
    global $xoopsDB,$xoopsTpl ,$err_message  , $message ,  $DEF ;

    if ($ver ==5) {
        $reader = PHPExcel_IOFactory::createReader('Excel5');
    } else {
        $reader = PHPExcel_IOFactory::createReader('Excel2007');
    }

    $PHPExcel = $reader->load($file_up); // 檔案名稱
    $sheet = $PHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
    $highestRow = $sheet->getHighestRow(); // 取得總列數

    //0年級	1班級代號	2座號	3學生姓名	4繳費總額(整數)	5轉帳戶名	6轉帳戶身份證編號	7存款別(P/G)	8立帳局號	9存簿帳號	10劃撥帳號	11現金繳費(設為1)

    // 一次讀取一列
    for ($row = 2; $row <= $highestRow; $row++) {
        unset($v);

        //讀取一列中的每一格
        $this_line_data_fg  = false   ;
        for ($col = 0; $col <= 11; $col++) {
            $val =  $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            if (!get_magic_quotes_runtime()) {
                $v[$col]=strtoupper(trim(addSlashes($val)));
            } else {
                $v[$col]= strtoupper(trim($val)) ;
            }
            if ($v[$col]) {         //有內容要做動作
                $this_line_data_fg  = true  ;
            }
        }


        if ($this_line_data_fg) {        //些列有資料
            $line_str =   join(',', $v)  ;
            //echo $line_str ."<br />" ;


            if (! is_numeric($v[0])) {
                $v[0] = $DEF['class2id'][$v[0]] ;
            }

            if (! is_numeric($v[1])) {
                $v[1]=  $DEF['class2id'][$v[1]] ;
            }

            $class_id = $v[0]*100+$v[1] ;
            $class_id  =  sprintf("%03d", $class_id) ;
            $seat_id = $v[2] ;
            $stud_name = trim($v[3]) ;


            if (($v[0]>=0)  and  ($v[1] >=1)  and   ($v[2]>=1)  and    ($v[3])   and    ($v[4]>=0)) {
                $ckeck1 = 'ok'     ;
            } else {
                $err_message .=  " $line_str  必需有年、班、姓名、座號、繳費總額 <br/> " ;
                $ckeck1 = 'no'     ;
            }



            if ((strlen($v[6]) <>10) and   (strlen($v[6]) <>0)) {
                $err_message .=  " $line_str 身份證證號長度不正確！<br/> " ;
            }



            if ($ckeck1 == 'ok') {
                //移除帳號中非數字部份字元
                $v[8] = preg_replace('/\D/', '', $v[8]);
                $v[9] = preg_replace('/\D/', '', $v[9]);
                $v[10] = preg_replace('/\D/', '', $v[10]);

                //帳號補 0
                $v[8] = sprintf("%07d", $v[8]) ;
                $v[9] = sprintf("%07d", $v[9]) ;
                $v[10] = sprintf("%014d", $v[10]) ;

                $stud_sn = 'E' .  sprintf("%03d", $class_id)  . sprintf("%02d", $seat_id) ;
                //自繳或無扣款資料
                if (($v[11]) or ($v[6]=='')) {
                    $cash_fg =1 ;
                } else {
                    $cash_fg =0 ;
                }

                //寫入
                $sql = " INSERT INTO " .  $xoopsDB->prefix("charge_poster_data")
                   ." (`item_id`, `t_id`, `class_id`, `sit_num`, `st_name`, `pay`, `acc_name`, `acc_personid`, `acc_mode`, `acc_b_id`, `acc_id`, `acc_g_id` , stud_else ,cash ,pay_fail  )  "
                   ."  VALUES ( '$item_id' , '$stud_sn'  , '$class_id' , '$seat_id' , '$stud_name'  , '{$v[4]}'   "
                   ." , '{$v[5]}'   , '{$v[6]}'    , '{$v[7]}'   , '{$v[8]}'    , '{$v[9]}'    , '{$v[10]}'  , '1' , '$cash_fg'  ,0 ) ;   " ;

                $result = $xoopsDB->queryF($sql) or  $err_message .= $line_str  .  $xoopsDB->error()."(應該為班級座號重覆)<br />"  ;
                $update_ok_num ++ ;
            }
        }//此列有內容
    }

    //如果有錯誤，寫到記錄檔中
    if ($err_message) {
        $file = XOOPS_ROOT_PATH."/uploads/es_charge/" . $item_id .'-err.log' ;
        file_put_contents($file, $err_message);
    }

    $message = "完成 $update_ok_num 筆更新<br/>"   ;
}


//清除這次郵局資料
function clear_poster_data($item_id)
{
    global   $xoopsDB  ;
    $sql = " DELETE FROM " .  $xoopsDB->prefix("charge_poster_data")  ." WHERE item_id = '$item_id'  "  ;
    $result = $xoopsDB->queryF($sql) ;

    //清除錯誤檔
    $file = XOOPS_ROOT_PATH."/uploads/es_charge/" . $item_id .'-err.log' ;
    if (file_exists($file)) {
        unlink($file) ;
    }
}


//------------------------------------------------------------------------------
//對帳單 ，匯入純文字檔
function result_data($item_id)
{
    global   $xoopsDB ,$DEF  , $chk_error ;

    //這次的帳號
    change_account($item_id) ;


    if ($_FILES['result_data']['name']) {
        $file_up = XOOPS_ROOT_PATH."/uploads/es_charge/" . date('Ymd-'). $_FILES['result_data']['name'] ;
        copy($_FILES['result_data']['tmp_name'], $file_up);
        //$main="開始匯入" . $file_up .'<br>';

        //失敗註記還原
        $sql =  " UPDATE  " . $xoopsDB->prefix("charge_poster_data") . " SET  pay_fail = '0'  	where      item_id = '$item_id'    ; " ;
        $xoopsDB->queryF($sql)     ;




        //讀取文字檔 ，分行讀取
        $fp=fopen($file_up, "r");
        while (!feof($fp)) {
            $mydata[] =fgets($fp);
        }
        fclose($fp);

        //這一期繳費日期
        $must_pay_day = get_bank_date_cht($item_id) ;

        $pay_ok_num = 0 ;
        try {
            foreach ($mydata as $li =>$line) {
                if ($line[0] ==1) {
                    //扣款金額


                    //取得單位代號
                    $this_unit = substr($line, 2, 3);
                    $this_pay_day = substr($line, 9, 7);
                    if ($must_pay_day<>$this_pay_day) {
                        throw new Exception("扣款日期不相符 $this_pay_day ");
                    }

                    if ($this_unit<>$DEF['school_id']) {
                        throw new Exception("單位代碼不相同 $this_unit");
                    }

                    $pay = substr($line, 43, 11)/100;
                    //失敗原因 01  or  10   ????
                    $no_pay = substr($line, 78, 2);
                    if ($no_pay<>'  ') {
                        $pay_err_sum += $pay ;
                        $pay_err_num ++ ;
                        //身份証、局號、帳號
                        $person_id =   substr($line, 33, 10);
                        $acc_bid= substr($line, 19, 7);
                        $id= substr($line, 26, 7);
                        //echo "$person_id $acc_bid $id ---  $no_pay <br />" ;
                        //只以身份證做判別
                        $sql =  " UPDATE  " . $xoopsDB->prefix("charge_poster_data") . " SET  pay_fail =  '$no_pay'
                        where  acc_personid  = '$person_id'  and  acc_b_id='$acc_bid'  and acc_id='$id'   and   item_id = '$item_id'    ; " ;
                        $xoopsDB->queryF($sql)     ;
                    } else {
                        $pay_ok_num ++ ;
                        $pay_ok_sum += $pay ;
                    }
                    $pay_num ++ ;
                    $pay_sum += $pay ;
                }

                if ($line[0] ==2) {
                  //結算記錄
                  $s_recode_num  = substr($line, 19, 7);
                  $s_money  = substr($line, 26, 13)/100;
                  $s_account = substr($line, 39, 8);
                  $s_account2 = substr($line, 47, 8);
                  $s_in_recode_num  = substr($line, 55, 7);
                  $s_in_money  = substr($line, 62, 13)/100 ;
                  if ($DEF['school_accont']<>$s_account )
                    throw new Exception('學校撥款帳號不相同' .$s_account);
                  if ($DEF['school_accont2']<>$s_account2 )
                      throw new Exception('學校扣手續費帳號不相同' . $s_account2);
                    break ;
                }
            }
            $main .= "對帳記錄檔總數如下：<br>" ;
            $main .= "對帳記錄檔總筆數：  $s_recode_num ，應扣總金額：  $s_money          ，入帳總筆數： $s_in_recode_num          ，入帳總金額：$s_in_money    <br>";
            $main .= "逐筆計算所得：<br>應扣款筆數 $pay_num 筆， 應扣款額額：$pay_sum 元  。   成功扣款： $pay_ok_num 筆 ，成功扣款總額： " . ($pay_sum- $pay_err_sum)  ." 元 ";

            if  ($s_in_recode_num  ==0)
              throw new Exception('對帳記錄檔成功筆數為 0 ');
            //把總筆數及總扣款數寫入
            //$chk_sum = $pay_sum- $pay_err_sum ;
            $sql = " update     ".  $xoopsDB->prefix("charge_item")
                    ." SET  `c_rec_num`= '$s_in_recode_num'  ,`c_sum`= '$s_in_money'  "
                    ."  where  item_id='$item_id'     " ;
            $result = $xoopsDB->queryF($sql)   ;
        } catch (Exception $e) {
            $chk_error =  $e->getMessage() ;
        }



        //刪除上傳的檔。
        //unlink($file_up)  ;
    }
    return $main;
}

/*-----------執行動作判斷區----------*/
$item_id=empty($_REQUEST['item_id'])?"":$_REQUEST['item_id'];

//轉帳表
switch ($_POST["do_key"]) {
    case "add":
        add_from_charge($_POST['item_id']) ;
    break;
    case "add_other":
        import_else_data($_POST['item_id']) ;
    break;
    case "clear":
        clear_poster_data($_POST['item_id']) ;
    break;
    case "export":
        //郵局傳送檔
        export_poster_data($_POST['item_id']) ;
    break;
}

//對帳表
switch ($_POST["do_key2"]) {
    case "result_upload":
        $message2 = result_data($_POST['item_id']) ;
    break;
    case "result_stud":
        export_fail($_POST['item_id']) ;
    break;
}

/*----------取得資料區--------------*/
//取得目前可填收費表
$data['item_list']=get_item_list('all') ;

 if (!$item_id) {
     //選定最近的工作表
     $key = array_keys($data['item_list'])  ;
     $item_id=$key[1] ;
 }

 $data['select_item'] = $item_id  ;

if ($item_id) {
  //更換這次的扣款帳號相關資料
  change_account($item_id) ;

    //取得各項統計資料
    $data['total'] = get_poster_stud_num($item_id) ;
    //
    $pr = get_poster_chare_num($item_id) ;
    $data['p_text'] = "扣款記錄  $pr 筆 * 手續費 {$DEF['fee']}  +  總計: {$data['total']['pay']['pm'][0]} = "   .($pr *  $DEF['fee'] + $data['total']['pay']['pm'][0]) ;

    //扣款失敗人數
    $data['fail_studs'] =get_poster_chare_fail($item_id) ;
    //在記錄中已有扣款單、對帳單
    $data['item']= get_item_data($item_id) ;

    //比對最後結果是否正確
    if ($data['fail_studs']  and $data['item']){

      if ($data['item']['f_sum'] - $data['item']['f_rec_num']*$DEF['fee']<>$data['fail_studs']['pay_sum'])
        $chk_error.="<p>補繳金額計算式有問題，請做檢查！ {$data['item']['f_sum']} - {$data['item']['f_rec_num']} * {$DEF['fee']} <> {$data['fail_studs']['pay_sum']} </p>";
    }

}


//如果有錯誤，就不可以再進行下一步
$file = XOOPS_ROOT_PATH."/uploads/es_charge/" . $item_id .'-err.log' ;
if (file_exists($file)) {
    $has_error = ture ;
    $err_log = file_get_contents($file);
}





/*-----------秀出結果區--------------*/
$xoopsTpl->assign("data", $data) ;
$xoopsTpl->assign("message2", $message2) ;
$xoopsTpl->assign("err_message", $err_message) ;
$xoopsTpl->assign("DEF", $DEF) ;
$xoopsTpl->assign("has_error", $has_error) ;
$xoopsTpl->assign("err_log", $err_log) ;
$xoopsTpl->assign("chk_error", $chk_error) ;

include_once 'footer.php';
