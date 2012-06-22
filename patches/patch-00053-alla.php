<?php
function patchName_old_to_new($file) {
  if (preg_match("/patch-([0-9]*)(.*)/",$file,$m)) {
    #echo "<pre>".print_r($m,1)."</pre>";
    $new = "patch-".sprintf("%05d",$m[1]).$m[2];
    #echo "<br>New: ".$new;
    return $new;
  }
}

$dir = ALLOC_MOD_DIR."patches/";

$db = new db_alloc();
$db2 = new db_alloc();

$q = prepare("SELECT patchName,patchLogID FROM patchLog");
$db->query($q);

while ($row = $db->row()) {
  $new = patchName_old_to_new($row["patchName"]);
  if ($new) {
    $q = prepare("UPDATE patchLog SET patchName = '%s' WHERE patchLogID = %d",$new,$row["patchLogID"]);
    $db2->query($q);
    #echo "<br>".$q;
  }
}

if (is_dir($dir)) {
  $handle = opendir($dir);
  while (false !== ($file = readdir($handle))) {
    clearstatcache();

    if ($file != "." && $file != "..") {
      $new = patchName_old_to_new($file);
      if ($new) {
        rename($dir.$file,$dir.$new);
        #echo "<br><br>rename(${dir}${file},${dir}${new});";
      }
    }
  }
}




?>
