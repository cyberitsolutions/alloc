<?php
class task_message_list_home_item extends home_item {
  var $date;

  function task_message_list_home_item() {
    home_item::home_item("task_message_list_home_item", "Messages For You", "project", "taskMessageListH.tpl", "narrow");
  }

  function show_tasks() {
    global $current_user, $tasks_date;
    $q = sprintf("SELECT * 
                  FROM task 
                  WHERE (task.dateActualCompletion IS NULL AND task.taskTypeID = %d) 
                  AND (personID = %d) 
                  ORDER BY priority
                 ",TT_MESSAGE,$current_user->get_id());

    $db = new db_alloc;
    $db->query($q);

    while ($db->next_record()) {
      $task = new task;
      $task->read_db_record($db);
      echo $br.$task->get_task_link();
      $br = "<br/>";
    }
  }
}



?>
