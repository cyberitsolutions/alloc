<?php
class tfList_home_item extends home_item {
  function tfList_home_item() {
    home_item::home_item("", "TF", "home", "tfListH.tpl", "narrow");
  }


  function show_tfList($template_name) {
    global $TPL, $current_user;

    $db = new db_alloc;
    $q = sprintf("SELECT * FROM tfPerson WHERE personID = %d",$current_user->get_id());
    $db->query($q);

    while ($db->next_record()) {
      $tf = new tf;
      $tf->set_id($db->f("tfID"));
      $tf->select();
      $tf->set_tpl_values();

      if (have_entity_perm("transaction", PERM_READ, $current_user, $tf->is_owner())) {
        $TPL["tfBalance"] = number_format($tf->get_balance(), 2);
        $grand_total += $tf->get_balance();
      } else {
        $TPL["tfBalance"] = "not available";
      }
      $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";

      $nav_links = $tf->get_nav_links();
      $TPL["data"] = format_nav_links($nav_links);
      include_template($template_name);
    }

    $TPL["grand_total"] = number_format($grand_total, 2);

  }




}



?>
