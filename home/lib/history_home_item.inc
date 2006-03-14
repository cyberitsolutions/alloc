<?php
class history_home_item extends home_item {
  function history_home_item() {
    home_item::home_item("history", "History", "home", "historyH.tpl", "narrow");
  }

  function show_links($template_name) {
    global $TPL, $current_user, $sess;
    $display_num = 15;
    $db = new db_alloc;
    $query = sprintf("SELECT *
					  FROM history WHERE personID = %d
					  order by the_time", $current_user->get_id());
    $db->query($query);

    if ($db->num_rows() > $display_num) {
      $start = $db->num_rows() - $display_num;
    } else {
      $start = 0;
    }

    if ($db->num_rows() != 0) {
      $db->seek($start);
    }

    while ($db->next_record()) {
      $start++;
      $the_place = explode("/", $db->f("the_place"));
      $the_place = substr(end($the_place), 0, 30);
      $TPL["link_label"] = $the_place;
      $TPL["link_url"] = $sess->url($db->f("the_place"));
      $TPL["number"] = $start.".  ";
      $TPL["the_time"] = $db->f("the_time");

      include_template($this->get_template_dir().$template_name, $this);
    }
  }
}



?>
