<?php
require_once("alloc.inc");

$sess->Destroy();
$url = $TPL["url_alloc_index"];
page_close();
header("Location: $url");



?>
