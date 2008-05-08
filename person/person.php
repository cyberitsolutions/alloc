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

require_once("../alloc.php");

  function show_perm_select() {
    global $person;
    if ($person->have_perm(PERM_PERSON_WRITE_ROLES)) {
      $selected = explode(",",$person->get_value("perms"));
      $ops = role::get_roles_array("person");
      foreach ($ops as $p => $l) {
        unset($sel);
        in_array($p,$selected) and $sel = " checked";
        echo $br."<input type=\"checkbox\" name=\"perm_select[]\" value=\"".$p."\"".$sel."> ".$l;
        $br = "<br>";
      }
    } else {
      $selected = explode(",",$person->get_value("perms"));
      $ops = role::get_roles_array("person");
      foreach ($selected as $sel) {
        echo $br.$ops[$sel];
        $br = "<br>";
      }
    }
  }

  function show_absence_forms($template) {
    global $personID;

    $db = new db_alloc;
    $query = sprintf("SELECT * FROM absence WHERE personID=%d", $personID);
    $db->query($query);
    $absence = new absence;
    while ($db->next_record()) {
      $absence->read_db_record($db);
      $absence->set_tpl_values(DST_HTML_ATTRIBUTE, "absence_");
      include_template($template);
    }
  }

  function show_action_buttons() {
    global $person, $TPL;

    echo "<input type=\"submit\" name=\"save\" value=\"&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;\">";

    if ($person->have_perm(PERM_DELETE)) {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
      echo "<input type=\"submit\" name=\"delete\" value=\"Delete Record\" onClick=\"return confirm('Deleting users may have adverse affects on any projects/tasks/transactions/etc that the user was associated with. It may be preferable to simply disable the user account. Are you sure you want to delete this user?')\">"; 
    } 

    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<a href=\"".$TPL["url_alloc_personList"]."\">Return to Person List</a>"; 
  }

  function include_employee_fields() {
    global $person;
    show_skills_list();
    include_template("templates/personEmployeeFieldsS.tpl");
  }

  function include_employee_skill_fields() {
    global $person;
    include_template("templates/personEmployeeSkillFieldsS.tpl");
  }

  function show_person_areasOfExpertise($template) {
    global $TPL, $personID, $skill_header, $skill_prof, $skills_got;

    $TPL["personExpertiseItem_buttons"] = "<input type=\"submit\" name=\"personExpertiseItem_save\" value=\"Save\">
            <input type=\"submit\" name=\"personExpertiseItem_delete\" value=\"Delete\">";
    $proficiencys = array("Novice"=>"Novice", "Junior"=>"Junior", "Intermediate"=>"Intermediate", "Advanced"=>"Advanced", "Senior"=>"Senior");

    # step through the list of skills ordered by skillclass
    $db = new db_alloc;
    // $query = "SELECT * FROM skillList ORDER BY skillClass,skillName";
    $query = "SELECT * FROM skillList LEFT JOIN skillProficiencys ON skillList.skillID=skillProficiencys.skillID";
    $query.= sprintf(" WHERE skillProficiencys.personID=%d", $personID);
    $query.= " ORDER BY skillClass,skillName";
    $db->query($query);
    $currSkillClass = null;
    while ($db->next_record()) {
      $skillList = new skillList;
      $skillList->read_db_record($db);
      $skillList->set_tpl_values();

      $skillPrificiencys = new skillProficiencys;
      $skillPrificiencys->read_db_record($db);
      $skillPrificiencys->set_tpl_values();

      # if tey do and there is no heading for this segment put a heading
      $thisSkillClass = $skillList->get_value('skillClass');
      if ($currSkillClass != $thisSkillClass) {
        $currSkillClass = $thisSkillClass;
        $skill_header = true;
      } else {
        $skill_header = false;
      }
      $skill_prof = $skillPrificiencys->get_value('skillProficiency');
      $TPL["skill_proficiencys"] = get_options_from_array($proficiencys, $skill_prof, true);

      # display rating if there is one
      include_template($template);
    }
  }

  function show_skills_list() {
    global $TPL, $personID, $skills;

    $db = new db_alloc;
    $query = sprintf("SELECT * FROM skillProficiencys WHERE personID=%d", $personID);
    $db->query($query);
    $skills_got = array();
    while ($db->next_record()) {
      $skillList = new skillList;
      $skillList->read_db_record($db);
      array_push($skills_got, $skillList->get_id());
    }
    $query = "SELECT * FROM skillList ORDER BY skillClass";
    $db->query($query);
    while ($db->next_record()) {
      $skillList = new skillList;
      $skillList->read_db_record($db);
      if (in_array($skillList->get_id(), $skills_got)) {
        // dont show this item
      } else {
        $skills[$skillList->get_id()] = sprintf("%s - %s", $skillList->get_value('skillClass'), $skillList->get_value('skillName'));
      }
    }
    if (count($skills) > 0) {
      $TPL["skills"] = get_options_from_array($skills, "", true);
    }
  }

  function check_optional_person_skills_header() {
    global $skill_header;
    return $skill_header;
  }

  function include_management_fields() {
    global $person;
    if ($person->have_perm(PERM_PERSON_READ_MANAGEMENT)) {
      include_template("templates/personManagementFieldsS.tpl");
    }
  }

