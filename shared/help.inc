<?php
function get_help_button($topic, $module = "") {
  global $sess, $TPL;

  if ($module == "") {
    $module = ALLOC_MODULE_NAME;
  }

  if ($topic) {
    $url = $sess->url("../help/help.php?topic=$topic&module=$module");
  } else {
    $url = "../help/alloc_help.html#".$TPL["alloc_help_link_name"];
  }
  return "<a href=\"$url\" target=\"_blank\"><img src=\"../images/help.gif\" alt=\"help\" border=\"0\"></a>";
}

function help_button($topic = "", $module = "") {
  echo get_help_button($topic, $module);
}

function get_help_link() {
  global $TPL;
  $url = "../help/alloc_help.html#".$TPL["alloc_help_link_name"];
  echo "<a href=\"".$url."\">Help</a>";
}




?>
