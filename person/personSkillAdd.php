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



// add new skill to database
if ($_POST["add_skill"]) {
  $failed = FALSE;
  $skill = new skill();
  if ($_POST["new_skill_class"] != "") {
    $skill->set_value('skillClass', $_POST["new_skill_class"]);
  } else if ($_POST["other_new_skill_class"] != "") {
    $skill->set_value('skillClass', $_POST["other_new_skill_class"]);
  } else {
    $failed = TRUE;
  } 
  if ($_POST["other_new_skill_name"] != "") {
    $skill->set_value('skillName', $_POST["other_new_skill_name"]);
    // description for now can be the same as the name
    $skill->set_value('skillDescription', $_POST["other_new_skill_name"]);
  } else {
    $failed = TRUE;
  } 
  if ($failed == FALSE && $skill->skill_exists() == FALSE) {
    $skill->save();
  } 
} 
if ($_POST["delete_skill"]) {
  $skill = new skill();
  if ($_POST["new_skill_name"] != "") {
    $skill->set_id($_POST["new_skill_name"]);
    $skill->delete();
  } 
} 


$skill_classes = skill::get_skill_classes();
$skill_classes[""] = ">> OTHER >>";
$TPL["new_skill_classes"] = page::select_options($skill_classes, $_POST["skill_class"]);

$skills = skill::get_skills();
// if a skill class is selected and a skill that is not in that class is also selected, clear the skill as this is what the filter options will do
if ($skill_class && !in_array($skills[$_POST["skill"]], $skills)) { $_POST["skill"] = ""; }
$skills[""] = ">> NEW >>";
$TPL["new_skills"] = page::select_options($skills, $_POST["skill"]);


$TPL["main_alloc_title"] = "Edit Skills - ".APPLICATION_NAME;
if ($current_user->have_perm(PERM_PERSON_READ_MANAGEMENT)) {
  include_template("templates/personSkillAdd.tpl");
}
