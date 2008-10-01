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

class savedView extends db_entity {
  public $data_table = "savedView";
  public $display_field_name = "savedViewID";
  public $key_field = "savedViewID";
  public $data_fields = array("personID"
                             ,"formName"
                             ,"viewName"
                             ,"formView"
                             );

  function save() {
    // Check for a duplicate
    $previousView = savedView::find_by_name($this->get_value("viewName"), $this->get_value("personID"));
    if($previousView) {
      $this->set_id($previousView->get_id());
    }
    parent::save();
  }

  function process_form($_FORM) {
    // Processes the result of a form submission $_FORM, returning the new version of $_FORM
    global $current_user, $TPL;

    if($_POST["filterName"] && $_POST["saveFilter"]) {
      $savedView = new savedView();
      $savedView->set_value("formName",$_FORM['form_name']);
      $savedView->set_value("viewName",$_POST['filterName']);
      $savedView->set_value('formView', serialize($_FORM));
      $savedView->set_value('personID', $current_user->get_id());
      $savedView->save();
      $_POST["savedViewID"] = $_FORM["savedViewID"] = $savedView->get_id();
      $_POST["loadFilter"] = true;
      $TPL["message_good"] = "Filter saved.";
    }

    if($_POST["deleteFilter"] && $_POST["savedViewID"]) {
      $savedView = new savedView();
      $savedView->set_id($_POST["savedViewID"]);
      $savedView->select();
      $savedView->delete();

    } else if($_POST["loadFilter"] && $_POST["savedViewID"] && !$_POST["applyFilter"]) {
      $savedView = new savedView();
      $savedView->set_id($_POST["savedViewID"]);
      $savedView->select();

      // OK, overwrite $_FORM and save it to the user's preferences
      $_FORM = $savedView->get_form_data();
      $_FORM["savedViewID"] = $_POST["savedViewID"];
      if(is_object($current_user)) {
        $current_user->prefs[$_FORM["form_name"]] = $_FORM;
      }
    } else if ($_POST["applyFilter"]) {
      unset($_FORM["savedViewID"]);
    }
    return $_FORM;
  }

  function delete() {
    // Deletes the record, checking that the user owns the savedView (or is a superuser)
    global $current_user, $TPL;
    if($current_user->have_role("god") || $current_user->get_id() == $this->get_value("personID")) {
      $TPL["message_good"] = "Filter deleted.";
      parent::delete();
    } else {
      $TPL["message"] = "Permission to delete filter denied.";
    }
  }

  function get_form_data() {
    // Gets the content of $_FORM saved in this savedView
    return unserialize($this->get_value('formView'));
  }

  function find_by_name($filterName, $personID) {
    // Finds a savedView instance, owned by $personID, that has the given name. Returns false on failure.
    $db = new db_alloc();
    $query = sprintf("SELECT *
                        FROM savedView
                       WHERE personID = '%d' AND viewName = '%s'", $personID, db_esc($filterName));

    $db->query($query);

    if($db->next_record()) {
      $savedView = new savedView;
      $savedView->read_db_record($db);
      return $savedView;
    } else {
      return false;
    }
  }

  function get_saved_view_options($form_name, $personID) {
    // Gets the saved views for the given $personID and $form_name
    $db = new db_alloc();
    $query = sprintf("SELECT savedViewID, viewName
                        FROM savedView
                       WHERE formName = '%s'
                         AND personID = '%d'
                    ORDER BY viewName
                     ", db_esc($form_name), $personID);
    $db->query($query);

    $rtn_array = array();
    while($db->next_record()) {
      $rtn_array[$db->f('savedViewID')] = $db->f('viewName');
    }
    return $rtn_array;
  }



}


?>