$skill_header = false;
$person = new person;

$personID = $_POST["personID"] or $personID = $_GET["personID"];

if ($personID) {
  $person->set_id($personID);
  $person->select();
}
    

if ($_POST["personExpertiseItem_add"] || $_POST["personExpertiseItem_save"] || $_POST["personExpertiseItem_delete"]) {
  $skillProficiencys = new skillProficiencys;
  $skillProficiencys->read_globals();


  if ($_POST["skillID"] != null) {
    if ($_POST["personExpertiseItem_delete"]) {
      $skillProficiencys->delete();
    } else if ($_POST["personExpertiseItem_save"]) {
      $skillProficiencys->save();
    } else if ($_POST["personExpertiseItem_add"]) {
      // skillID is an array if when adding but not when saving or deleting
      $skillProficiency = $skillProficiencys->get_value('skillProficiency');
      for ($i = 0; $i < count($_POST["skillID"]); $i++) {
        $skillProficiencys = new skillProficiencys;

        $skillProficiencys->set_value('skillID', $_POST["skillID"][$i]);
        $skillProficiencys->set_value('skillProficiency', $_POST["skillProficiency"]);
        $skillProficiencys->set_value('personID', $personID);

        $db = new db_alloc;
        $query = "SELECT * FROM skillProficiencys WHERE personID = $personID";
        $query.= sprintf(" AND skillID = %d", $_POST["skillID"][$i]);
        $db->query($query);
        if (!$db->next_record()) {
          $skillProficiencys->save();
        }
      }
    }
  }
}

if ($_POST["save"]) {
  $person->read_globals();

  if ($person->can_write_field("perms")) {
    $_POST["perm_select"] or $_POST["perm_select"] = array();
    $person->set_value("perms", implode(",", $_POST["perm_select"]));
  }

  if ($_POST["password1"] && $_POST["password1"] == $_POST["password2"]) {
    $person->set_value('password', encrypt_password($_POST["password1"]));

  } else if (!$_POST["password1"] && $personID) {
    // nothing required here, just don't update the password field

  } else {
    $TPL["message"][] = "Please re-type the passwords";
  }


  if ($_POST["username"]) {
    $q = sprintf("SELECT personID FROM person WHERE username = '%s'",db_esc($_POST["username"]));
    $db = new db_alloc();
    $db->query($q);
    $num_rows = $db->num_rows();
    $row = $db->row();

    if (($num_rows > 0 && !$person->get_id()) || ($num_rows > 0 && $person->get_id() != $row["personID"])){
      $TPL["message"][] = "That username is already taken. Please select another.";
    }
  } else {
    $TPL["message"][] = "Please enter a username.";
  }

  $person->set_value("personActive", $_POST["personActive"] ? 1 : "0");

  $max_alloc_users = get_max_alloc_users();
  if ($max_alloc_users && get_num_alloc_users() >= $max_alloc_users && $_POST["personActive"]) {
    $TPL["message"][] = get_max_alloc_users_message();
  }

  if (!$TPL["message"]) {
    $person->set_value("availability",rtrim($person->get_value("availability")));
    $person->set_value("areasOfInterest",rtrim($person->get_value("areasOfInterest")));
    $person->set_value("comments",rtrim($person->get_value("comments")));
    $person->set_value("emergencyContact",rtrim($person->get_value("emergencyContact")));
    $person->set_value("managementComments",rtrim($person->get_value("managementComments")));
    $person->save();
    header("Location: ".$TPL["url_alloc_personList"]);
  }



} else if ($_POST["delete"]) {
  $person->delete();
  header("Location: ".$TPL["url_alloc_personList"]);
}

#$person = new person;
#$person->set_id($personID);
#$person->select();
$person->set_tpl_values(DST_HTML_ATTRIBUTE, "person_");

if ($person->get_id()) {
  $db_tf = new db_alloc;
  $db_tf->query("SELECT tf.tfName as name, tfPerson.tfID as id FROM tf, tfPerson 
  				 WHERE tf.tfID = tfPerson.tfID AND tfPerson.personID = ".$person->get_id()." AND tf.status = 'active'");
  $TPL["preferred_tfID_options"] = get_options_from_db($db_tf, "name", "id", $person->get_value("preferred_tfID"));
  $tf = new tf;
  $tf->set_id($person->get_value("preferred_tfID"));
  $tf->select();
  // Need to show the person's TF even if it's disabled. 
  if ($person->get_value("preferred_tfID") && $tf->get_value("status") != 'active') {
    $TPL["preferred_tfID_options"].= get_option($tf->get_value("tfName"). " (disabled)", $tf->get_id(), true);
  }
}

$TPL["absence_url"] = $TPL["url_alloc_absence"]."personID=".$personID;
$TPL["personActive"] = (!$person->get_id() || $person->get_value("personActive")) ? " checked" : "";

if ($personID) {
  $TPL["main_alloc_title"] = "Person Details: " . $person->get_value("username")." - ".APPLICATION_NAME;
} else {
  $TPL["main_alloc_title"] = "New Person - ".APPLICATION_NAME;
}

include_template("templates/personM.tpl");

page_close();



?>
