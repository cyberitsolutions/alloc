<?php
class timeSheet_filter extends db_filter {
  var $classname = "timeSheet_filter";

  function timeSheet_filter($filter_values = "") {
    $this->elements = array("status"=>new field_filter_element("status", "Status", "timeSheet.status")
                           ,"project"=>new foreign_key_filter_element("project", "Project", "timeSheet.projectID")
                           ,"user"=>new foreign_key_filter_element("user", "Person Responsible", "timeSheet.personID")
                           ,"person"=>new foreign_key_filter_element("person", "Send To", "personID")
      );
  }

  function get_form() {
    global $MOD_DIR, $TPL, $current_user, $timeSheetStatus;

    $timeSheetStatus = $this->get_element("status");
    $timeSheetStatusOptions = array("edit_to_manager"=>"Edit=> Manager",
                                    "edit_to_admin"=>"Edit=> Admin",
                                    "manager_to_admin"=>"Manager=> Admin", "admin_to_invoiced"=>"Admin=> Invoiced", "manager_to_edit"=>"Manager=> Edit", "admin_to_edit"=>"Admin=> Edit", "admin_to_manager"=>"Admin=> Manager", "invoiced_to_admin"=>"Invoiced=> Admin");
    $TPL["timeSheetStatusOptions"] = get_options_from_array($timeSheetStatusOptions, $timeSheetStatus);

    $project = $this->get_element("project");
    $projectID = is_object($project) ? $project->get_id() : 0;
    $person = $this->get_element("person");

    $selectDb = new db_alloc;
    // ATTENTION : should that be have entity perm for TASK?? if it should then delete this comment.
    if (have_entity_perm("task", PERM_READ, $current_user, false)) {
      $query = "SELECT personID, username FROM person ORDER BY username";
      $selectDb->query($query);

      $personSelect.= "<select name=\"personID\">";
      $personSelect.= "<option value=\"\"> -- ALL -- ";
      $personSelect.= get_options_from_db($selectDb, "username", "personID", is_object($person) ? $person->get_id() : 0);
      $personSelect.= "</select>";

      // Selects all projects, but places this user's projects at the top of the list
      $query = "SELECT project.projectID, projectName 
                  FROM project LEFT JOIN projectPerson 
                       ON project.projectID = projectPerson.projectID AND personID=".$current_user->get_id()."
                  ORDER BY projectPersonID IS NULL, projectName";
      $selectDb->query($query);
      $projectOptions = get_options_from_db($selectDb, "projectName", "projectID", $projectID);
    } else {
      $personSelect = $current_user->get_value("username")."<input type=\"hidden\" name=\"personID\" value=\"".$current_user->get_id()."\">";

      $query = "SELECT project.projectID, projectName 
                  FROM project, projectPerson 
                  WHERE project.projectID = projectPerson.projectID AND personID=".$current_user->get_id()."
                  ORDER BY projectName";
      $selectDb->query($query);
      $projectOptions = get_options_from_db($selectDb, "projectName", "projectID", $projectID);
    }

    $TPL["personSelect"] = $personSelect;
    $TPL["projectOptions"] = $projectOptions;

    return get_template_string($MOD_DIR."/time/templates/timeSheetFilterS.tpl");
  }

  function read_form() {
    global $current_user, $timeSheetStatus, $personID, $projectID;

    if (!have_entity_perm("timeSheet", PERM_READ, $current_user, false)) {
      $user = $current_user;
    } else if ($personID != "") {
      $user = new person;
      $user->set_id($personID);
      $user->select();
    }
    $this->set_element("user", $user);
    $this->set_element("person", $current_user);

    if (!isset($timeSheetStatus)) {
      $timeSheetStatus = "this_shouldnt_happen";
    }
    $this->set_element("status", $timeSheetStatus);

    if ($projectID) {
      $project = new project;
      $project->set_id($projectID);
      $this->set_element("project", $project);
    }
  }

  function check_object($o) {
    // status check
    if ($o->get_value("status") != $this->get_element("status")) {
      return false;
    }
    // user check
    $user = $this->get_element("user");
    if (is_object($user) && ($o->get_value("personID") != $user->get_id())) {
      return false;
    }
    // project check
    $project = $this->get_element("project");
    if (is_object($project) && ($o->get_value("projectID") != $project->get_id())) {
      return false;
    }

    return true;
  }

  function get_message($o) {
    global $HOSTNAME;

    $project = $this->get_element("project");
    if (!is_object($project)) {
      $project = new project;
    }

    $project->set_id($o->get_value("projectID"));
    $project->select();

    $proj_msg = "  for project : ".$project->get_value("projectName")."\n";

    $user = $this->get_element("user");
    if (!is_object($user)) {
      $user = new person;
      $user->set_id($o->get_value("personID"));
      $user->select();
    }
    $user_msg = "  from user   : ".$user->get_value("username")."\n";

    $link = "http://".$HOSTNAME."/alloc/time/timeSheet.php?timeSheetID=".$o->get_id();

    switch ($this->get_element("status")) {
    case "edit_to_manager":
      $msg = "Timesheet submitted to manager\n";
      break;
    case "edit_to_admin":
      $msg = "Timesheet submitted to admin\n";
      break;
    case "manager_to_admin":
      $msg = "Timesheet submitted to admin from manager\n";
      break;
    case "admin_to_invoiced":
      $msg = "Timesheet invoiced\n";
      break;
    case "manager_to_edit":
      $msg = "Timesheet returned from manager\n";
      break;
    case "admin_to_edit":
      $msg = "Timesheet returned from admin\n";
      break;
    case "admin_to_manager":
      $msg = "Timesheet returned from admin to manager\n";
      break;
    case "invoiced_to_admin":
      $msg = "Timesheet uninvoiced\n";
      break;
    }
    $msg.= $proj_msg.$user_msg."\n".$link;

    return $msg;
  }
}




?>
