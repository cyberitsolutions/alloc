<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of allocPSA <info@cyber.com.au>.
 * 
 * allocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * allocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */


include(ALLOC_MOD_DIR."/task/lib/task.inc.php");
include(ALLOC_MOD_DIR."/task/lib/taskType.inc.php");
include(ALLOC_MOD_DIR."/task/lib/taskCommentTemplate.inc.php");
include(ALLOC_MOD_DIR."/task/lib/taskFilter.inc.php");
include(ALLOC_MOD_DIR."/task/lib/task_calendar.inc.php");


class task_module extends module
{
  var $db_entities = array("task"
                         , "taskType"
                         , "taskCommentTemplate"
                         );

  function register_home_items() {
    global $current_user;

    include(ALLOC_MOD_DIR."/task/lib/task_calendar_home_item.inc.php");
    register_home_item(new task_calendar_home_item());


    include(ALLOC_MOD_DIR."/task/lib/top_ten_tasks_home_item.inc.php");
    if (have_entity_perm("task", PERM_READ_WRITE, $current_user, true)) {
      register_home_item(new top_ten_tasks_home_item());
      flush();
    } 

    if ($current_user->has_messages()) {
      include(ALLOC_MOD_DIR."/task/lib/task_message_list_home_item.inc.php");
      register_home_item(new task_message_list_home_item());
    }



  }

}




?>
