<?php
include("alloc.inc");

page_close();

$first_toolbar_item = $toolbar_items[0];
$url = $first_toolbar_item->get_url();
header("Location: ".$url);



?>
