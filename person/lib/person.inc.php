<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
 * Pty. Ltd.
 * 
 * This file is part of the allocPSA application <info@cyber.com.au>.
 * 
 * allocPSA is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at
 * your option) any later version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public
 * License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with allocPSA. If not, see <http://www.gnu.org/licenses/>.
*/

define("PERM_PERSON_READ_DETAILS", 256);
define("PERM_PERSON_READ_MANAGEMENT", 512);
define("PERM_PERSON_WRITE_MANAGEMENT", 1024);
define("PERM_PERSON_WRITE_ROLES", 2048);
define("PERM_PERSON_SEND_EMAIL", 4096);

class person extends db_entity {
  public $classname = "person";
  public $data_table = "person";
  public $display_field_name = "username";
  public $key_field = "personID";
  public $data_fields = array("username"
                             ,"lastLoginDate"
                             ,"password"=>array("read_perm_name"=>PERM_PERSON_READ_DETAILS)
                             ,"perms"=>array("write_perm_name"=>PERM_PERSON_WRITE_ROLES)
                             ,"emailAddress"
                             ,"availability"=>array("read_perm_name"=>PERM_PERSON_READ_DETAILS)
                             ,"areasOfInterest"=>array("read_perm_name"=>PERM_PERSON_READ_DETAILS)
                             ,"comments"=>array("read_perm_name"=>PERM_PERSON_READ_DETAILS)
                             ,"managementComments"=>array("read_perm_name"=>PERM_PERSON_READ_MANAGEMENT
                                                         ,"write_perm_name"=>PERM_PERSON_WRITE_MANAGEMENT)
                             ,"firstName"
                             ,"surname"
                             ,"preferred_tfID"
                             ,"personActive"
                             ,"sessData"
                             ,"phoneNo1"
			                       ,"phoneNo2"
      			                 ,"emergencyContact"
                             );
  
  public $prefs = array();
  public $permissions = array(PERM_PERSON_READ_DETAILS => "Read details"
                             ,PERM_PERSON_READ_MANAGEMENT => "Read management fields"
                             ,PERM_PERSON_WRITE_MANAGEMENT => "Write management fields"
                             ,PERM_PERSON_WRITE_ROLES => "Set roles"
                             ,PERM_PERSON_SEND_EMAIL => "Send mail-outs");


  function get_tasks_for_email() {

    $format = "text";

    $options = array();
    #$options["projectType"] = "mine";
    $options["limit"] = 3;
    $options["current_user"] = $this->get_id();
    $options["personID"] = $this->get_id();
    $options["taskView"] = "prioritised";
    $options["return"] = $format;
    $options["taskStatus"] = "not_completed";
    $options["taskTypeID"] = array(TT_TASK,TT_MESSAGE,TT_FAULT,TT_MILESTONE);

    $summary = task::get_list($options);

    if ($summary) {
      $topThree = "\n\nTop Three Tasks";
      $topThree.= $summary;
    } 

    unset($summary);
    unset($options["limit"]);
    $options["taskStatus"] = "due_today";
    $summary = task::get_list($options);

    if ($summary) {
      $dueToday = "\n\nTasks Due Today";
      $dueToday.= $summary;
    } 

    unset($summary);
    unset($options["limit"]);
    $options["taskStatus"] = "new";
    $summary = task::get_list($options);

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
    $db = new db_alloc;
    $q = sprintf("SELECT * FROM person WHERE username = '%s' AND personActive = 1"
                 ,db_esc($username));

    $db->query($q);
    $row = $db->row();

    if (check_password($password, $row["password"])) {
      return $row;
    }
  }

  function load_get_current_user($personID) {
    $current_user = new person;
    $current_user->set_id($personID);
    if ($current_user->select()) {
      $current_user->load_prefs();
      return $current_user;
    }
  }

  function load_prefs() {
    $this->prefs = unserialize($this->get_value("sessData"));
    isset($this->prefs["topTasksNum"]) or $this->prefs["topTasksNum"] = 5;
    $this->prefs["topTasksStatus"] or $this->prefs["topTasksStatus"] = "not_completed";
    isset($this->prefs["projectListNum"]) or $this->prefs["projectListNum"] = "10";
    isset($this->prefs["tasksGraphPlotHome"]) or $this->prefs["tasksGraphPlotHome"] = "4";
    isset($this->prefs["tasksGraphPlotHomeStart"]) or $this->prefs["tasksGraphPlotHomeStart"] = "1";
    isset($this->prefs["receiveOwnTaskComments"]) or $this->prefs["receiveOwnTaskComments"] = "1";
  }

  function store_prefs() {
    $p = new person;
    $p->set_id($this->get_id());
    $p->select();
    $p->load_prefs();
  
    $old_prefs = $p->prefs or $old_prefs = array();
    foreach ($old_prefs as $k => $v) {
      if ($this->prefs[$k] != $v) {
        $save = true;
      }
    }
    foreach ($this->prefs as $k => $v) {
      if ($old_prefs[$k] != $v) {
        $save = true;
      }
    }

    if ($save || (!is_array($old_prefs) || !count($old_prefs))) {
      $arr = serialize($this->prefs);
      $p->set_value("sessData",$arr);
      $p->save();
    }

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

  function find_by_name($name=false) {

    $stack1 = array();
    $people = get_cached_table("person");
    foreach ($people as $personID => $row) {
      similar_text($row["name"],$name,$percent1);
      $stack1[$personID] = $percent1;
    }

    asort($stack1);
    end($stack1);
    $probable1_personID = key($stack1);
    $person_percent1 = current($stack1);

    if ($probable1_personID && $person_percent1 > 70) {
      return $probable1_personID;
    }
  }

  function find_by_email($email=false) {
    $email = str_replace(array("<",">"),"",$email);
    $people = get_cached_table("person");
    foreach ($people as $personID => $row) {
      if ($email == str_replace(array("<",">"),"",$row["emailAddress"])) {
        return $personID;
      }
    }
  }

  function get_from() {
    $name = $this->get_username(1);
    $email = $this->get_value("emailAddress");
    if ($email) {
      $str = $name;
      $str and $str.= " <";
      $str and $end = ">";
      return $str.$email.$end;
    }
  }

}



?>
