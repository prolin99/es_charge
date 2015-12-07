<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
include_once "header_admin.php";
//樣版
$xoopsOption['template_main'] = "es_c_a_poster_tpl.html";
include_once "header.php";


include_once "../function.php";

include_once "../../tadtools/PHPExcel.php";
require_once '../../tadtools/PHPExcel/IOFactory.php';
/*-----------function區--------------*/



//匯入判別
function import_data(){

	if ($_FILES['userdata']['name'] ) {

		$file_up = XOOPS_ROOT_PATH."/uploads/" .$_FILES['userdata']['name'] ;
		copy($_FILES['userdata']['tmp_name'] , $file_up );
		$main="開始匯入" . $file_up .'<br>';

		//副檔名
		$file_array= preg_split('/[.]/', $_FILES['userdata']['name'] ) ;
		$ext= strtoupper(array_pop($file_array)) ;
		if ($ext=='XLS')
			import_excel($file_up) ;
		if ($ext=='XLSX')
			import_excel($file_up , 2007) ;
		//刪除上傳的檔。
		unlink($file_up)  ;
	}
	return $main;
}

//excel 格式
function import_excel($file_up,$ver=5) {

    global $xoopsDB,$xoopsTpl ,$message ,$data  , $class_sit_message ;



	if ($ver ==5)
		$reader = PHPExcel_IOFactory::createReader('Excel5');
	else
		$reader = PHPExcel_IOFactory::createReader('Excel2007');

	$PHPExcel = $reader->load( $file_up ); // 檔案名稱
	$sheet = $PHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
	$highestRow = $sheet->getHighestRow(); // 取得總列數

    //0年級	1班級代號	2座號	3學生姓名	4性別	5學號	6純特戶	7轉帳戶名	8轉帳戶身份證編號	9存款別	10立帳局號	11存簿帳號	12劃撥帳號	13電話號碼	14地址	15身份別
		//0年級	1班級代號	2座號	3學生姓名	4繳費 	5轉帳戶名	6轉帳戶身份證編號	7存款別(P/G)	8立帳局號	9存簿帳號	10劃撥帳號
	// 一次讀取一列
	for ($row = 2; $row <= $highestRow; $row++) {
		$v="";

		//讀取一列中的每一格
		for ($col = 0; $col <= 15; $col++) {
            $val =  $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
			if(!get_magic_quotes_runtime()) {
				$v[$col]=strtoupper(trim(addSlashes($val)));
			}else{
				$v[$col]= strtoupper(trim($val)) ;
			}

		}

		$line_str =   join( ',' , $v   )  ;
        $class_id = $v[0]*100+$v[1] ;
        $class_id  =  sprintf("%03d" ,$class_id) ;
        $seat_id = $v[2] ;
        $stud_name = trim($v[3]) ;



        if (( !$v[0]   )  or  ( !$v[1])  or   ( !$v[2])  or   ( !$v[3])   or    ( !$v[5]   )  or  ( !$v[6])  or   ( !$v[7])  or   ( !$v[8])  or ( !$v[9])   ) {
            $message .=  " $line_str 資料有缺少<br/> " ;
            $ckeck1 = 'no'     ;
        } else
            $ckeck1 = 'ok'     ;
		if (strlen($v[6] ) <>10)  {
            $message .=  " $line_str 身份證證號長度不正確！<br/> " ;
            $ckeck1 = 'no'     ;
        }

        if ($ckeck1 == 'ok' ) {
			//帳號補 0
			$v[8] = sprintf("%07d", $v[8]) ;
			$v[9] = sprintf("%07d", $v[9]) ;
			$v[10] = sprintf("%014d", $v[10]) ;

            $stud_sn='' ;
            //由學生資料中取得 單獨ID
            $sql = "  SELECT stud_id From "  . $xoopsDB->prefix("e_student") . " where `class_id`='$class_id' and
				`class_sit_num`='$seat_id' and ( `name`='$stud_name'   or  parent=   '$v[5]' )        ; " ;
            $result = $xoopsDB->query($sql)   ;
            while($stud=$xoopsDB->fetchArray($result)){
                $stud_sn = $stud['stud_id'] ;
            }

            if ($stud_sn ) {
				//---------------------有找到學生資料
                $sql = " SELECT stud_sn From "  . $xoopsDB->prefix("charge_account") . " where stud_sn ='$stud_sn' ; " ;

                $result = $xoopsDB->query($sql)   ;
                while($stud=$xoopsDB->fetchArray($result)){
                    $get_stud_sn = $stud['stud_sn'] ;
                }

				//更新或新增
                if ( $get_stud_sn==$stud_sn )
                    $sql=  " UPDATE  " . $xoopsDB->prefix("charge_account") . " SET  stud_name=  '{$v[3]}'  ,
                        `acc_name`= '{$v[5]}' , `acc_person_id`= '{$v[6]}' , `acc_mode`= '{$v[7]}' , `acc_b_id`= '{$v[8]}' , `acc_id`= '{$v[9]}' , `acc_g_id`= '{$v[10]}'
                        where stud_sn = '$stud_sn'  ; " ;
                else
                    $sql=  " INSERT INTO " . $xoopsDB->prefix("charge_account") .
			           "  (`a_id` , `stud_sn`, `stud_name`, `acc_name`, `acc_person_id`, `acc_mode`, `acc_b_id`, `acc_id`, `acc_g_id`  )
			            VALUES ('0',  '$stud_sn'  , '{$v[3]}' , '{$v[5]}' , '{$v[6]}' , '{$v[7]}' , '{$v[8]}' , '{$v[9]}' , '{$v[10]}'  )  ;" ;

			    $result = $xoopsDB->query($sql)  or      $message .= "語法錯誤：$sql <br/>" ;
				//echo $sql ."<br>" ;
				$update_ok_num ++ ;

            }else{
                $message .= " $class_id  班 $seat_id 號  $stud_name  ，無此人，檢查班級、座號、姓名是否相同 ---- $line_str <br />" ;
				$class_sit_message[$class_id][$seat_id] = $line_str ;
			}
        }
	}

    $message = "完成 $update_ok_num 筆更新<br/>" .$message ;



}



