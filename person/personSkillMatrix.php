<?php
require_once("alloc.inc");

function show_filter($template) {
  global $TPL;
  show_skill_classes();
  show_skills();
  include_template($template);
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
  if ($skill_class != "" && !in_array($skills[$skill], $skills)) {
    $skill = "";
  }
  $TPL["skills"] = get_options_from_array($skills, $skill, true);
}

function get_people_header() {
  global $TPL, $people_ids, $people_header, $skill, $skill_class, $show_all;

  $people_ids = array();

  $where = FALSE;
  $db = new db_alloc;
  $query = "SELECT * FROM person";
  $query.= " LEFT JOIN skillProficiencys ON person.personID=skillProficiencys.personID";
  $query.= " LEFT JOIN skillList ON skillProficiencys.skillID=skillList.skillID";
  if (!isset($show_all)) {
    $query.= " WHERE skillProficiencys.skillProficiency";
    $where = TRUE;
  }
  if ($skill) {
    if ($where = FALSE) {
      $query.= sprintf(" WHERE skillList.skillID=%d", $skill);
      $where = TRUE;
    } else {
      $query.= sprintf(" AND skillList.skillID=%d", $skill);
    }
  } else if ($skill_class) {
    if ($where = FALSE) {
      $query.= sprintf(" WHERE skillList.skillClass='%s'", $skill_class);
      $where = TRUE;
    } else {
      $query.= sprintf(" AND skillList.skillClass='%s'", $skill_class);
    }
  }
  $query.= " GROUP BY username ORDER BY username";
  $db->query($query);
  while ($db->next_record()) {
    $person = new person;
    $person->read_db_record($db);
    array_push($people_ids, $person->get_id());
    $people_header.= sprintf("<th>%s</th>\n", $person->get_value('username'));
  }
}

function show_skill_expertise() {
  global $TPL, $people_ids, $people_header, $skill, $skill_class;

  $currSkillClass = null;

  $db = new db_alloc;
  $query = "SELECT * FROM skillProficiencys";
  $query.= " LEFT JOIN skillList ON skillProficiencys.skillID=skillList.skillID";
  if ($skill != "" || $skill_class != "") {
    if ($skill != "") {
      $query.= sprintf(" WHERE skillProficiencys.skillID=%d", $skill);
    } else {
      $query.= sprintf(" WHERE skillClass='%s'", $skill_class);
    }
  }
  $query.= " GROUP BY skillName ORDER BY skillClass,skillName";
  $db->query($query);
  while ($db->next_record()) {
    $skillList = new skillList;
    $skillList->read_db_record($db);
    $thisSkillClass = $skillList->get_value('skillClass');
    if ($currSkillClass != $thisSkillClass) {
      $currSkillClass = $thisSkillClass;
      if (!isset($people_header)) {
        get_people_header();
      }
      $class_header = sprintf("<tr class=\"highlighted\">\n<th>%s&nbsp;&nbsp;&nbsp;</th>\n", $skillList->get_value('skillClass'));
      print $class_header.$people_header."</tr>\n";
    }
    print sprintf("<tr>\n<th>%s</th>\n", $skillList->get_value('skillName'));
    for ($i = 0; $i < count($people_ids); $i++) {
      $db2 = new db_alloc;
      $query = "SELECT * FROM skillProficiencys";
      $query.= sprintf(" WHERE skillID=%d AND personID=%d", $skillList->get_id(), $people_ids[$i]);
      $db2->query($query);
      if ($db2->next_record()) {
        $skillProficiencys = new skillProficiencys;
        $skillProficiencys->read_db_record($db2);
        $proficiency = sprintf("<td align=\"center\"><img src=\"../images/skill_%s.jpg\" alt=\"%s\" width=18 height=18></td>\n", strtolower($skillProficiencys->get_value('skillProficiency')), substr($skillProficiencys->get_value('skillProficiency'), 0, 1));
        print $proficiency;
      } else {
        print "<td align=\"center\">-</td>\n";
        // print "<td align=\"center\"><img src=\"../images/none.jpg\" alt=\"-\" width=18 height=18></td>\n";
      }
    }
    print "</tr>\n";
  }
}

$db = new db_alloc;

include_template("templates/personSkillMatrix.tpl");
page_close();



?>
