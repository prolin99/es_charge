<?php
use XoopsModules\Tadtools\Utility;

function xoops_module_uninstall_es_charge(&$module) {
	Utility::delete_directory(XOOPS_ROOT_PATH."/uploads/es_charge") ;
	return true;
}




?>
