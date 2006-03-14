<?php
class tasks_completed_today_home_item extends home_item {
  var $date;

  function tasks_completed_today_home_item() {
    home_item::home_item("tasks_completed_today", "Tasks Completed Today", "project", "tasksCompletedTodayH.tpl");

    global $task_date, $TPL;

    if (isset($task_date)) {
      $this->date = $task_date;
    } else {
      $this->date = mktime();
    }

    $TPL["prev_task_date"] = mktime(0, 0, 0, date("m", $this->date), date("d", $this->date) - 1, date("Y", $this->date));
    $TPL["next_task_date"] = mktime(0, 0, 0, date("m", $this->date), date("d", $this->date) + 1, date("Y", $this->date));
  }

  function show_tasks() {
    global $current_user, $tasks_date;

    $task_filter = new task_filter;
    $task_filter->set_element("person", $current_user);
    $task_filter->set_element("dateActualCompletion", date("Y-m-d", $this->date));
    $task_list = new task_list($task_filter);
    echo $task_list->get_task_summary("", false);
  }

  function get_title() {
    return "Tasks Completed ".date("Y-m-d (D)", $this->date);
  }
}



?>
