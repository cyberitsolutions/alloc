<?php
include("alloc.inc");

$current_user->check_employee();

$tf_filter = new tf_filter();
if (isset($apply_filter)) {
  // Read filter values from form
  $tf_filter->read_form();
} else {
  // Default filter values
  $tf_filter->set_element("owner", $current_user);
}

$TPL["filter_form"] = $tf_filter->get_form();

// global $grand_total;
// echo "hey" .$grand_total;

include_template("templates/tfListM.tpl");

function show_tf($template_name) {
  global $TPL, $tf_filter;

  $tf_list = new tf_list($tf_filter);
  $tfs = $tf_list->get_entity_array();

  while (list(, $tf) = each($tfs)) {
    $tf->set_tpl_values();

    if (have_entity_perm("transaction", PERM_READ, $current_user, $tf->is_owner())) {
      $TPL["tfBalance"] = number_format($tf->get_balance(), 2);
      $grand_total += $tf->get_balance();
    } else {
      $TPL["tfBalance"] = "not available";
    }
    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";

    $nav_links = $tf->get_nav_links();
    $TPL["nav_links"] = format_nav_links($nav_links);
    include_template($template_name);
  }

  $TPL["grand_total"] = number_format($grand_total, 2);

}

page_close();



?>
