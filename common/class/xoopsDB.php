<?php
//資料庫物件
class xoopsDB{
  var $conn;
  function xoopsDB(){
   $this->conn =  mysql_connect(XOOPS_DB_HOST,XOOPS_DB_USER,XOOPS_DB_PASS);
   mysql_select_db(XOOPS_DB_NAME);
   $this->query("SET NAMES '" . XOOPS_DB_CHARSET . "'");
  }

  function prefix($table_name=""){
    $table_name=XOOPS_DB_PREFIX."_".$table_name;
    return $table_name;
  }

  function query($sql=""){
    $result=mysql_query($sql,$this->conn) ;
    return $result;
  }

  function queryF($sql=""){
    $result=mysql_query($sql,$this->conn) ;
    return $result;
  }

  function fetchRow($result=""){
    return @mysql_fetch_row($result);
  }

  function fetchArray($result=""){
    return @mysql_fetch_assoc($result);
  }

    function fetchBoth($result){
        return @mysql_fetch_array($result, MYSQL_BOTH);
    }

    function fetchObject($result){
        return @mysql_fetch_object($result);
    }

    function getInsertId(){
        return mysql_insert_id($this->conn);
    }

    function getRowsNum($result){
        return @mysql_num_rows($result);
    }


    function getAffectedRows(){
        return mysql_affected_rows($this->conn);
    }


    function close(){
        mysql_close($this->conn);
    }


    function freeRecordSet($result){
        return mysql_free_result($result);
    }


    function error(){
        return @mysql_error();
    }


    function errno(){
        return @mysql_errno();
    }


    function quoteString($str){
        return $this->quote($str);
    }

    function quote($string){
        return "'" . str_replace("\\\"", '"', str_replace("\\&quot;", '&quot;', mysql_real_escape_string($string, $this->conn))) . "'";
    }

    function getFieldName($result, $offset){
        return mysql_field_name($result, $offset);
    }


    function getFieldType($result, $offset){
        return mysql_field_type($result, $offset);
    }


    function getFieldsNum($result){
        return mysql_num_fields($result);
    }
}
?>