<?php
class top_ten_tasks_home_item extends home_item {
  var $date;

  function top_ten_tasks_home_item() {
    home_item::home_item("top_ten_tasks", "Top Five Tasks", "project", "topTenTasksH.tpl");
  }

  function show_tasks() {
    global $current_user, $tasks_date;

    $options["taskView"] = "prioritised";
    $options["projectType"] = "mine";
    $options["personID"] = $current_user->get_id();
    $options["taskStatus"] = "not_completed";
    $options["limit"] = 5;
    $options["showDate1"] = true;
    $options["showDate2"] = true;
    $options["showHeader"] = true;
    $options["showProject"] = true;

    echo task::get_task_list($options);
  }
}



?>
