<?php
include("$MOD_DIR/project/lib/project.inc");
include("$MOD_DIR/project/lib/task.inc");
include("$MOD_DIR/project/lib/projectPerson.inc");
include("$MOD_DIR/project/lib/projectPersonRole.inc");
include("$MOD_DIR/project/lib/projectModificationNote.inc");
include("$MOD_DIR/project/lib/projectCommissionPerson.inc");
include("$MOD_DIR/project/lib/taskType.inc");
include("$MOD_DIR/project/lib/taskComment.inc");
include("$MOD_DIR/project/lib/taskCommentTemplate.inc");


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
    global $MOD_DIR, $current_user;

    include("$MOD_DIR/project/lib/tasks_completed_today_home_item.inc");
    // include announcements here so that is comes earlier in the list
    include("$MOD_DIR/announcement/lib/announcements_home_item.inc");
    include("$MOD_DIR/project/lib/project_list_home_item.inc");
    include("$MOD_DIR/project/lib/top_ten_tasks_home_item.inc");

    if (have_entity_perm("task", PERM_READ_WRITE, $current_user, true)) {
      register_home_item(new top_ten_tasks_home_item());
      flush();
    }
    $announcement = new announcement;
    if ($announcement->has_announcements()) {
      register_home_item(new announcements_home_item());
    }
    include("$MOD_DIR/project/lib/task_graph_home_item.inc");
    register_home_item(new task_graph_home_item());
    register_home_item(new project_list_home_item());
    if (have_entity_perm("task", PERM_READ_WRITE, $current_user, true)) {
      // register_home_item(new tasks_completed_today_home_item());
    }
    if ($this->has_messages()) {
      include("$MOD_DIR/project/lib/task_message_list_home_item.inc");
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
