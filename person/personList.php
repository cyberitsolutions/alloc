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

require_once("../alloc.php");

function show_people($template_name) {
  global $db, $current_user, $TPL;

  // Get averages for hours worked over the past fortnight and year
  $t = new timeSheetItem;
  list($ts_hrs_col_1,$ts_dollars_col_1) = $t->get_averages(date("Y-m-d",mktime(0,0,0,date("m"),date("d")-14, date("Y"))));
  list($ts_hrs_col_2,$ts_dollars_col_2) = $t->get_fortnightly_average();
  
  $where = FALSE;
  $query = "SELECT *, person.personID as personID, username as u";
  $query.= " FROM person";
  $query.= " LEFT JOIN absence on person.personID=absence.personID";
  $query.= " AND absence.dateFrom <= Current_DATE and absence.dateTO >= CURRENT_DATE";
  if ($_POST["skill"] != "" || $_POST["skill_class"] != "") {
    $query.= " LEFT JOIN skillProficiencys on person.personID=skillProficiencys.personID";
    // A single selected skill has precedence over skill class
    if ($_POST["skill"] != "") {
      $query.= sprintf(" WHERE skillID=%d", $_POST["skill"]);
    } else {
      // get list of skill IDs
      $query.= " WHERE (";
      $first = TRUE;
      $skills_query = "SELECT * FROM skillList";
      $skills_query.= sprintf(" WHERE skillClass='%s'", $_POST["skill_class"]);
      $db->query($skills_query);
      while ($db->next_record()) {
        $skillList = new skillList;
        $skillList->read_db_record($db);
        if ($first != TRUE) {
          $query.= " OR ";
        }
        $query.= sprintf("skillID=%d", $skillList->get_id());
        $first = FALSE;
      }
      $query.= ")";
    }
    if ($_POST["expertise"] != "") {
      $query.= sprintf(" AND skillProficiency='%s'", $_POST["expertise"]);
    }
    $where = TRUE;
  }
  $w = " AND ";
  $where or $w = " WHERE ";
  $_POST["show_all_users"] or $query .= $w." personActive = 1 ";
  $query.= " GROUP BY username";
  $query.= " ORDER BY firstName,surname,username";
  $db->query($query);
  while ($db->next_record()) {
    $TPL["person_absence"] = NULL;
    $person = new person;
    $person->read_db_record($db);
    $person->select();
    $person->set_tpl_values(DST_HTML_ATTRIBUTE, "person_");

    $TPL["person_username"] = $person->get_username(1);

    $TPL["person_personActive"] = $person->get_value("personActive") == 1 ? "Y":"";

    $TPL["person_personID"] = $db->f("personID");
    if ($db->f("absenceID") != NULL) {
      $msg = $db->f("absenceType")." Due back: ";
      $msg.= $db->f("dateTo");
      $TPL["person_absence"] = $msg;
    }

    $senior_skills = $person->get_skills('Senior');
    $TPL["senior_skills"] = ($senior_skills ? "<img src=\"../images/skill_senior.png\" alt=\"Senior=\">$senior_skills; " : "");

    $advanced_skills = $person->get_skills('Advanced');
    $TPL["advanced_skills"] = ($advanced_skills ? "<img src=\"../images/skill_advanced.png\" alt=\"Advanced=\">$advanced_skills; " : "");

    $intermediate_skills = $person->get_skills('Intermediate');
    $TPL["intermediate_skills"] = ($intermediate_skills ? "<img src=\"../images/skill_intermediate.png\" alt=\"Intermediate=\">$intermediate_skills; " : "");

    $junior_skills = $person->get_skills('Junior');
    $TPL["junior_skills"] = ($junior_skills ? "<img src=\"../images/skill_junior.png\" alt=\"Junior=\">$junior_skills; " : "");

    $novice_skills = $person->get_skills('Novice');
    $TPL["novice_skills"] = ($novice_skills ? "<img src=\"../images/skill_novice.png\" alt=\"Novice\">$novice_skills; " : "");

    if ($person->have_perm(PERM_PERSON_READ_MANAGEMENT)) {
      $TPL["ts_hrs_col_1"] = sprintf("%d",$ts_hrs_col_1[$db->f("personID")]);
      $TPL["ts_hrs_col_2"] = sprintf("%d",$ts_hrs_col_2[$db->f("personID")]);
    }

    # Might want to consider privacy issues before putting this in.
    #$TPL["ts_dollars_col_1"] = sprintf("%0.2f",$ts_dollars_col_1[$db->f("personID")]);
    #$TPL["ts_dollars_col_2"] = sprintf("%0.2f",$ts_dollars_col_2[$db->f("personID")]);

    $TPL["person_phoneNo1"] && $TPL["person_phoneNo2"] and $TPL["person_phoneNo1"].= "&nbsp;&nbsp;/&nbsp;&nbsp;";

    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";

    include_template($template_name);
  }
}

function check_optional_show_skills_list() {
  if ($_POST["show_skills"]) {
    return true;
  }
  return false;
}


$db = new db_alloc;
$_POST["show_skills"] and $TPL["show_skills_checked"] = " checked";
$_POST["show_all_users"] and $TPL["show_all_users_checked"] = " checked";
 
$employee_expertise = array(""=>"Any Expertise", "Novice"=>"Novice", "Junior"=>"Junior", "Intermediate"=>"Intermediate", "Advanced"=>"Advanced", "Senior"=>"Senior");
$TPL["employee_expertise"] = get_options_from_array($employee_expertise, $_POST["expertise"], true);

$skill_classes = skillList::get_skill_classes();
$TPL["skill_classes"] = get_options_from_array($skill_classes, $_POST["skill_class"], true);

$skills = skillList::get_skills();
// if a skill class is selected and a skill that is not in that class is also selected, clear the skill as this is what the filter options will do
if ($skill_class && !in_array($skills[$_POST["skill"]], $skills)) { $_POST["skill"] = ""; }
$TPL["skills"] = get_options_from_array($skills, $_POST["skill"], true);

include_template("templates/personListM.tpl");
page_close();



?>
