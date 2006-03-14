<?php
include("alloc.inc");

function show_people($template_name) {
  global $db, $current_user, $auth, $TPL, $skill, $skill_class, $expertise;

  // Get averages for hours worked over the past fortnight and year
  $t = new timeSheetItem;
  list($ts_hrs_col_1,$ts_dollars_col_1) = $t->get_averages(date("Y-m-d",mktime(0,0,0,date("m"),date("d")-14, date("Y"))));
  list($ts_hrs_col_2,$ts_dollars_col_2) = $t->get_fortnightly_average();
  
  $where = FALSE;
  $query = "SELECT *, person.personID as pid, username as u";
  $query.= " FROM person";
  $query.= " LEFT JOIN absence on person.personID=absence.personID";
  $query.= " AND absence.dateFrom <= Current_DATE and absence.dateTO >= CURRENT_DATE";
  if ($skill != "" || $skill_class != "") {
    $query.= " LEFT JOIN skillProficiencys on person.personID=skillProficiencys.personID";
    // A single selected skill has precedence over skill class
    if ($skill != "") {
      $query.= sprintf(" WHERE skillID=%d", $skill);
    } else {
      // get list of skill IDs
      $query.= " WHERE (";
      $first = TRUE;
      $skills_query = "SELECT * FROM skillList";
      $skills_query.= sprintf(" WHERE skillClass='%s'", $skill_class);
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
    if ($expertise != "") {
      $query.= sprintf(" AND skillProficiency='%s'", $expertise);
    }
    $where = TRUE;
  }
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

    $TPL["person_personID"] = $db->f("pid");
    if ($db->f("absenceID") != NULL) {
      $msg = $db->f("absenceType")." leave.  Due back after: ";
      $msg.= $db->f("dateTo");
      $TPL["person_absence"] = $msg;
    }

    $senior_skills = $person->get_skills('Senior');
    $TPL["senior_skills"] = ($senior_skills ? "<img src=\"../images/skill_senior.jpg\" alt=\"Senior=\">$senior_skills; " : "");

    $advanced_skills = $person->get_skills('Advanced');
    $TPL["advanced_skills"] = ($advanced_skills ? "<img src=\"../images/skill_advanced.jpg\" alt=\"Advanced=\">$advanced_skills; " : "");

    $intermediate_skills = $person->get_skills('Intermediate');
    $TPL["intermediate_skills"] = ($intermediate_skills ? "<img src=\"../images/skill_intermediate.jpg\" alt=\"Intermediate=\">$intermediate_skills; " : "");

    $junior_skills = $person->get_skills('Junior');
    $TPL["junior_skills"] = ($junior_skills ? "<img src=\"../images/skill_junior.jpg\" alt=\"Junior=\">$junior_skills; " : "");

    $novice_skills = $person->get_skills('Novice');
    $TPL["novice_skills"] = ($novice_skills ? "<img src=\"../images/skill_novice.jpg\" alt=\"Novice\">$novice_skills; " : "");

    $TPL["ts_hrs_col_1"] = sprintf("%d",$ts_hrs_col_1[$db->f("pid")]);
    $TPL["ts_hrs_col_2"] = sprintf("%d",$ts_hrs_col_2[$db->f("pid")]);
    # Might want to consider privacy issues before putting this in.
    #$TPL["ts_dollars_col_1"] = sprintf("%0.2f",$ts_dollars_col_1[$db->f("pid")]);
    #$TPL["ts_dollars_col_2"] = sprintf("%0.2f",$ts_dollars_col_2[$db->f("pid")]);

    $TPL["odd_even"] = $TPL["odd_even"] == "odd" ? "even" : "odd";

    include_template($template_name);
  }
}

function show_filter($template_name) {
  global $TPL, $show_skills;
  if (isset($show_skills)) {
    $TPL["show_skills_checked"] = "checked";
  }
  show_skill_classes();
  show_skills();
  show_expertise();
  include_template($template_name);
}

function check_optional_show_skills_list() {
  global $show_skills;
  if (isset($show_skills)) {
    return true;
  }
  return false;
}

function show_skill_classes() {
  global $TPL, $skill_class, $db;
  $skill_classes = array(""=>"Any class");
  $query = "SELECT skillClass FROM skillList ORDER BY skillClass";
  $db->query($query);
  while ($db->next_record()) {
    $skillList = new skillList;
    $skillList->read_db_record($db);
    if (!in_array($skillList->get_value('skillClass'), $skill_classes)) {
      $skill_classes[$skillList->get_value('skillClass')] = $skillList->get_value('skillClass');
    }
  }
  $TPL["skill_classes"] = get_options_from_array($skill_classes, $skill_class, true);
  $skill_classes[""] = ">> OTHER >>";
  $TPL["new_skill_classes"] = get_options_from_array($skill_classes, $skill_class, true);
}

function show_skills() {
  global $TPL, $skill, $skills, $skill_class, $db;
  $skills = array(""=>"Any skill");
  $query = "SELECT * FROM skillList";
  if ($skill_class != "") {
    $query.= sprintf(" WHERE skillClass='%s'", $skill_class);
  }
  $query.= " ORDER BY skillClass,skillName";
  $db->query($query);
  while ($db->next_record()) {
    $skillList = new skillList;
    $skillList->read_db_record($db);
    $skills[$skillList->get_id()] = sprintf("%s - %s", $skillList->get_value('skillClass'), $skillList->get_value('skillName'));
  }
  // if a skill class is selected and a skill that is not in that class is also selected, clear the skill
  // as this is what the filter options will do
  if ($skill_class != "" && !in_array($skills[$skill], $skills)) {
    $skill = "";
  }
  $TPL["skills"] = get_options_from_array($skills, $skill, true);
  $skills[""] = ">> NEW >>";
  $TPL["new_skills"] = get_options_from_array($skills, $skill, true);
}

function show_expertise() {
  global $TPL, $expertise;
  $employee_expertise = array(""=>"Any expertise", "Novice"=>"Novice", "Junior"=>"Junior", "Intermediate"=>"Intermediate", "Advanced"=>"Advanced", "Senior"=>"Senior");
  $TPL["employee_expertise"] = get_options_from_array($employee_expertise, $expertise, true);
}

function show_add_skill($template) {
  global $personID;

  $person = new person;
  $person->set_id($personID);
  $person->select();

  if ($person->have_perm(PERM_PERSON_READ_MANAGEMENT)) {
    include_template($template);
  }
}

  // add new skill to database
if (isset($add_skill)) {
  $failed = FALSE;
  $skillList = new skillList;
  if ($new_skill_class != "") {
    $skillList->set_value('skillClass', $new_skill_class);
  } else if ($other_new_skill_class != "") {
    $skillList->set_value('skillClass', $other_new_skill_class);
  } else {
    $failed = TRUE;
  }
  if ($other_new_skill_name != "") {
    $skillList->set_value('skillName', $other_new_skill_name);
    // description for now can be the same as the name
    $skillList->set_value('skillDescription', $other_new_skill_name);
  } else {
    $failed = TRUE;
  }
  if ($failed == FALSE && $skillList->skill_exists() == FALSE) {
    $skillList->save();
  }
}
if (isset($delete_skill)) {
  $skillList = new skillList;
  if ($new_skill_name != "") {
    $skillList->set_id($new_skill_name);
    $skillList->delete();
  }
}

$db = new db_alloc;

include_template("templates/personListM.tpl");
page_close();



?>
