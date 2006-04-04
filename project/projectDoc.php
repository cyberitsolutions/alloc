<?php

include("alloc.inc");

$file = urldecode($_GET["file"]);

if ($_GET["projectID"] && is_numeric($_GET["projectID"]) && $file && !preg_match("/\.\./",$file)) {

  $p = new project;
  $p->set_id($_GET["projectID"]);
  $p->select();

  $file = $TPL["url_alloc_projectDocs_dir"].$_GET["projectID"]."/".$file;

  if ($p->has_project_permission($current_user) && file_exists($file) && is_writeable($file)) {
    $fp = fopen($file, "rb");
    header('Content-Type: application/octet-stream');
    header("Content-Length: ".filesize($file));
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    fpassthru($fp);
    exit;
  }
}



?>
