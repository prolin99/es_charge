<?php
//模組物件
class xoopsModule{
  function xoopsModule(){

  }

  function getVar($col=""){
    global $modversion;
    if($col=="dirname"){
      $main=$modversion['dirname'];
    }
    return $main;
  }
}
?>