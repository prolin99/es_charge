<?php

// $Id: list.php 5310 2009-01-10 07:57:56Z hami $
include "config.php";
include "my_fun.php";

sfs_check();

//秀出網頁
head("收費管理(導師版)");
//橫向選單標籤
$linkstr="item_id=$item_id";
echo print_menu($MENU_P,$linkstr);

if($m_arr[is_list] AND $class_id) {
	
echo <<<HERE
<script>
function tagall(status) {
  var i =0;
  while (i < document.myform.elements.length)  {
    if (document.myform.elements[i].name=='selected_stud[]') {
      document.myform.elements[i].checked=status;
    }
    i++;
  }
}
</script>
HERE;

//學期別
$work_year_seme = sprintf("%03d%d",curr_year(),curr_seme());
$item_id=$_REQUEST[item_id];
$stud_class=$_POST[stud_class];
$selected_stud=$_POST[selected_stud];
//print_r($selected_stud);
//取得目前班級id
$grade+=$class_data[2];
//取出班級名稱陣列
$class_base = class_base($work_year_seme);

 

if (strstr($class_base[$class_id],'忠') ) 
  $spe_class= true ;
$class_year = substr($class_id ,0,1) ;


if($selected_stud AND $_POST['act']=='開列選擇的學生'){
	if($item_id<>'' AND $class_id<>'')
	{
		//抓取選擇的班級學生
		$batch_value="";
		foreach($selected_stud as $stud_datas)
		{
			$stud_data=explode(',',$stud_datas);
			$sn=$stud_data[0];
			$record_id=$work_year_seme.$stud_data[1];
			$batch_value.="('$record_id',$sn,$item_id),";
		}
		$batch_value=substr($batch_value,0,-1);
		//echo "===================<BR>$batch_value<BR>===================";
		$sql_select="REPLACE INTO charge_record(record_id,student_sn,item_id) values $batch_value";
		$res=$CONN->Execute($sql_select) or user_error("讀取失敗！<br>$sql_select",256);
	} else echo "<script language=\"Javascript\"> alert (\"資訊不足, 無法身分別批次新增！\")</script>";
};

if($_POST['act']=='清除本班級未繳款的名單'){
	$sql_select="delete from charge_record where item_id=$item_id AND record_id like '$work_year_seme$class_id%' AND dollars=0";
	$res=$CONN->Execute($sql_select) or user_error("讀取失敗！<br>$sql_select",256);
};
$main="<table border='1' cellpadding='3' cellspacing='0' style='border-collapse: collapse' bordercolor='#AAAAAA' width='100%'><form name='myform' method='post' action='$_SERVER[PHP_SELF]'>
	選擇繳費：<select name='item_id' onchange='this.form.submit()'><option></option>";


//取得年度項目
$sql_select="select * from charge_item where cooperate=1 AND year_seme='$work_year_seme' AND (curdate() between start_date AND end_date) order by end_date desc";
$res=$CONN->Execute($sql_select) or user_error("讀取失敗！<br>$sql_select",256);
while(!$res->EOF) {
	$main.="<option ".($item_id==$res->fields[item_id]?"selected":"")." value=".$res->fields[item_id].">".$res->fields[item]."(".$res->fields[start_date]."~".$res->fields[end_date].")</option>";
	$res->MoveNext();
}

$main.="</select>";
if($item_id)
{
	if($class_id)
	{
		//取得前已開列學生資料
		$sql_select="select * from charge_record where item_id=$item_id AND record_id like '$work_year_seme$class_id%' order by record_id";
		$recordSet=$CONN->Execute($sql_select) or user_error("讀取失敗！<br>$sql_select",256);
		$listed=array();
		while(!$recordSet->EOF)
		{
			$listed[$recordSet->fields[student_sn]]=$recordSet->fields[dollars];
			$recordSet->MoveNext();
		}

		//取得stud_base中班級學生列表並據以與前sql對照後顯示
                if ($spe_class) 
                   $stud_select="SELECT student_sn,curr_class_num,spe_sit_num as class_no,stud_name,stud_sex FROM stud_base WHERE stud_study_cond=0 AND curr_class_num like '$class_year%' and spe_sit_num >0  ORDER BY spe_sit_num"; 
                else  
		   $stud_select="SELECT student_sn,curr_class_num,right(curr_class_num,2) as class_no,stud_name,stud_sex FROM stud_base WHERE stud_study_cond=0 AND curr_class_num like '$class_id%' and spe_sit_num is null ORDER BY curr_class_num";
		$recordSet=$CONN->Execute($stud_select) or user_error("讀取失敗！<br>$stud_select",256);
		//以checkbox呈現
		$col=7; //設定每一列顯示幾人
		$studentdata="";
		while(list($student_sn,$curr_class_num,$class_no,$stud_name,$stud_sex)=$recordSet->FetchRow()) {
                        $curr_class_num = $class_id *100+ $class_no ; 
			if($recordSet->currentrow() % $col==1) $studentdata.="<tr>";
			if (array_key_exists($student_sn,$listed)) {
    				$studentdata.="<td bgcolor=".($listed[$recordSet->fields[student_sn]-1]?"#CCCCCC":"#FFFFDD").">▲($class_no)$stud_name</td>";
			} else {
				$studentdata.="<td bgcolor=".($stud_sex==1?"#CCFFCC":"#FFCCCC")."><input type='checkbox' name='selected_stud[]' value='$student_sn,$curr_class_num' id='stud_selected'>($class_no)$stud_name</td>";
			}
			if($recordSet->currentrow() % $col==0  or $recordSet->EOF) $studentdata.="</tr>";
			//echo "<BR>$curr_class_num === $stud_name";
		}
		$studentdata.="<tr height='50'><td align='right' colspan=$col>▲：已開列　　<input type='button' name='all_stud' value='全選' onClick='javascript:tagall(1);'><input type='button' name='clear_stud'  value='全不選' onClick='javascript:tagall(0);'>　";
		$studentdata.="<input type='submit' value='開列選擇的學生' name='act'>";
		$studentdata.="　<input type='submit' value='清除本班級未繳款的名單' name='act' onclick='return confirm(\"確定要\"+this.value+\"?\\n\\nPS.繳費紀錄亦會一併被刪除\")'></td></tr>";
	}
}
echo $main.$studentdata."</form></table>\n 
<font color=blue>基本上全班學生都開列，除非該學生在這次的轉帳的各種項目費用都無關才不需開列。</font>
";
} else echo $not_allowed;
 
foot();
?>
