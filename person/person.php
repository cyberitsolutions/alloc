<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
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

    $db = new db_alloc();
    $query = prepare("SELECT * FROM absence WHERE personID=%d", $personID);
    $db->query($query);
    $absence = new absence();
    while ($db->next_record()) {
      $absence->read_db_record($db);
      $absence->set_values("absence_");
      include_template($template);
    }
  }

  function show_action_buttons() {
    global $person;
    global $TPL;
    if ($person->have_perm(PERM_DELETE)) {
      echo '<button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button> ';
    } 
    echo '<button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button> ';
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
    global $TPL;
    global $personID;
    global $skill_header;
    global $skill_prof;
    global $skills_got;

    $TPL["personExpertiseItem_buttons"] = '
       <button type="submit" name="personExpertiseItem_delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
       <button type="submit" name="personExpertiseItem_save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
         ';
    $proficiencys = array("Novice"=>"Novice", "Junior"=>"Junior", "Intermediate"=>"Intermediate", "Advanced"=>"Advanced", "Senior"=>"Senior");

    # step through the list of skills ordered by skillclass
    $db = new db_alloc();
    // $query = "SELECT * FROM skill ORDER BY skillClass,skillName";
    $query = "SELECT * FROM skill LEFT JOIN proficiency ON skill.skillID=proficiency.skillID";
    $query.= prepare(" WHERE proficiency.personID=%d", $personID);
    $query.= " ORDER BY skillClass,skillName";
    $db->query($query);
    $currSkillClass = null;
    while ($db->next_record()) {
      $skill = new skill();
      $skill->read_db_record($db);
      $skill->set_tpl_values();

      $skillProficiencys = new proficiency();
      $skillProficiencys->read_db_record($db);
      $skillProficiencys->set_values();

      # if they do and there is no heading for this segment put a heading
      $thisSkillClass = $skill->get_value('skillClass');
      if ($currSkillClass != $thisSkillClass) {
        $currSkillClass = $thisSkillClass;
        $skill_header = true;
      } else {
        $skill_header = false;
      }
      $skill_prof = $skillProficiencys->get_value('skillProficiency');
      $TPL["skill_proficiencys"] = page::select_options($proficiencys, $skill_prof);

      # display rating if there is one
      include_template($template);
    }
  }

  function show_skills_list() {
    global $TPL;
    global $personID;
    global $skills;

    $db = new db_alloc();
    $query = prepare("SELECT * FROM proficiency WHERE personID=%d", $personID);
    $db->query($query);
    $skills_got = array();
    while ($db->next_record()) {
      $skill = new skill();
      $skill->read_db_record($db);
      array_push($skills_got, $skill->get_id());
    }
    $query = "SELECT * FROM skill ORDER BY skillClass";
    $db->query($query);
    while ($db->next_record()) {
      $skill = new skill();
      $skill->read_db_record($db);
      if (in_array($skill->get_id(), $skills_got)) {
        // dont show this item
      } else {
        $skills[$skill->get_id()] = sprintf("%s - %s", $skill->get_value('skillClass'), $skill->get_value('skillName'));
      }
    }
    if (count($skills) > 0) {
      $TPL["skills"] = page::select_options($skills, "");
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
$person = new person();

$personID = $_POST["personID"] or $personID = $_GET["personID"];

if ($personID) {
  $person->set_id($personID);
  $person->select();
}
    

if ($_POST["personExpertiseItem_add"] || $_POST["personExpertiseItem_save"] || $_POST["personExpertiseItem_delete"]) {
  $proficiency = new proficiency();
  $proficiency->read_globals();


  if ($_POST["skillID"] != null) {
    if ($_POST["personExpertiseItem_delete"]) {
      $proficiency->delete();
    } else if ($_POST["personExpertiseItem_save"]) {
      $proficiency->save();
    } else if ($_POST["personExpertiseItem_add"]) {
      // skillID is an array if when adding but not when saving or deleting
      $skillProficiency = $proficiency->get_value('skillProficiency');
      for ($i = 0; $i < count($_POST["skillID"]); $i++) {
        $proficiency = new proficiency();

        $proficiency->set_value('skillID', $_POST["skillID"][$i]);
        $proficiency->set_value('skillProficiency', $_POST["skillProficiency"]);
        $proficiency->set_value('personID', $personID);

        $db = new db_alloc();
        $query = prepare("SELECT * FROM proficiency WHERE personID = %d", $personID);
        $query.= prepare(" AND skillID = %d", $_POST["skillID"][$i]);
        $db->query($query);
        if (!$db->next_record()) {
          $proficiency->save();
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
    alloc_error("Please re-type the passwords");
  }


  if ($_POST["username"]) {
    $q = prepare("SELECT personID FROM person WHERE username = '%s'",$_POST["username"]);
    $db = new db_alloc();
    $db->query($q);
    $num_rows = $db->num_rows();
    $row = $db->row();

    if (($num_rows > 0 && !$person->get_id()) || ($num_rows > 0 && $person->get_id() != $row["personID"])){
      alloc_error("That username is already taken. Please select another.");
    }
  } else {
    alloc_error("Please enter a username.");
  }

  $person->set_value("personActive", $_POST["personActive"] ? 1 : "0");

  $max_alloc_users = get_max_alloc_users();
  if ($max_alloc_users && get_num_alloc_users() >= $max_alloc_users && $_POST["personActive"]) {
    alloc_error(get_max_alloc_users_message());
  }

  if (!$TPL["message"]) {
    $person->set_value("availability",rtrim($person->get_value("availability")));
    $person->set_value("areasOfInterest",rtrim($person->get_value("areasOfInterest")));
    $person->set_value("comments",rtrim($person->get_value("comments")));
    $person->set_value("emergencyContact",rtrim($person->get_value("emergencyContact")));
    $person->set_value("managementComments",rtrim($person->get_value("managementComments")));
    $person->currency = config::get_config_item('currency');
    $person->save();
    alloc_redirect($TPL["url_alloc_personList"]);
  }



} else if ($_POST["delete"]) {
  $person->delete();
  alloc_redirect($TPL["url_alloc_personList"]);
}

#$person = new person();
#$person->set_id($personID);
#$person->select();
$person->set_values("person_");

if ($person->get_id()) {
  $q = prepare("SELECT tfPerson.tfID AS value, tf.tfName AS label 
                  FROM tf, tfPerson 
  				       WHERE tf.tfID = tfPerson.tfID 
                   AND tfPerson.personID = %d 
                   AND (tf.tfActive = 1 OR tf.tfID = %d)"
                ,$person->get_id(),$person->get_value("preferred_tfID"));
  $TPL["preferred_tfID_options"] = page::select_options($q, $person->get_value("preferred_tfID"));

  $tf = new tf();
  $tf->set_id($person->get_value("preferred_tfID"));
  $tf->select();
}

$TPL["absence_url"] = $TPL["url_alloc_absence"]."personID=".$personID;
$TPL["personActive"] = (!$person->get_id() || $person->get_value("personActive")) ? " checked" : "";

if (has("time")) {
  $timeUnit = new timeUnit();
  $rate_type_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelB");
}
$TPL["timeSheetRateUnit_select"] = page::select_options($rate_type_array, $person->get_value("defaultTimeSheetRateUnitID"));
$TPL["timeSheetRateUnit_label"] = $rate_type_array[$person->get_value("defaultTimeSheetRateUnitID")];

if ($personID) {
  $TPL["main_alloc_title"] = "Person Details: " . $person->get_value("username")." - ".APPLICATION_NAME;
} else {
  $TPL["main_alloc_title"] = "New Person - ".APPLICATION_NAME;
}

include_template("templates/personM.tpl");


?>
