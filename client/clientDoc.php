<?php

include("alloc.inc");

$file = urldecode($_GET["file"]);

if ($_GET["clientID"] && is_numeric($_GET["clientID"]) && $file && !preg_match("/\.\./",$file)) {

  $file = $TPL["url_alloc_clientDocs_dir"].$_GET["clientID"]."/".$file;

  if (file_exists($file) && is_writeable($file)) {
    $fp = fopen($file, "rb");
    header('Content-Type: application/octet-stream');
    header("Content-Length: ".filesize($file));
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    fpassthru($fp);
    exit;
  }
}



?>
