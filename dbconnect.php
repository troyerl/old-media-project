<?php

include ("adodb5/adodb.inc.php");
include ("adodb5/adodb-exceptions.inc.php");

$DB = NewADOConnection("mysql");

try{
  $DB->Connect("localhost", "troyerl", "troyerl","troyerl_db");
} catch(Exception $e){
  print $e;
}

?>
