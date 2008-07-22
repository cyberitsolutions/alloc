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
  var $data_table = "savedView";
  var $display_field_name = "savedViewID";


  function savedView() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_field("savedViewID");
    $this->data_fields = array("personID"=>new db_field("personID")
                              ,"formName"=>new db_field("formName")
                              ,"viewName"=>new db_field("viewName")
                              ,"formView"=>new db_field("formView")
                              );
  }

  function save_from_form($_FORM, $personID) {
    // Saves a filter for the given form, with the name, et cetera given in the form itself
    $filterName = db_esc($_FORM['new_filter_name']);
    $formName = db_esc($_FORM['form_name']);
    unset($_FORM['url_form_action']);

    // Check for a duplicate
    if( ($previousView = savedView::find_by_name($filterName, $personID)) !== FALSE) {
      // A duplicate, so just update that one
      $this->set_id($previousView->get_id());
    }

    $this->set_value('personID', $personID);
    $this->set_value('formName', $formName);
    $this->set_value('viewName', $filterName);
    $this->set_value('formView', serialize($_FORM));

    $this->save();
  }

  function process_form($_FORM) {
    // Processes the result of a form submission $_FORM, returning the new version of $_FORM
    global $current_user, $TPL;

    if($_POST["saveFilter"]) {
      $savedView = new savedView();
      $current_user and $savedView->save_from_form($_FORM, $current_user->get_id());
      $TPL["message_good"] = "Filter saved.";
    }

    if($_POST["deleteFilter"] && $_POST["saved_filter"] != "current") {
      $viewID = $_POST["saved_filter"];
      $savedView = new savedView();
      $savedView->set_id($viewID);
      $savedView->select();
      $savedView->delete();
    }

    if($_POST["loadFilter"] && $_POST["saved_filter"] != "current") {
      // We expect the filter to be specified in the form viewN, where N is the viewID to load
      $viewID = $_POST["saved_filter"];
      $savedView = new savedView();
      $savedView->set_id($viewID);
      $savedView->select();

      // OK, overwrite $_FORM
      $_FORM = $savedView->get_form_data();
      // and save it to the user's preferences
      if(is_object($current_user)) {
        $url = $_FORM["url_form_action"];
        unset($_FORM["url_form_action"]);
        $current_user->prefs[$_FORM["form_name"]] = $_FORM;
        $_FORM["url_form_action"] = $url;
      }
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
    // Gets the saved views for the given $personID for the given $form_name
    $db = new db_alloc();
    $query = sprintf("SELECT savedViewID, viewName
                        FROM savedView
                       WHERE formName = '%s'
                         AND personID = '%d'", db_esc($form_name), $personID);
    $db->query($query);

    $rtn_array = array();
    while($db->next_record()) {
      $rtn_array[$db->f('savedViewID')] = $db->f('viewName');
    }
    return $rtn_array;
  }



}


?>
