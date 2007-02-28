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

define("PERM_PERSON_READ_DETAILS", 256);
define("PERM_PERSON_READ_MANAGEMENT", 512);
define("PERM_PERSON_WRITE_MANAGEMENT", 1024);
define("PERM_PERSON_WRITE_ROLES", 2048);
define("PERM_PERSON_SEND_EMAIL", 4096);

class person extends db_entity {
  var $classname = "person";
  var $data_table = "person";
  var $display_field_name = "username";
  var $prefs = array();

  function person() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("personID");
    $this->data_fields = array("username"=>new db_field("username")
                               , "lastLoginDate"=>new db_field("lastLoginDate")
                               , "password"=>new db_field("password", array("allow_null"=>false, "read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "perms"=>new db_field("perms", array("write_perm_name"=>PERM_PERSON_WRITE_ROLES))
                               , "emailAddress"=>new db_field("emailAddress")
                               , "emailFormat"=>new db_field("emailFormat", array("read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "availability"=>new db_field("availability", array("read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "areasOfInterest"=>new db_field("areasOfInterest", array("read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "comments"=>new db_field("comments", array("read_perm_name"=>PERM_PERSON_READ_DETAILS))
                               , "managementComments"=>new db_field("managementComments", array("read_perm_name"=>PERM_PERSON_READ_MANAGEMENT, "write_perm_name"=>PERM_PERSON_WRITE_MANAGEMENT))
                               , "firstName"=>new db_field("firstName")
                               , "surname"=>new db_field("surname")
                               , "preferred_tfID"=>new db_field("preferred_tfID")
                               , "dailyTaskEmail"=>new db_field("dailyTaskEmail")
                               , "personActive"=>new db_field("personActive")
                               , "sessData"=>new db_field("sessData")
                               , "phoneNo1"=>new db_field("phoneNo1")
                               , "phoneNo2"=>new db_field("phoneNo2")
      );

    $this->permissions[PERM_PERSON_READ_DETAILS] = "Read details";
    $this->permissions[PERM_PERSON_READ_MANAGEMENT] = "Read management fields";
    $this->permissions[PERM_PERSON_WRITE_MANAGEMENT] = "Write management fields";
    $this->permissions[PERM_PERSON_WRITE_ROLES] = "Set roles";
    $this->permissions[PERM_PERSON_SEND_EMAIL] = "Send mail-outs";
  }

  function get_tasks_for_email() {

    $format = "text";

    $options = array();
    $options["projectType"] = "mine";
    $options["limit"] = 3;
    $options["current_user"] = $this->get_id();
    $options["personID"] = $this->get_id();
    $options["taskView"] = "prioritised";
    $options["return"] = $format;
    $options["taskStatus"] = "not_completed";
    $options["taskTypeID"] = array(TT_TASK,TT_MESSAGE,TT_FAULT,TT_MILESTONE);

    $summary = task::get_task_list($options);

    if ($summary) {
      $topThree = "\n\nTop Three Tasks";
      $topThree.= $summary;
    } 

    unset($summary);
    unset($options["limit"]);
    $options["taskStatus"] = "due_today";
    $summary = task::get_task_list($options);

    if ($summary) {
      $dueToday = "\n\nTasks Due Today";
      $dueToday.= $summary;
    } 

    unset($summary);
    unset($options["limit"]);
    $options["taskStatus"] = "new";
    $summary = task::get_task_list($options);

    if ($summary) {
      $newTasks = "\n\nNew Tasks";
      $newTasks.= $summary;
    } 

    return $topThree.$dueToday.$newTasks;
  }

  function get_announcements_for_email() {
    $db = new db_alloc;
    $db->query("SELECT * FROM announcement WHERE CURDATE() <= displayToDate AND CURDATE() >= displayFromDate");

    while ($db->next_record()) {
      $announcement["heading"] = "Announcement\n".$db->f("heading");
      $announcement["body"] = $db->f("body");
    }
    return $announcement;
  }

  function get_project_persons($extra_condition = "") {
    // Return an array of project_person objects that this user is associated with
    // $extra_condition: A SQL expression added to the WHERE clause of the query
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
    $perms = explode(",",$this->get_value("perms"));
    return (in_array("god",$perms) || in_array($perm_name,$perms));
  }

  function check_role($perm_name) {
    if (!$this->have_role($perm_name)) {
      die("Pemission denied");
    }
  }

  function is_employee() {
    // Function to check if the person is an employee
    global $current_user;
    return true;
    $permissions = explode(",", $current_user->get_value("perms"));

    if (in_array("employee", $permissions)) {
      return true;
    } else {
      return false;
    }
  }

  function check_employee() {
    // Ensure the current user is an employee
    if (!$this->is_employee()) {
      die("You must be an employee to access this function");
    }
  }

  function get_skills($proficiency) {
    // Return a string of skills with a given proficiency
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

  function get_tfIDs() {
    $db = new db_alloc;
    $db->query("SELECT tfID FROM tfPerson WHERE personID = %d",$this->get_id());
    while ($row = $db->row()) {
      $tfIDs[] = $row["tfID"];
    }
    return $tfIDs;
  }



  function get_valid_login_row($username, $password="") {
    $q = sprintf("SELECT * FROM person WHERE username = '%s'",db_esc($username));
    $db = new db_alloc;
    $db->query($q);
    $db->next_record();
    $salt = $db->f("password");

    $q = sprintf("SELECT * FROM person WHERE username = '%s' AND password = '%s'"
                ,db_esc($username),db_esc(crypt(trim($password), $salt)));

    $db->query($q);
    return $db->row();
  }

  function load_get_current_user($personID) {
    $current_user = new person;
    $current_user->set_id($personID);
    $current_user->select();
    $current_user->prefs = unserialize($current_user->get_value("sessData"));

    isset($current_user->prefs["topTasksNum"]) or $current_user->prefs["topTasksNum"] = 5;
    $current_user->prefs["topTasksStatus"] or $current_user->prefs["topTasksStatus"] = "not_completed";
    isset($current_user->prefs["projectListNum"]) or $current_user->prefs["projectListNum"] = "10";
    isset($current_user->prefs["tasksGraphPlotHome"]) or $current_user->prefs["tasksGraphPlotHome"] = "4";
    isset($current_user->prefs["tasksGraphPlotHomeStart"]) or $current_user->prefs["tasksGraphPlotHomeStart"] = "1";
    return $current_user;
  }

  function has_messages() {
    if (is_object($this)) {
      $db = new db_alloc;
      $query = "SELECT * 
                  FROM task 
                 WHERE taskTypeID = ".TT_MESSAGE." 
                   AND personID = ".$this->get_id(). " 
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
