<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
include_once "header_admin.php";
//樣版
$xoopsOption['template_main'] = "es_a_post_join_tpl.html";
include_once "header.php";

include_once "../function.php";
/*
if (!$DEF['bank_account_use']) {
	echo '未使用郵局扣款！' ;
	exit() ;
}
*/
if ($_POST["do_key"] =='export') {
    export_poster_data($_POST['item_id'] ) ;
    exit() ;
}
if ($_POST["do_key2"] =='result_stud') {
    export_fail($_POST['item_id'] ) ;
    exit() ;
}


include_once "../../tadtools/PHPExcel.php";
require_once '../../tadtools/PHPExcel/IOFactory.php';
/*-----------function區--------------*/

//把資料轉放到 郵局的記錄檔案
function add_from_charge($item_id){
    global   $xoopsDB , $err_message  ;
    //細項名稱
	$detail_list=get_item_detail_list_name($item_id) ;

	//取得全部細項的收費
	$charge_array= get_detail_charge_dollars( $item_id) ;
	//有繳費的各班級
	$class_list = get_class_id_list($item_id) ;

	foreach ($class_list as $class_id=> $class) {
		//分別以各班計算每人要繳費，寫在資料庫 end_pay
		each_stud_pay_class($class_id , $item_id ,  $detail_list ,  $charge_array) ;
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

	while($stud=$xoopsDB->fetchArray($result_out)){
        //自交或無帳號
        if ( ($stud['in_bank']==1) and   ( ! is_null($stud['acc_person_id'] ) ) )
            $cash_fg = 0  ;
        else
            $cash_fg =  1  ;

        //寫入紀錄：
         $sql = " INSERT INTO " .  $xoopsDB->prefix("charge_poster_data")
            ." (`item_id`, `t_id`, `class_id`, `sit_num`, `st_name`, `pay`, `acc_name`, `acc_personid`, `acc_mode`, `acc_b_id`, `acc_id`, `acc_g_id` , stud_else ,cash , pay_fail  )  "
            ."  VALUES ( '$item_id' , '{$stud[student_sn]}'  , '{$stud[class_id]}' , '{$stud[sit_num]}' , '{$stud[rec_name]}'  , '{$stud[end_pay]}'   "
            ." , '{$stud[acc_name]}'   , '{$stud[acc_person_id]}'    , '{$stud[acc_mode]}'   , '{$stud[acc_b_id]}'    , '{$stud[acc_id]}'    , '{$stud[acc_g_id]}'  , '0' , '$cash_fg' ,0  ) ;   " ;
        $result = $xoopsDB->queryF($sql)  or  $err_message .=   $sql .'<br />' .$xoopsDB->error()."(應該為班級座號重覆)<br />"  ;
    }
}

//匯入判別
function import_else_data($item_id ){
	if ($_FILES['userdata']['name'] ) {
		$file_up = XOOPS_ROOT_PATH."/uploads/" .$_FILES['userdata']['name'] ;
		copy($_FILES['userdata']['tmp_name'] , $file_up );
		$main="開始匯入" . $file_up .'<br>';

		//副檔名
		$file_array= preg_split('/[.]/', $_FILES['userdata']['name'] ) ;
		$ext= strtoupper(array_pop($file_array)) ;
		if ($ext=='XLS')
			import_excel($item_id , $file_up) ;
		if ($ext=='XLSX')
			import_excel($item_id , $file_up , 2007) ;
		//刪除上傳的檔。
		unlink($file_up)  ;
	}
	return $main;
}

//excel 格式
function import_excel($item_id ,$file_up,$ver=5) {
    global $xoopsDB,$xoopsTpl ,$err_message  , $message ,  $DEF ;

	if ($ver ==5)
		$reader = PHPExcel_IOFactory::createReader('Excel5');
	else
		$reader = PHPExcel_IOFactory::createReader('Excel2007');

	$PHPExcel = $reader->load( $file_up ); // 檔案名稱
	$sheet = $PHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
	$highestRow = $sheet->getHighestRow(); // 取得總列數

    //0年級	1班級代號	2座號	3學生姓名	4繳費總額(整數)	5轉帳戶名	6轉帳戶身份證編號	7存款別(P/G)	8立帳局號	9存簿帳號	10劃撥帳號	11現金繳費(設為1)

	// 一次讀取一列
	for ($row = 2; $row <= $highestRow; $row++) {
		$v="";

		//讀取一列中的每一格
		for ($col = 0; $col <= 11; $col++) {
            $val =  $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
			if(!get_magic_quotes_runtime()) {
				$v[$col]=strtoupper(trim(addSlashes($val)));
			}else{
				$v[$col]= strtoupper(trim($val)) ;
			}
		}

		$line_str =   join( ',' , $v   )  ;
        //echo $line_str ."<br />" ;
        //

        if ( ! is_numeric($v[0]) )
            $v[0] = $DEF['class2id'][$v[0]] ;

        if (! is_numeric($v[1])  )
            $v[1]=  $DEF['class2id'][$v[1]] ;

        $class_id = $v[0]*100+$v[1] ;
        $class_id  =  sprintf("%03d" ,$class_id) ;
        $seat_id = $v[2] ;
        $stud_name = trim($v[3]) ;


        if ( ( $v[0]>=0   )  and  ( $v[1] >=1 )  and   ( $v[2]>=1)  and    ( $v[3] )   and    ( $v[4]>=0 )    )
            $ckeck1 = 'ok'     ;
        else {
            $err_message .=  " $line_str  必需有年、班、姓名、座號、繳費總額 <br/> " ;
            $ckeck1 = 'no'     ;
        }



		if ( (strlen($v[6] ) <>10) and   (strlen($v[6] ) <>0)  )
            $err_message .=  " $line_str 身份證證號長度不正確！<br/> " ;

        if ($ckeck1 == 'ok' ) {
			//帳號補 0
			$v[8] = sprintf("%07d", $v[8]) ;
			$v[9] = sprintf("%07d", $v[9]) ;
			$v[10] = sprintf("%014d", $v[10]) ;

            $stud_sn = 'E' .  sprintf("%03d" ,$class_id)  . sprintf("%02d" ,$seat_id) ;
            //自繳或無扣款資料
            if  ( ($v[11]) or ($v[6]=='' )  )
                $cash_fg =1 ;
            else
                $cash_fg =0 ;

			//寫入
            $sql = " INSERT INTO " .  $xoopsDB->prefix("charge_poster_data")
               ." (`item_id`, `t_id`, `class_id`, `sit_num`, `st_name`, `pay`, `acc_name`, `acc_personid`, `acc_mode`, `acc_b_id`, `acc_id`, `acc_g_id` , stud_else ,cash  )  "
               ."  VALUES ( '$item_id' , '$stud_sn'  , '$class_id' , '$seat_id' , '$stud_name'  , '{$v[4]}'   "
               ." , '{$v[5]}'   , '{$v[6]}'    , '{$v[7]}'   , '{$v[8]}'    , '{$v[9]}'    , '{$v[10]}'  , '1' , '$cash_fg'  ) ;   " ;

            $result = $xoopsDB->queryF($sql) or  $err_message .= $line_str  .  $xoopsDB->error()."(應該為班級座號重覆)<br />"  ;
			$update_ok_num ++ ;
        }


	}

    $message = "完成 $update_ok_num 筆更新<br/>"   ;
}


//清除這次郵局資料
function clear_poster_data($item_id){
    global   $xoopsDB  ;
    $sql = " DELETE FROM " .  $xoopsDB->prefix("charge_poster_data")  ." WHERE item_id = '$item_id'  "  ;
    $result = $xoopsDB->queryF($sql) ;
}



//對帳單 ，匯入純文字檔
function result_data($item_id ){
	global   $xoopsDB  ;
	if ($_FILES['result_data']['name'] ) {
		$file_up = XOOPS_ROOT_PATH."/uploads/" .$_FILES['result_data']['name'] ;
		copy($_FILES['result_data']['tmp_name'] , $file_up );
		//$main="開始匯入" . $file_up .'<br>';

		//失敗註記還原
		$sql =  " UPDATE  " . $xoopsDB->prefix("charge_poster_data") . " SET  pay_fail = '0'  	where      item_id = '$item_id'    ; " ;
		 $xoopsDB->queryF($sql) 	 ;


		//讀取文字檔 ，分行讀取
		$fp=fopen( $file_up ,"r");
		while(!feof($fp)){
			$mydata[] =fgets($fp );
		}
		fclose($fp);

		$pay_ok_num = 0 ;
		foreach ($mydata as $li =>$line)   {
			if ($line[0] ==1 ){
				//扣款金額
				$pay = substr($line,43,9);
				//失敗原因 01  or  10   ????
				$no_pay = substr($line,78,2);
				if ($no_pay<>'  '){
					$pay_err_sum += $pay ;
					//身份証、局號、帳號
					$person_id =   substr($line,33,10);
					$acc_bid= substr($line,19,7);
					$id= substr($line,26,7);
					//echo "$person_id $acc_bid $id ---  $no_pay <br />" ;
					//只以身份證做判別
					$sql =  " UPDATE  " . $xoopsDB->prefix("charge_poster_data") . " SET  pay_fail =  '$no_pay'
                        where  acc_personid  = '$person_id'  and  acc_b_id='$acc_bid'  and acc_id='$id'   and   item_id = '$item_id'    ; " ;
					 $xoopsDB->queryF($sql) 	 ;
				}else
					$pay_ok_num ++ ;
				$pay_num ++ ;
				$pay_sum += $pay ;

			}

			if ($line[0] ==2 )	{
				break ;
			}

		}
		$main .= "應扣款筆數 $pay_num 筆， 應扣款額額：$pay_sum 元  。   成功扣款： $pay_ok_num 筆 ，成功扣款總額： " . (  $pay_sum- $pay_err_sum )  ." 元 ";

		//把總筆數及總扣款數寫入
		$chk_sum = $pay_sum- $pay_err_sum ;
	    $sql = " update     ".  $xoopsDB->prefix("charge_item")
	            ." SET  `c_rec_num`= '$pay_ok_num'  ,`c_sum`= '$chk_sum'  "
				."  where  item_id='$item_id'     " ;
		$result = $xoopsDB->queryF($sql)   ;

		//刪除上傳的檔。
		unlink($file_up)  ;
	}
	return $main;
}

/*-----------執行動作判斷區----------*/
$item_id=empty($_REQUEST['item_id'])?"":$_REQUEST['item_id'];

//轉帳表
switch ($_POST["do_key"]){
    case "add":
        add_from_charge($_POST['item_id'] ) ;
    break;
    case "add_other":
        import_else_data($_POST['item_id'] ) ;
    break;
    case "clear":
        clear_poster_data($_POST['item_id'] ) ;
    break;
    case "export":
        export_poster_data($_POST['item_id'] ) ;
    break;
}

//對帳表
switch ($_POST["do_key2"]){
    case "result_upload":
        $message2 = result_data($_POST['item_id'] ) ;
    break;
    case "result_stud":
        export_fail($_POST['item_id'] ) ;
    break;
}

/*----------取得資料區--------------*/
//取得目前可填收費表
$data['item_list']=get_item_list('all') ;

 if  (!$item_id) {
	//選定最近的工作表
	$key = array_keys($data['item_list'])  ;
	$item_id=$key[1] ;
 }

 $data['select_item'] = $item_id  ;

if ($item_id ) {
    //取得各項統計資料
	$data['total'] = get_poster_stud_num($item_id) ;
	//
	$pr = get_poster_chare_num($item_id) ;
	$data['p_text'] = "扣款記錄  $pr 筆 * 手續費 {$DEF['fee']}  +  總計: {$data['total']['pay']['pm'][0]} = "   .( $pr *  $DEF['fee'] + $data['total']['pay']['pm'][0]  ) ;

	//扣款失敗人數
	$data['fail_studs'] =get_poster_chare_fail($item_id) ;
	//在記錄中已有扣款單、對帳單
	$data['item']= get_item_data($item_id) ;

}







/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "data" , $data ) ;
$xoopsTpl->assign( "message2" , $message2 ) ;
$xoopsTpl->assign( "err_message" , $err_message ) ;
$xoopsTpl->assign( "DEF" , $DEF ) ;

include_once 'footer.php';

?>
