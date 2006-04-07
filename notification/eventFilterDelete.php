<?php
require_once("alloc.inc");

$eventFilter = new eventFilter();
$eventFilter->set_id($eventFilterID);
$eventFilter->select() || die("Could not load record");

$eventFilter->get_value("personID") == $current_user->get_id() || die("Permission denied");

$eventFilter->delete();

header("Location: ".$TPL["url_alloc_eventFilterList"]);



?>
