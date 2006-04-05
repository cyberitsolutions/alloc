<?php
include(ALLOC_MOD_DIR."/project/lib/project.inc.php");
include(ALLOC_MOD_DIR."/project/lib/task.inc.php");
include(ALLOC_MOD_DIR."/project/lib/projectPerson.inc.php");
include(ALLOC_MOD_DIR."/project/lib/projectPersonRole.inc.php");
include(ALLOC_MOD_DIR."/project/lib/projectModificationNote.inc.php");
include(ALLOC_MOD_DIR."/project/lib/projectCommissionPerson.inc.php");
include(ALLOC_MOD_DIR."/project/lib/taskType.inc.php");
include(ALLOC_MOD_DIR."/project/lib/taskComment.inc.php");
include(ALLOC_MOD_DIR."/project/lib/taskCommentTemplate.inc.php");


class project_module extends module
{
  var $db_entities = array("project"
                         , "task"
                         , "projectPerson"
                         , "projectModificationNote"
                         , "projectCommissionPerson"
                         , "taskType"
                         , "taskCommentTemplate"
                         );

  function register_toolbar_items() {
    global $current_user;

    if (have_entity_perm("task", PERM_READ, $current_user, true)) {
      register_toolbar_item("taskSummary", "Tasks");
    }

    register_toolbar_item("projectList", "Projects");

    // if (have_entity_perm("task", PERM_READ, $current_user, false)) {
    // register_toolbar_item("personGraphs", "Person Graphs");
    // }
  }

  function register_home_items() {
    global $current_user;

    include(ALLOC_MOD_DIR."/project/lib/tasks_completed_today_home_item.inc.php");
    // include announcements here so that is comes earlier in the list
    include(ALLOC_MOD_DIR."/announcement/lib/announcements_home_item.inc.php");
    include(ALLOC_MOD_DIR."/project/lib/project_list_home_item.inc.php");
    include(ALLOC_MOD_DIR."/project/lib/top_ten_tasks_home_item.inc.php");

    if (have_entity_perm("task", PERM_READ_WRITE, $current_user, true)) {
      register_home_item(new top_ten_tasks_home_item());
      flush();
    }
    $announcement = new announcement;
    if ($announcement->has_announcements()) {
      register_home_item(new announcements_home_item());
    }
    include(ALLOC_MOD_DIR."/project/lib/task_graph_home_item.inc.php");
    register_home_item(new task_graph_home_item());
    register_home_item(new project_list_home_item());
    if (have_entity_perm("task", PERM_READ_WRITE, $current_user, true)) {
      // register_home_item(new tasks_completed_today_home_item());
    }
    if ($this->has_messages()) {
      include(ALLOC_MOD_DIR."/project/lib/task_message_list_home_item.inc.php");
      register_home_item(new task_message_list_home_item());
    }
  }

  function has_messages() {
    global $current_user;
    if ($current_user && TT_MESSAGE) {
      $db = new db_alloc;
      $query = "SELECT * 
                  FROM task 
                 WHERE taskTypeID = ".TT_MESSAGE." 
                   AND personID = ".$current_user->get_id(). " 
                   AND (dateActualCompletion = '' OR dateActualCompletion IS NULL)";
      $db->query($query);
      if ($db->next_record()) {
        return true;
      }
    }
    return false;
  }


}




?>
