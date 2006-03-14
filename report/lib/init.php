<?php
class report_module extends module {
  function register_toolbar_items() {
    global $current_user, $auth;
    if (isset($current_user) && $current_user->is_employee() && has_report_perm()) {
      register_toolbar_item("report", "Reports");
    } else {
      register_toolbar_item("search", "Search");
    }
  }
}

function has_report_perm() {
  global $auth;
  if (is_object($auth)) {
    $permissions = explode(",", $auth->auth["perm"]);
    if (in_array("admin", $permissions) || in_array("god", $permissions)) {
      return true;
    }
  }
  return false;
}





?>
