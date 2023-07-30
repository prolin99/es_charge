<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2020-03-04
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
use XoopsModules\Tadtools\Utility;


include_once "header.php";


$xoopsOption['template_main'] = "es_kw_join.tpl";
include_once XOOPS_ROOT_PATH."/header.php";

/*
if (!$DEF['bank_account_use']) {
    echo '未使用郵局扣款！' ;
    exit() ;
}
*/

//把  ONLY_FULL_GROUP_BY 移除
$sql = " SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')); "  ;
$xoopsDB->queryF($sql)   ;


require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel.php'; //引入 PHPExcel 物件庫
require_once XOOPS_ROOT_PATH . '/modules/tadtools/vendor/phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php'; //引入PHPExcel_IOFactory 物件庫

/*-----------function區--------------*/


//額外線費學生資料 匯入判別  ---------------------------------------------------------------------------------
function import_else_data()
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
function import_excel($item_id, $file_up, $ver=2007)
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


    //資料庫中學生列表
    $stud_data_list = get_studdata_list() ;

    //0報名編號	1社團年度	2社團名稱	3社團學費	4額外費用	5請輸入學生身分證號或居留證號	6學生姓名	7年級	8班級	9家長姓名	10家長聯絡電話	11報名日期

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

            if (  ( is_numeric($v[0]) )  and  (   $v[3]>0  )  and   (   $v[4]>=0   )  and    ($v[5])   and    ($v[6])   and    ($v[7])  and    ($v[8])                          ) {
                $ckeck1 = 'ok'     ;
            } else {
                $err_message .=  " $line_str  必需有年、班、姓名、座號、繳費總額 <br/> " ;
                $ckeck1 = 'no'     ;
            }



            if ((strlen($v[5]) <>10) and   (strlen($v[5]) <>0)) {
                $err_message .=  " $line_str 身份證證號長度不正確！<br/> " ;
            }


            if ($ckeck1 == 'ok') {

                //如果繳費中已有，做加總
                if ( $data_line[$v[5]][3] ) {
                    //echo $data_line[$v[5]][3] ;
                    $data_line[$v[5]][4] += $v[3] ;
                    $data_line[$v[5]][12] = $data_line[$v[5]][12] . ' + ' . $v[2] ;

                }else {

                    //如果有身份證號 (有取得學生資料)
                    if ( $stud_data_list[$v[5]] ) {
                        $on_stud= $stud_data_list[$v[5]] ;
                        $data_line[$v[5]][0] = floor($on_stud['class_id']/100);
                        $data_line[$v[5]][1] = $on_stud['class_id']-floor($on_stud['class_id']/100)*100;
                        $data_line[$v[5]][2] = $on_stud['class_sit_num'];
                        $data_line[$v[5]][3] = $on_stud['name'];
                        $data_line[$v[5]][4] = $v[3] ;
                        $data_line[$v[5]][5] = $on_stud['acc_name'];
                        $data_line[$v[5]][6] = $on_stud['acc_person_id'];
                        $data_line[$v[5]][7] = $on_stud['acc_mode'];
                        $data_line[$v[5]][8] = $on_stud['acc_b_id'];
                        $data_line[$v[5]][9] = $on_stud['acc_id'];
                        $data_line[$v[5]][10] = $on_stud['acc_g_id'];
                        $data_line[$v[5]][11] = 0 ;
                        $data_line[$v[5]][12] = $v[7].$v[8].$v[6] . "({$v[9]})" . $v[2] ;
                    }else {
                        //echo $v[6] .$v[7].$v[8] ."<br>" ;

                        //無扣款帳號（幼兒園）
                        $on_stud= $stud_data_list[$v[5]] ;
                        $data_line[$v[5]][0] = 0;
                        $data_line[$v[5]][1] = 0;
                        $data_line[$v[5]][2] = 0;
                        $data_line[$v[5]][3] = $v[6];
                        $data_line[$v[5]][4] = $v[3] ;
                        $data_line[$v[5]][5] = '';
                        $data_line[$v[5]][6] =  '';
                        $data_line[$v[5]][7] = '';
                        $data_line[$v[5]][8] = '';
                        $data_line[$v[5]][9] = '';
                        $data_line[$v[5]][10] ='' ;
                        $data_line[$v[5]][11] = 0 ;
                        $data_line[$v[5]][12] = '***' . $v[7].$v[8].$v[6] . "({$v[9]})" .'--'. $v[2] ;
                    }
                }

                $update_ok_num ++ ;
            }
        }//此列有內容
    }

    //匯出成 外部扣款 excel

    export_excel($data_line) ;
    var_dump($data_line ) ;

}


