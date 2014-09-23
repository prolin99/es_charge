<?php
//  ------------------------------------------------------------------------ //
// 本模組由 prolin 製作
// 製作日期：2014-02-16
// $Id:$
// ------------------------------------------------------------------------- //

//---基本設定---//

$modversion['name'] ='學生收費管理';					//模組名稱
$modversion['version']	= '1.5';						//模組版次
$modversion['author'] = 'prolin(prolin@tn.edu.tw)';		//模組作者
$modversion['description'] ='收費管理，需配合學生名單模組';	//模組說明
$modversion['credits']	= 'prolin';						//模組授權者
$modversion['license']		= "GPL see LICENSE";		//模組版權
$modversion['official']		= 0;							//模組是否為官方發佈1，非官方0
$modversion['image']		= "images/logo.png";			//模組圖示
$modversion['dirname'] = basename(dirname(__FILE__));	//模組目錄名稱

//---模組狀態資訊---//
//$modversion['status_version'] = '0.8';
$modversion['release_date'] = '2014-04-01';
$modversion['module_website_url'] = 'https://github.com/prolin99/es_charge';
$modversion['module_website_name'] = 'prolin';
$modversion['module_status'] = 'release';
$modversion['author_website_url'] = 'http://www.syps.tn.edu.tw';
$modversion['author_website_name'] = 'prolin';
$modversion['min_php']= 5.2;



//---啟動後台管理界面選單---//
$modversion['system_menu'] = 1;//---資料表架構---//
$modversion['sqlfile']['mysql'] = "sql/mysql.sql";
$modversion['tables'][1] = "charge_item";
$modversion['tables'][2] = "charge_detail";
$modversion['tables'][3] = "charge_record";
$modversion['tables'][4] = "charge_decrease";

//---管理介面設定---//
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";
 
//---使用者主選單設定---//
$modversion['hasMain'] = 1;

//---安裝設定---//
$modversion['onUpdate'] = "include/onUpdate.php";


//---樣板設定---要有指定，才會編譯動作，//
$modversion['templates'] = array();
$i=1;
$modversion['templates'][$i]['file'] = 'es_admin_index_tpl.html';
$modversion['templates'][$i]['description'] = 'es_admin_index_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_admin_dcrease_tpl.html';
$modversion['templates'][$i]['description'] = 'es_admin_dcrease_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_admin_bank_tpl.html';
$modversion['templates'][$i]['description'] = 'es_admin_bank_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_admin_recip_tpl.html';
$modversion['templates'][$i]['description'] = 'es_admin_recipt_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_admin_sum_class_tpl.html';
$modversion['templates'][$i]['description'] = 'es_admin_sum_class_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_admin_sum_detail_tpl.html';
$modversion['templates'][$i]['description'] = 'es_admin_sum_detail_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_admin_class_detail_tpl.html';
$modversion['templates'][$i]['description'] = 'es_admin_class_detail_tpl.html';


$i++ ;
$modversion['templates'][$i]['file'] = 'es_index_tpl.html';
$modversion['templates'][$i]['description'] = 'es_index_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_decrease_tpl.html';
$modversion['templates'][$i]['description'] = 'es_decrease_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_report_tpl.html';
$modversion['templates'][$i]['description'] = 'es_report_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_report_html_tpl.html';
$modversion['templates'][$i]['description'] = 'es_report_html_tpl.html';
$i++ ;
$modversion['templates'][$i]['file'] = 'es_sum_tpl.html';
$modversion['templates'][$i]['description'] = 'es_sum_tpl.html';

$i++ ;
$modversion['templates'][$i]['file'] = 'es_decrease_table_tpl.html';
$modversion['templates'][$i]['description'] = 'es_decrease_table_tpl.html';
 
$i=0 ;
//偏好設定


$i++ ;
$modversion['config'][$i]['name'] = 'es_charge_decrease_cause';
$modversion['config'][$i]['title']   = '_MI_ESCHARGE_CONFIG_TITLE4';
$modversion['config'][$i]['description'] = '_MI_ESCHARGE_CONFIG_DESC4';
$modversion['config'][$i]['formtype']    = 'textarea';
$modversion['config'][$i]['valuetype']   = 'text';
$modversion['config'][$i]['default'] ="無\r\n低收入戶\r\n中低收入戶\r\n家境貧困及家庭突遭變故(導師家訪認定)\r\n原住民\r\n重度以上身心障礙學生或身心障礙人士之子女\r\n中度以下身心障礙學生或身心障礙人士之子女" ;

$i++ ;

$modversion['config'][$i]['name'] = 'es_charge_ps';
$modversion['config'][$i]['title']   = '_MI_ESCHARGE_CONFIG_TITLE2';
$modversion['config'][$i]['description'] = '_MI_ESCHARGE_CONFIG_DESC2';
$modversion['config'][$i]['formtype']    = 'textarea';
$modversion['config'][$i]['valuetype']   = 'text';
$modversion['config'][$i]['default'] ="特殊身份者才能勾選[申請補助]\r\n<br /><br />無需繳費的減免(如家長費不用繳)，不要勾選[申請補助] \r\n<br /><br /> \r\n 各項減免需要附上當年度本市身份證明文書。 \r\n<br /><br /> \r\n 點選項目輸入金額或清空。<br /><br /> \r\n 如果有多項身份別，請在該項補助後再指定其他減免身份  " ;
$i++ ;
$modversion['config'][$i]['name'] = 'es_charge_default_detail';
$modversion['config'][$i]['title']   = '_MI_ESCHARGE_CONFIG_TITLE3';
$modversion['config'][$i]['description'] = '_MI_ESCHARGE_CONFIG_DESC3';
$modversion['config'][$i]['formtype']    = 'textarea';
$modversion['config'][$i]['valuetype']   = 'text';
$modversion['config'][$i]['default'] ="01_學生團體保險_158,158,158,158,158,158	\r\n02_教科書_476,462,524,512,532,444	\r\n03_家長會費x_100,100,100,100,100,100	\r\n04_午餐費_550,550,550,550,550,550	\n05_合作社代辦費x_270,230,170,230,260,220	\r\n06_校外教學&畢業旅行_450,0,0,0,0,2600        " ;
?>