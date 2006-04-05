<?php
class quick_links_home_item extends home_item {
  function quick_links_home_item() {
    home_item::home_item("quick_links", "Quick Links", "home", "quickLinksH.tpl", "narrow");
  }

  function show_links($template_name) {
    global $toolbar_items, $TPL;
    reset($toolbar_items);
    while (list(, $item) = each($toolbar_items)) {
      $TPL["link_url"] = $item->get_url();
      $TPL["link_label"] = $item->get_label();
      include_template($this->get_template_dir().$template_name, $this);
    }
  }
}



?>
