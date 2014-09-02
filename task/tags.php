<?php

require_once("../alloc.php");

$db = new db_alloc();
$db->query("SELECT DISTINCT name FROM tag");
while ($row = $db->row()) {
  $data[] = $row["name"];
}

if(isset($_GET['term'])) {
  $result = array();
  foreach($data as $key => $value) {
    if(strlen($_GET['term']) == 0 || strpos(strtolower($value), strtolower($_GET['term'])) !== false) {
      $result[] = '{"id":"'.$key.'","label":"'.$value.'","value":"'.$value.'"}';
    }
  }
  echo "[".implode(',', $result)."]";
}

?>
