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

define("PERM_PERSON_READ_DETAILS", 256);
define("PERM_PERSON_READ_MANAGEMENT", 512);
define("PERM_PERSON_WRITE_MANAGEMENT", 1024);
define("PERM_PERSON_WRITE_ROLES", 2048);
define("PERM_PERSON_SEND_EMAIL", 4096);

class person extends db_entity
{
  var $classname = "person";
  var $data_table = "person";
  var $display_field_name = "username";
  var $prefs = array();

  function person() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("personID");
    $this->data_fields = array("username"=>new db_text_field("username", "User name", "")
                               , "lastLoginDate"=>new db_text_field("lastLoginDate", "Last login date", "".array("read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "password"=>new db_text_field("password", "Password", "", array("allow_null"=>false, "read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "perms"=>new db_text_field("perms", "Permissions", "", array("write_perm_name"=>PERM_PERSON_WRITE_ROLES))
                               , "emailAddress"=>new db_text_field("emailAddress", "Email address", "")
                               , "emailFormat"=>new db_text_field("emailFormat", "Email Format", "", array("read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "availability"=>new db_text_field("availability", "Availability", "", array("read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "areasOfInterest"=>new db_text_field("areasOfInterest", "Areas of interest", "", array("read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "comments"=>new db_text_field("comments", "Comments", "", array("read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "managementComments"=>new db_text_field("managementComments", "Management Comments", "", array("read_perm_name"=>PERM_PERSON_READ_MANAGEMENT, "write_perm_name"=>PERM_PERSON_WRITE_MANAGEMENT))
                               , "firstName"=>new db_text_field("firstName", "First name", "")
                               , "surname"=>new db_text_field("surname", "Surname", "")
                               , "preferred_tfID"=>new db_text_field("preferred_tfID", "Preferred Payment TF", "")
                               , "dailyTaskEmail"=>new db_text_field("dailyTaskEmail", "Daily Task Email", "")
                               , "personActive"=>new db_text_field("personActive", "Active", "")
                               , "sessData"=>new db_text_field("sessData", "Session Data", "")
      );

    $this->permissions[PERM_PERSON_READ_DETAILS] = "Read details";
    $this->permissions[PERM_PERSON_READ_MANAGEMENT] = "Read management fields";
    $this->permissions[PERM_PERSON_WRITE_MANAGEMENT] = "Write management fields";
    $this->permissions[PERM_PERSON_WRITE_ROLES] = "Set roles";
    $this->permissions[PERM_PERSON_SEND_EMAIL] = "Send mail-outs";
  }


/*
  function get_task_summary($existing_filter = "", $format = "html", $task_options = "", $applyDateRegex = true) {
    if ($existing_filter == "") {
      $filter = new task_filter();
    } else {
      $filter = $existing_filter;
    }

    $summary = "";
    $project_persons = $this->get_project_persons();

    reset($project_persons);
    while (list(, $project_person) = each($project_persons)) {
      $project = $project_person->get_foreign_object("project");

      if ($applyDateRegex && !$project_person->date_regex_matches()) {
        continue;
      }

      if ($project_person->get_value("emailType") == "Assigned Tasks") {
        $filter->set_element("person", $this);
      } else if ($project_person->get_value("emailType") == "All Tasks") {
        $filter->set_element("person", "");
      } else if ($project_person->get_value("emailType") == "None") {
        continue;
      } else {
        echo "Unexpected emailType value: ".$project_person->get_value("emailType")."\n";
      }
      $project_summary = $project->get_task_summary($filter, $task_options, false, $format, $this->get_id());
      if ($project_summary == "" && $project_person->get_value("emailEmptyTaskList")) {
        $project_summary = "No matching tasks\n";
      }

      if ($project_summary == "") {
        continue;
      }

      if ($format == "html") {
        $summary.= "<a href=\"".$project->get_url()."\"><strong>".$project->get_value("projectName")."</strong></a><br>\n";
      } else {
        $summary.= strtoupper($project->get_value("projectName"))." (".$project->get_url().")\n";
      }
      $summary.= $project_summary;
      if ($format == "html") {
        $summary.= "<br><br>";
      } else {
        $summary.= "\n";
      }
    }

    return $summary;
  }
*/



  function get_tasks_for_email() {
    global $person, $db;

    $format = $this->get_value("emailFormat");
    $task_filter = new task_filter();
    $task_filter->set_element("person", $person);
    $task_filter->set_element("in_progress", true);
    $task_filter->set_element("completed", false);
    $task_filter->set_element("project", true);
    $task_list = new prioritised_task_list($task_filter, 3);
    $summary = $task_list->get_task_summary(array("show_project"=>true, "status_type"=>"brief", "show_links"=>true), false, $format);
    if ($summary) {

      if ($format == "html") {
        $topThree = "<br><br><h4>TOP THREE TASKS</h4>";
      } else {
        $topThree = "\nTOP THREE TASKS";
      }
      $topThree.= $summary;

    } else {
      $topThree = false;
    }

    unset($summary);
    unset($task_filter);
    unset($task_list);

    $task_filter = new task_filter();
    $task_filter->set_element("person", $person);
    $task_filter->set_element("in_progress", true);
    $task_filter->set_element("completed", false);
    $task_filter->set_element("project", true);
    $task_filter->set_element("due_today", true);
    $task_list = new task_list($task_filter);
    $summary = $task_list->get_task_summary(array("show_project"=>true, "status_type"=>"brief", "show_links"=>true), false, $format);
    if ($summary) {

      if ($format == "html") {
        $dueToday = "<br><br><h4>TASKS DUE TODAY</h4>";
      } else {
        $dueToday = "\n\n- - - - - - - - - -\n\nTASKS DUE TODAY";
      }
      $dueToday.= $summary;

    } else {
      $dueToday = false;
    }

    unset($summary);
    unset($task_filter);
    unset($task_list);


    $task_filter = new task_filter();
    $task_filter->set_element("person", $person);
    $task_filter->set_element("in_progress", true);
    $task_filter->set_element("completed", false);
    $task_filter->set_element("project", true);
    $task_filter->set_element("new", true);
    $task_list = new task_list($task_filter);
    $summary = $task_list->get_task_summary(array("show_project"=>true, "status_type"=>"brief", "show_links"=>true), false, $format);
    if ($summary) {

      if ($format == "html") {
        $newTasks = "<br><br><h4>NEW TASKS</h4>";
      } else {
        $newTasks = "\n\n- - - - - - - - - -\n\nNEW TASKS";
      }

      $newTasks.= $summary;

    } else {
      $newTasks = false;
    }


    return $topThree.$dueToday.$newTasks;


  }


  function get_announcements_for_email() {
    $today = mktime(0, 0, 0, date(m), date(d), date(Y));
    $db = new db_alloc;
    $db->query("select * from announcement");

    while ($db->next_record()) {

      if ($today >= strtotime($db->f("displayFromDate")) && $today <= strtotime($db->f("displayToDate"))) {
        $announcement["heading"] = "ANNOUNCEMENT\n".$db->f("heading");
        $announcement["body"] = $db->f("body");
      } else {
        $announcement = false;
      }
    }
    // Return it in an array so that getting txt or html version can be decided in sendEmail.php 
    // rather than hitting this function 60 times (and thus DB). 
    return $announcement;
  }





  // Return an array of project_person objects that this user is associated with
  // $extra_condition: A SQL expression added to the WHERE clause of the query
  function get_project_persons($extra_condition = "") {
    $query = "SELECT * FROM projectPerson WHERE projectPerson.personID=".$this->get_id();
    if ($extra_condition) {
      $query.= " AND (".$extra_condition.")";
    }
    $db = new db_alloc;
    $db->query($query);
    $rtn = array();
    while ($db->next_record()) {
      $project_person = new projectPerson;
      $project_person->read_db_record($db);
      $rtn[] = $project_person;
    }

    return $rtn;
  }

  function have_role($perm_name) {
    $p = $this->get_value("perms");
    $p = ",$p,";
    return ereg(",$perm_name,", $p);
  }

  function check_role($perm_name) {
    if (!$this->have_role($perm_name)) {
      die("Pemission denied");
    }
  }

  // Convenience function to check if the person is an employee
  function is_employee() {
    global $current_user;
    return true;
    $permissions = explode(",", $current_user->get_value("perms"));

    if (in_array("employee", $permissions)) {
      return true;
    } else {
      return false;
    }
  }

  // Ensure the current user is an employee
  function check_employee() {
    if (!$this->is_employee()) {
      die("You must be an employee to access this function");
    }
  }

  // Return a string of skills with a given proficiency
  function get_skills($proficiency) {
    $query = "SELECT * FROM skillProficiencys LEFT JOIN skillList on skillProficiencys.skillID=skillList.skillID";
    $query.= sprintf(" WHERE personID=%d AND skillProficiency='%s' ORDER BY skillName", $this->get_id(), $proficiency);

    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $skillList = new skillList;
      $skillList->read_db_record($db);
      if ($rtn) {
        $rtn.= ", ";
      }
      $rtn.= $skillList->get_value('skillName');
    }
    return $rtn;
  }

  function get_username_list($push_personID="") {
    static $rows;

    // Cache rows
    if(!$rows) {
      $q = sprintf("SELECT personID, username, firstName, surname, personActive FROM person ORDER BY firstname,surname,username");
      $db = new db_alloc;
      $db->query($q);
      while($db->next_record()) {
        if ($db->f("firstName") && $db->f("surname")){
          $name = $db->f("firstName")." ".$db->f("surname");
        } else {
          $name = $db->f("username");
        }
      
        $rows[$db->f("personID")] = array("active"=>$db->f("personActive"), "name"=>$name);

      }
    }

    // If person is active or the person is the selected person (to enable drpodown list to have people who have since been made inactive)
    foreach ($rows as $personID => $info) {
      if ($info["active"] || $personID == $push_personID) {
        $rtn[$personID] = $info["name"];
      }
    }

    return $rtn;
  }

  // Static
  function get_fullname($personID) {
    // Get vars for the emails below
    $people_cache = get_cached_table("person");
    return $people_cache[$personID]["name"];

  } 

  function get_username($long_format=false) {
    
    // If id is a $person object
    if ($this->get_value("username")) {
      $firstName = $this->get_value("firstName");
      $surname   = $this->get_value("surname");
      $username  = $this->get_value("username");
    } 
    if ($long_format && $firstName && $surname){
      return $firstName." ".$surname;
    } else {
      return $username;
    }
  }

}

?>
