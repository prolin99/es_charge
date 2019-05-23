<?php
use XoopsModules\Tadtools\Utility;

function xoops_module_install_es_charge(&$module) {

	Utility::mk_dir(XOOPS_ROOT_PATH."/uploads/es_charge");

	return true;
}



?>
