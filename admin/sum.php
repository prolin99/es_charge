<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-03-01
// $Id:$
// ------------------------------------------------------------------------- //
/*-----------引入檔案區--------------*/
include_once "header_admin.php";
//樣版
$xoopsOption['template_main'] = "es_admin_sum_class_tpl.html";
include_once "header.php";


include_once "../function.php";

/*-----------function區--------------*/


/*-----------執行動作判斷區----------*/
$item_id=empty($_REQUEST['item_id'])?"":$_REQUEST['item_id'];




/*----------取得資料區--------------*/
//取得中文班名
$data['class_list_c'] = es_class_name_list_c('long')  ;

//取得目前可填收費表
$data['item_list']=get_item_list('all') ;


 if  (!$item_id) {
	//選定最近的工作表
	$key = array_keys($data['item_list'])  ;
	$item_id=$key[1] ;
 }

 $data['select_item'] = $item_id  ;
//是否有在作業期間轉出要刪除的學生
$data['out_student']= chk_student_out($item_id , $class_id , 'all' ) ;


//全校各班人數
$sql = "SELECT  class_id   , count( * ) cc  FROM " . $xoopsDB->prefix("e_student") . "  group by   class_id   " ;
$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
while($row=$xoopsDB->fetchArray($result)){
	$data['students'][$row['class_id']]= $row['cc'] ;
}


//各班收費減免統計
$sql =  "  SELECT  curr_class_num  , sum(decrease_dollar) as decrease_sum  FROM " . $xoopsDB->prefix("charge_decrease")  .
			" where  item_id = '$item_id'     group by   curr_class_num  " ;
$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
while($data_row=$xoopsDB->fetchArray($result)){
	$data['decrease_sum'][$data_row['curr_class_num'] ] = $data_row['decrease_sum'] ;
	//全扣
	$data['all_decrease'] += $data_row['decrease_sum'] ;
}

//有繳費的班級及繳費人數
$sql =  "  SELECT  class_id  ,   count(*) as st_count  ,sum(dollars) as dollars_sum FROM " . $xoopsDB->prefix("charge_record")  .
	" where  item_id = '$item_id'     group by   class_id  order by class_id  " ;
$result = $xoopsDB->query($sql) or die($sql."<br>". $xoopsDB->error());
while($data_row=$xoopsDB->fetchArray($result)){
	$class_id = $data_row['class_id'] ;
  //每人原應繳
  $data_row['dollars'] =  $data_row['dollars_sum'] /$data_row['st_count'] ;

	$data['record'][$class_id] = $data_row ;
	$data['all_pay_st_num'] += $data_row['st_count']  ;
	$data['all_st_num'] += $data['students'][$class_id] ;

	$data['all_pay_dollars_sum'] += $data_row['st_count']  ;

	//實付
	$data['record'][$class_id]['dollars_pay'] =  $data_row['dollars_sum'] - $data['decrease_sum'][$class_id] ;
	$data['all_pay'] += $data['record'][$class_id]['dollars_pay']  ;

}

$data['all_class_count']  = count($data['record']) ;



/*-----------秀出結果區--------------*/

$xoopsTpl->assign( "data" , $data ) ;


include_once 'footer.php';

?>
