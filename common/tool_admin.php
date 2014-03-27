<?php
if(file_exists("../../../mainfile.php")){
  include_once "../../../include/cp_header.php";
  include_once "../common/tool_xoops.php";
}else{
  include_once "../common/tool_php.php";
  include_once XOOPS_ROOT_PATH."/language/{$xoopsConfig['language']}/admin.php";

  if(!isAdmin()){
    header("location:".XOOPS_URL."/index.php");
  }
}
?>