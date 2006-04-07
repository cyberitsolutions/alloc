<?php
define("NO_AUTH",true);
require_once("alloc.inc");

  // stats
$stats = new stats;
$msg = $stats->get_stats_for_email("text");

print "<pre>";
print_r($msg);
print "</pre>";

page_close();



?>
