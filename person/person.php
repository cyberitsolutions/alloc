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

require_once("alloc.inc");

  function show_perm_select() {
    global $person;
    if ($person->have_perm(PERM_PERSON_WRITE_ROLES)) {
      $selected = explode(",",$person->get_value("perms"));
      $ops = array("god"=>"Super User","admin"=>"Finance Admin","manage"=>"Project Manager");
      echo sprintf("<select size=\"3\" multiple name=\"perm_select[]\">\n");
      echo get_select_options($ops,$selected);
      echo "</select>";
    } else {
      echo $person->get_value("perms");
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
      echo "<input type=\"submit\" name=\"delete\" value=\"Delete Record\" onClick=\"return confirm('Are you sure you want to delete this record?')\">"; 
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

if (isset($personID)) {
  $person->set_id($personID);
  $person->select();
}
    

if (isset($personExpertiseItem_add) || isset($personExpertiseItem_save) || isset($personExpertiseItem_delete)) {
  $skillProficiencys = new skillProficiencys;
  $skillProficiencys->read_globals();


  if ($skillID != null) {
    if (isset($personExpertiseItem_delete)) {
      $skillProficiencys->delete();
    } else if (isset($personExpertiseItem_save)) {
      $skillProficiencys->save();
    } else if (isset($personExpertiseItem_add)) {
      // skillID is an array if when adding but not when saving or deleting
      $skillProficiency = $skillProficiencys->get_value('skillProficiency');
      for ($i = 0; $i < count($skillID); $i++) {
        $skillProficiencys = new skillProficiencys;

        $skillProficiencys->set_value('skillID', $skillID[$i]);
        $skillProficiencys->set_value('skillProficiency', $skillProficiency);
        $skillProficiencys->set_value('personID', $personID);

        $db = new db_alloc;
        $query = "SELECT * FROM skillProficiencys WHERE personID = $personID";
        $query.= sprintf(" AND skillID = %d", $skillID[$i]);
        $db->query($query);
        if (!$db->next_record()) {
          $skillProficiencys->save();
        }
      }
    }
  }
}

if (isset($save)) {
  $person->read_globals();

  if ($password1 == $password2) {

    if ($person->can_write_field("perms")) {
      if (is_array($perm_select)) {
        $person->set_value("perms", implode(",", $perm_select).",employee");
      } else {
        $person->set_value("perms", "employee");
      }
    }

    $person->set_value("personActive", $personActive ? 1 : "0");


    if ($password1 == "") {
      $person_check = new person;
      $person_check->set_id($person->get_id());
      $person_check->select();
      $person->set_value('password', $person_check->get_value('password'));
    } else {
      $person->set_value('password', addslashes(crypt(trim($password1), trim($current_user->get_value('password')))));
    }

    $person->save();
    $personID = $person->get_id();
  } else {
    $TPL["message"][] = "Please re-type the passwords";
  }
} else if (isset($delete)) {
  $person->delete();
  header("Location: ".$TPL["url_alloc_personList"]);
}

$person = new person;
$person->set_id($personID);
$person->select();
$person->set_tpl_values(DST_HTML_ATTRIBUTE, "person_");

if ($person->get_id()) {
  $db_tf = new db_alloc;
  $db_tf->query("SELECT tf.tfName as name, tfPerson.tfID as id FROM tf, tfPerson 
  				 WHERE tf.tfID = tfPerson.tfID AND tfPerson.personID = ".$person->get_id());
  $TPL["preferred_tfID_options"] = get_options_from_db($db_tf, "name", "id", $person->get_value("preferred_tfID"));
  $dailyTEO = array("yes"=>"Yes", "no"=>"No");
  $TPL["dailyTaskEmailOptions"] = get_options_from_array($dailyTEO, $person->get_value("dailyTaskEmail"));
}


if ($person->get_value("emailFormat") == "html") {
  $email_format_options = "<option>text";
  $email_format_options.= "<option selected>html";
} else {
  $email_format_options = "<option selected>text";
  $email_format_options.= "<option>html";
}

$TPL["email_format_options"] = $email_format_options;

$TPL["absence_url"] = $TPL["url_alloc_absence"]."personID=".$personID;
$TPL["personActive"] = (!$person->get_id() || $person->get_value("personActive")) ? " checked" : "";

include_template("templates/personM.tpl");

page_close();



?>
