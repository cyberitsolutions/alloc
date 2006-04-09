<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

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
