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


$skill_classes = skillList::get_skill_classes();
$skill_classes[""] = ">> OTHER >>";
$TPL["new_skill_classes"] = get_options_from_array($skill_classes, $_POST["skill_class"], true);

$skills = skillList::get_skills();
// if a skill class is selected and a skill that is not in that class is also selected, clear the skill as this is what the filter options will do
if ($skill_class && !in_array($skills[$_POST["skill"]], $skills)) { $_POST["skill"] = ""; }
$skills[""] = ">> NEW >>";
$TPL["new_skills"] = get_options_from_array($skills, $_POST["skill"], true);



// add new skill to database
if ($_POST["add_skill"]) {
  $failed = FALSE;
  $skillList = new skillList;
  if ($_POST["new_skill_class"] != "") {
    $skillList->set_value('skillClass', $_POST["new_skill_class"]);
  } else if ($_POST["other_new_skill_class"] != "") {
    $skillList->set_value('skillClass', $_POST["other_new_skill_class"]);
  } else {
    $failed = TRUE;
  } 
  if ($_POST["other_new_skill_name"] != "") {
    $skillList->set_value('skillName', $_POST["other_new_skill_name"]);
    // description for now can be the same as the name
    $skillList->set_value('skillDescription', $_POST["other_new_skill_name"]);
  } else {
    $failed = TRUE;
  } 
  if ($failed == FALSE && $skillList->skill_exists() == FALSE) {
    $skillList->save();
    header("Location: ".$TPL["url_alloc_personList"]);
  } 
} 
if ($_POST["delete_skill"]) {
  $skillList = new skillList;
  if ($_POST["new_skill_name"] != "") {
    $skillList->set_id($_POST["new_skill_name"]);
    $skillList->delete();
    header("Location: ".$TPL["url_alloc_personList"]);
  } 
} 

if ($current_user->have_perm(PERM_PERSON_READ_MANAGEMENT)) {
  include_template("templates/personSkillAdd.tpl");
}
