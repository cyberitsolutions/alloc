<?php
class top_ten_tasks_home_item extends home_item {
  var $date;

  function top_ten_tasks_home_item() {
    home_item::home_item("top_ten_tasks", "Top Five Tasks", "project", "topTenTasksH.tpl");
  }

  function show_tasks() {
    global $current_user, $tasks_date;

    $task_filter = new task_filter;
    $task_filter->set_element("person", $current_user);
    $task_filter->set_element("completed", false);
    $task_list = new prioritised_task_list($task_filter, 5);
    echo $task_list->get_task_summary(array("show_project_short"=>true, "status_type"=>"brief", "show_links"=>true,"skip_indent"=>true), false);
  }
}



?>
