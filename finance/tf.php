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

function show_person_list($template) {
  global $TPL;
  global $tf;
  $db = new db_alloc();
  $TPL["person_buttons"] = '
        <button type="submit" name="person_delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
        <button type="submit" name="person_save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>';

  $tfID = $tf->get_id();

  if ($tfID) {
    $query = prepare("SELECT * from tfPerson WHERE tfID=%d", $tfID);
    $db->query($query);
    while ($db->next_record()) {
      $tfPerson = new tfPerson();
      $tfPerson->read_db_record($db);
      $tfPerson->set_values("person_");
      $person = $tfPerson->get_foreign_object("person");
      $TPL["person_username"] = $person->get_value("username");
      include_template($template);
    }
  }
}

function show_new_person($template) {
  global $TPL;
  $TPL["person_buttons"] = '
        <button type="submit" name="person_save" value="1" class="save_button">Add<i class="icon-plus-sign"></i></button>';

  $tfPerson = new tfPerson();
  $tfPerson->set_values("person_");
  include_template($template);
}

function show_person_options() {
  global $TPL;
  echo page::select_options(person::get_username_list($TPL["person_personID"]), $TPL["person_personID"]);
}

$db = new db_alloc();
$tf = new tf();

$tfID = $_GET["tfID"] or $tfID = $_POST["tfID"];
if ($tfID) {
  $tf->set_id($tfID);
  $tf->select();
} else {
  $tf_is_new = true;
}

if ($_POST["save"]) {
  $tf->read_globals();

  if ($_POST["isActive"]) {
    $tf->set_value("tfActive", 1);
  } else {
    $tf->set_value("tfActive", 0);
  }

  if ($tf->get_value("tfName") == "") {
    alloc_error("You must enter a name.");
  } else {

    if (!$tf->get_id()) {
      $db = new db_alloc();
      $q = prepare("SELECT count(*) AS tally FROM tf WHERE tfName = '%s'",$tf->get_value("tfName"));
      $db->query($q);
      $db->next_record();
      $tf_is_taken = $db->f("tally");
    }

    if ($tf_is_taken) {
      alloc_error("That TF name is taken, please choose another.");
    } else {
      $tf->set_value("tfComments",rtrim($tf->get_value("tfComments")));
      $tf->save();
      $TPL["message_good"][] = "Your TF has been saved.";
      $tf_is_new and $TPL["message_help"][] = "Please now add the TF Owners who are allowed to access this TF.";
    }
  }


} else {

  if ($_POST["delete"]) {
    $tf->delete();
    alloc_redirect($TPL["url_alloc_tfList"]);
    exit();
  }
}

if ($_POST["person_save"] || $_POST["person_delete"]) {

  $tfPerson = new tfPerson();
  $tfPerson->read_globals();
  $tfPerson->read_globals("person_");
  if (!$_POST["person_personID"]) {
    alloc_error("Please select a person from the dropdown list.");
  } else if ($_POST["person_save"]) {
    $tfPerson->save();
    $TPL["message_good"][] = "Person added to TF.";
  } else if ($_POST["person_delete"]) {
    $tfPerson->delete();
  }
}


$tf->set_values();


$TPL["tfModifiedTime"] = $tf->get_value("tfModifiedTime");
if ($tf->get_value("tfModifiedUser")) {
  $TPL["tfModifiedUser"] = person::get_fullname($tf->get_value("tfModifiedUser"));
}

$tf->get_value("tfActive") || !$tf->get_id() and $TPL["tfIsActive"] = " checked";

$TPL["main_alloc_title"] = "Edit TF - ".APPLICATION_NAME;

if (!$tf->get_id()) {
  $TPL["message_help"][] = "Enter the details below and click the Save button to create a new Tagged Fund. 
                            <br><br>A Tagged Fund or TF, is like a sort of bank account within allocPSA. 
                            It contains transactions which track the transfer of monies.";
}

include_template("templates/tfM.tpl");


?>
