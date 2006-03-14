<?php
include("alloc.inc");
$current_user->check_employee();
include_template("templates/exp-fixed-repeatM.tpl");
page_close();



?>
