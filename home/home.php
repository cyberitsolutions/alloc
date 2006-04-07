<?php
require_once("alloc.inc");

function show_home_items($width) {
  global $home_items, $current_home_item, $TPL;

  $item_num = 0;
  reset($home_items);
  while (list(, $current_home_item) = each($home_items)) {
    if ($current_home_item->get_width() != $width) {
      continue;
    }

    $item_num++;
    $TPL["item_title"] = $current_home_item->get_title();
    include_template("templates/homeItemS.tpl");
  }
}

function show_item() {
  global $current_home_item;
  $current_home_item->show();
}

register_home_items();

include_template("templates/homeM.tpl");

page_close();



?>
