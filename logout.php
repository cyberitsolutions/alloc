<?php
include("alloc.inc");

$auth->logout();
$url = $TPL["url_alloc_index"];

page_close();
header("Location: $url");



?>