/*-----------執行動作判斷區----------*/
//匯入更新
if ( $_POST['do_key'] ) {
    import_data() ;
}

//清空資料表
if ( $_POST['do_clear'] =='clear') {
	$sql= " TRUNCATE " . $xoopsDB->prefix("charge_account")  ;
	$result = $xoopsDB->query($sql)  ;
}

//輸入 -單筆方式-----------------------------------------------
if ($_POST['do']== 'input')  {
	foreach ($_POST["acc_person_id"] as $sn =>$acc_person_id) {
		$check1='no' ;

		if (($_POST["acc_name"][$sn]) and  ($_POST["acc_person_id"][$sn])  and   ($_POST["acc_mode"][$sn]=='P')  and ($_POST["acc_b_id"][$sn])  and ($_POST["acc_id"][$sn])  )
			$check1 = 'ok' ;
		if (($_POST["acc_name"][$sn]) and  ($_POST["acc_person_id"][$sn])  and   ($_POST["acc_mode"][$sn]=='G')  and ($_POST["acc_g_id"][$sn])  )
			$check1 = 'ok' ;

		if ($check1=='ok')	{
			$acc_pid = strtoupper($_POST["acc_person_id"][$sn]) ;
			$acc_m =  strtoupper($_POST["acc_mode"][$sn]) ;
			if  ($acc_m<>'G') $acc_m ='P' ;
			$acc_b_id = sprintf("%07d", $_POST["acc_b_id"][$sn] ) ;
			$acc_id = sprintf("%07d" ,$_POST["acc_id"][$sn] ) ;
			$acc_g_id = sprintf("%07d", $_POST["acc_g_id"][$sn] ) ;


			$sql=  " INSERT INTO " . $xoopsDB->prefix("charge_account") .
				"  (`a_id` ,`stud_sn`, `stud_name`, `acc_name`, `acc_person_id`, `acc_mode`, `acc_b_id`, `acc_id`, `acc_g_id`  )
				VALUES ( '0' ,  '$sn'  , '{$_POST['st_name'][$sn]}' , '{$_POST['acc_name'][$sn]}' , '$acc_pid' ,
				'$acc_m'  , '$acc_b_id'  , '$acc_id' , '$acc_g_id'   )  ; " ;
			//echo $sql ."<br>" ;
			$result = $xoopsDB->query($sql)   ;
		}

	}

}



/*----------取得資料區--------------*/
//有使用郵局帳號才做
if ($DEF['bank_account_use']) {
	//目前已有扣款帳號
	$sql = " SELECT count(*)  as ss FROM  ". $xoopsDB->prefix("e_student")     ;
	$result = $xoopsDB->query($sql)   ;
	//echo $sql ;
	while($row=$xoopsDB->fetchArray($result)){
		$stud_num = $row['ss'] ;

	}

	//無帳號資料的學生筆數
	$sql = " SELECT count(*)  as ss FROM  ". $xoopsDB->prefix("e_student") . "  as a LEFT JOIN " . $xoopsDB->prefix("charge_account") .
			" as b on a.stud_id =b.stud_sn  WHERE acc_person_id IS NULL  order by  a.class_id, a.class_sit_num  "  ;
	$result = $xoopsDB->query($sql)   ;
	//echo $sql ;
	while($row=$xoopsDB->fetchArray($result)){
		$no_account = $row['ss'] ;
		$infomessage.= "全校學生數總數： $stud_num   , 有扣款帳號學生數：" . ($stud_num-$no_account ) ."  , 無扣款帳號學生數： $no_account  <br/>" ;
	}

	//如果未匯入就視為不使用
	$DEF['bank_account_use']=($stud_num-$no_account ) ;

	//無帳號資料的學生
	if  ($DEF['bank_account_use']) {
		$sql = " SELECT a.class_id, a.class_sit_num ,a.name, a.stud_id , b.* FROM  ". $xoopsDB->prefix("e_student") . "  as a "
			." LEFT JOIN " . $xoopsDB->prefix("charge_account") . " as b "
			. " on a.stud_id =b.stud_sn  WHERE acc_person_id IS NULL  order by  a.class_id, a.class_sit_num  "  ;
		$result = $xoopsDB->query($sql)   ;

		while($stud=$xoopsDB->fetchArray($result)){
			$data[$stud['stud_id']] = $stud ;
		}
	}
}
/*-----------秀出結果區--------------*/

$xoopsTpl->assign( "data" , $data ) ;
$xoopsTpl->assign( "message" , $message ) ;
$xoopsTpl->assign( "infomessage" , $infomessage ) ;
$xoopsTpl->assign( "class_sit_message" , $class_sit_message ) ;
$xoopsTpl->assign( "bank_account_use" , $DEF['bank_account_use'] ) ;

include_once 'footer.php';
