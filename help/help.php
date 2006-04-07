<?php
require_once("alloc.inc");

function show_help_body() {
  global $topic, $module, $modules;

  // Security checks - do not allow arbitrary file access
  if (!(eregi("^[a-z0-9_]+$", $topic))) {
    echo "Invalid topic";
    return;
  }

  if (!isset($modules[$module])) {
    echo "Invalid module";
    return;
  }

  include_template("../$module/help/$topic.html");
}

include_template("templates/helpM.tpl");

page_close();



?>