function export_excel($data){
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);  //設定預設顯示的工作表
    $objActSheet = $objPHPExcel->getActiveSheet(); //指定預設工作表為 $objActSheet
    $objActSheet->setTitle('外部扣款名單');  //設定標題

    $row = 1;
    //標題行 //0年級	1班級代號	2座號	3學生姓名	4性別	5學號	6純特戶	7轉帳戶名	8轉帳戶身份證編號	9存款別	10立帳局號	11存簿帳號	12劃撥帳號	13電話號碼	14地址	15身份別
    //0年級	1班級代號	2座號	3學生姓名	4繳費 	5轉帳戶名	6轉帳戶身份證編號	7存款別(P/G)	8立帳局號	9存簿帳號	10劃撥帳號
    $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A'.$row, '年級')
        ->setCellValue('B'.$row, '班級代號')
        ->setCellValue('C'.$row, '座號')
        ->setCellValue('D'.$row, '學生姓名')
        ->setCellValue('E'.$row, '繳')
        ->setCellValue('F'.$row, '轉帳戶名')
        ->setCellValue('G'.$row, '轉帳戶身份證編號')
        ->setCellValue('H'.$row, '存款別')
        ->setCellValue('I'.$row, '立帳局號')
        ->setCellValue('J'.$row, '存簿帳號')
        ->setCellValue('K'.$row, '劃撥帳號')
        ->setCellValue('L'.$row, '自繳現金')
        ->setCellValue('M'.$row, '備註')
         ;
    foreach ($data as $stud)     {
        ++$row;
        $y = $stud[0];
        $c = $stud[1]  ;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$row, $y)
            ->setCellValue('B'.$row, $c)
            ->setCellValue('C'.$row, $stud[2])
            ->setCellValue('D'.$row, $stud[3])
            ->setCellValue('E'.$row,  $stud[4])
            ->setCellValue('F'.$row, $stud[5])
            ->setCellValue('G'.$row, $stud[6])
            ->setCellValue('H'.$row, $stud[7])
            ->setCellValue('I'.$row, $stud[8])
            ->setCellValue('J'.$row, $stud[9])
            ->setCellValue('K'.$row, $stud[10])
            ->setCellValue('L'.$row, $stud[11])
            ->setCellValue('M'.$row, $stud[12])
            ;

        $objPHPExcel->setActiveSheetIndex(0)->getStyle('I'.$row)->getNumberFormat()->setFormatCode('0000000');
        $objPHPExcel->setActiveSheetIndex(0)->getStyle('J'.$row)->getNumberFormat()->setFormatCode('0000000');
		$objPHPExcel->setActiveSheetIndex(0)->getStyle('K'.$row)->getNumberFormat()->setFormatCode('00000000000000');
    }

    //header('Content-Type: application/vnd.ms-excel');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename=扣款'.date('mdHi').'.xlsx');
    header('Cache-Control: max-age=0');
    ob_clean();

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}

/*-----------執行動作判斷區----------*/


//轉帳表
switch ($_POST["do_key"]) {

    case "add_other":
        import_else_data() ;
    break;

    break;
}


//取得學生資料和可扣款資料
function get_studdata_list(){
    global $xoopsDB ;
    $sql = ' SELECT a.* , b.class_id , b.class_sit_num , b.name ,b.person_id  From '.$xoopsDB->prefix('charge_account').' as a , '
        .$xoopsDB->prefix('e_student').' as  b '.
        '  where a.stud_sn  = b.stud_id  order by class_id,class_sit_num  ';

    $result = $xoopsDB->query($sql);
    while ($stud = $xoopsDB->fetchArray($result)) {
        ++$row;

        $data[$stud['person_id']] = $stud ;
    }
    return $data ;
}

/*----------取得資料區--------------*/
//取得目前可填收費表






/*-----------秀出結果區--------------*/
$xoopsTpl->assign( "toolbar" , Utility::toolbar_bootstrap($interface_menu)) ;
$xoopsTpl->assign( "data" , $data ) ;



include_once XOOPS_ROOT_PATH.'/footer.php';
