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


function load_form_data($defaults=array()) {
  global $current_user;

  $page_vars = array("projectID"
                    ,"taskStatus"
                    ,"taskTypeID"
                    ,"personID"
                    ,"taskView"
                    ,"projectType"
                    ,"applyFilter"
                    ,"showDescription"
                    ,"showDates"
                    ,"showCreator"
                    ,"showAssigned"
                    ,"showTimes"
                    ,"showPercent"
                    ,"showPriority"
                    ,"showStatus"
                    ,"showTaskID"
                    ,"showHeader"
                    ,"showProject"
                    ,"padding"
                    ,"url_form_action"
                    ,"form_name"
                    ,"dontSave"
                    );

  $_FORM = get_all_form_data($page_vars,$defaults);

  if ($_FORM["projectID"] && !is_array($_FORM["projectID"])) {
    $p = $_FORM["projectID"];
    unset($_FORM["projectID"]);
    $_FORM["projectID"][] = $p;

  } else if (!$_FORM["projectID"] && $_FORM["projectType"]) {
    $q = project::get_project_type_query($_FORM["projectType"]);
    $db = new db_alloc;
    $db->query($q);
    while($row = $db->row()) {
      $_FORM["projectID"][] = $row["projectID"];
    }

  } else if (!$_FORM["projectType"]){
    $_FORM["projectType"] = "mine";
  }
  
  if (!$_FORM["applyFilter"]) {
    $_FORM = $current_user->prefs[$_FORM["form_name"]];
    if (!isset($current_user->prefs[$_FORM["form_name"]])) {
      $_FORM["projectType"] = "mine";
      $_FORM["taskStatus"] = "not_completed";
      $_FORM["personID"] = $current_user->get_id();
    }

  } else if ($_FORM["applyFilter"] && is_object($current_user) && !$_FORM["dontSave"]) {
    $current_user->prefs[$_FORM["form_name"]] = $_FORM;
  }

  // If have check Show Description checkbox then display the Long Description and the Comments
  if ($_FORM["showDescription"]) {
    $_FORM["showComments"] = true;
  } else {
    unset($_FORM["showComments"]);
  }
  $_FORM["taskView"] or $_FORM["taskView"] = "byProject";
  return $_FORM;
}


function load_task_filter($_FORM) {

  $db = new db_alloc;


  // Load up the forms action url
  $rtn["url_form_action"] = $_FORM["url_form_action"];

  // Load up the filter bits
  $rtn["projectOptions"] = project::get_project_list_dropdown($_FORM["projectType"],$_FORM["projectID"]);

  $_FORM["projectType"] and $rtn["projectType_checked_".$_FORM["projectType"]] = " checked"; 

  $rtn["personOptions"] = "\n<option value=\"\"> ";
  $rtn["personOptions"].= get_select_options(person::get_username_list($_FORM["personID"]), $_FORM["personID"]);

  $taskType = new taskType;
  $rtn["taskTypeOptions"] = "\n<option value=\"\"> ";
  $rtn["taskTypeOptions"].= $taskType->get_dropdown_options("taskTypeID","taskTypeName",$_FORM["taskTypeID"]);


  $_FORM["taskView"] and $rtn["taskView_checked_".$_FORM["taskView"]] = " checked";

  $taskStatii = task::get_task_statii_array();
  $rtn["taskStatusOptions"] = get_options_from_array($taskStatii, $_FORM["taskStatus"]);

  $_FORM["showDescription"] and $rtn["showDescription_checked"] = " checked";
  $_FORM["showDates"]       and $rtn["showDates_checked"]       = " checked";
  $_FORM["showCreator"]     and $rtn["showCreator_checked"]     = " checked";
  $_FORM["showAssigned"]    and $rtn["showAssigned_checked"]    = " checked";
  $_FORM["showTimes"]       and $rtn["showTimes_checked"]       = " checked";
  $_FORM["showPercent"]     and $rtn["showPercent_checked"]     = " checked";
  $_FORM["showPriority"]    and $rtn["showPriority_checked"]    = " checked";
  $_FORM["showStatus"]      and $rtn["showStatus_checked"]      = " checked";
  $_FORM["showTaskID"]      and $rtn["showTaskID_checked"]      = " checked";
  
  // Get
  $rtn["FORM"] = "FORM=".urlencode(serialize($_FORM));


  return $rtn;
}



?>
