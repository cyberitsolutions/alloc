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




  function show_attachments() {
    global $projectID;
    util_show_attachments("project",$projectID);
  }

  function list_attachments($template_name) {
    global $TPL, $projectID;

    if ($projectID) {
      $rows = get_attachments("project",$projectID);
      foreach ($rows as $row) {
        $TPL = array_merge($TPL,$row);
        include_template($template_name);
      }
    }
  }

$grand_total = 0;

  function show_timeSheet($template) {
    global $db, $TPL, $projectID, $current_user, $projectBudget, $grand_total;

    $timeSheet = new timeSheet;

    $db2 = new db_alloc;

    if ($projectID) {

      $query = sprintf("SELECT timeSheet.*, username
                          FROM timeSheet
                     LEFT JOIN person on timeSheet.personID = person.personID 
                         WHERE timeSheet.projectID = %d 
                      GROUP BY timeSheetID
                      ORDER BY dateFrom, username",$projectID);
      $db->query($query);

      while ($db->next_record()) {
        $timeSheet = new timeSheet;
        $timeSheet->read_db_record($db,false);
        $timeSheet->set_tpl_values(DST_HTML_ATTRIBUTE, "timeSheet_");
        $TPL["timeSheet_username"] = $db->f("username");
        $timeSheet->load_pay_info();

        $q = sprintf("SELECT SUM(amount) AS amount 
                        FROM transaction 
                       WHERE timeSheetID = %d 
                         AND amount>0 
                         AND status = 'approved'
                     ",$timeSheet->get_id());
        $db2->query($q);
        $db2->next_record(); 

        $TPL["amount"] = sprintf("$%0.2f", $db2->f("amount"));
        $grand_total += $db2->f("amount");

        include_template($template);
      }
    }
  }

  function show_transaction($template) {
    global $db, $TPL, $projectID, $current_user, $projectBudget, $grand_total;

    $transaction = new transaction;

    if (isset($projectID) && $projectID) {
      if (have_entity_perm("transaction", PERM_READ, $current_user, false)) {
        $query = sprintf("SELECT transaction.* ")
          .sprintf("FROM transaction ")
          .sprintf("WHERE transaction.projectID = '%d' AND status='approved' ", $projectID)
          .sprintf("ORDER BY lastModified desc");
      } else {
        $query = sprintf("SELECT transaction.* ")
          .sprintf("FROM transaction ")
          .sprintf("WHERE transaction.projectID = '%d' ", $projectID)
          .sprintf(" AND transaction.tfID = %d AND status='approved'", $current_user->get_id())
          .sprintf("ORDER BY lastModified desc");
      }
      $db->query($query);
      while ($db->next_record()) {
        $transaction = new transaction;
        $transaction->read_db_record($db);
        $transaction->set_tpl_values(DST_HTML_ATTRIBUTE, "transaction_");

        $tf = $transaction->get_foreign_object("tf");
        $tf->set_tpl_values();
        $tf->set_tpl_values(DST_HTML_ATTRIBUTE, "tf_");

        $TPL["transaction_username"] = $db->f("username");
        $TPL["transaction_amount"] = number_format(($TPL["transaction_amount"]), 2);
        include_template($template);

        $grand_total += $TPL["transaction_amount"];
      }

      $gt = $TPL["grand_total"] = sprintf("%0.2f", $grand_total);
      $pb = $TPL["project_projectBudget"] = sprintf("%0.2f", $TPL["project_projectBudget"]);

      // calculate percentage from grand total (gt) and project budget (pb)
      if ($gt > 0 && $pb > 0) {
        $p = $gt / $pb * 100;
      } else {
        $p = "0";
      }
      $TPL["percentage"] = sprintf("%0.1f",$p);
    }
  }

  function show_commission_list($template_name) {
    global $TPL, $db, $projectID;

    $TPL["commission_list_buttons"] = "
      <input type=\"submit\" name=\"commission_save\" value=\"Save\">
      <input type=\"submit\" name=\"commission_delete\" value=\"Delete\">";

    if ($projectID) {
      $query = sprintf("SELECT * from projectCommissionPerson WHERE projectID= %d", $projectID);
      $db->query($query);

      while ($db->next_record()) {
        $commission_item = new projectCommissionPerson;
        $commission_item->read_db_record($db);
        $commission_item->set_tpl_values(DST_HTML_ATTRIBUTE, "commission_");
        $tf = $commission_item->get_foreign_object("tf");
        include_template($template_name);
      }
    }
  }

  function show_new_commission($template_name) {
    global $TPL, $projectID;

    // Don't show entry form for new projects
    if (!$projectID) {
      return;
    }

    $TPL["commission_list_buttons"] = "<input type=\"submit\" name=\"commission_save\" value=\"Add\">";
    $commission_item = new projectCommissionPerson;
    $commission_item->set_tpl_values(DST_HTML_ATTRIBUTE, "commission_");
    $TPL["commission_projectID"] = $projectID;
    include_template($template_name);
  }

  function show_modification_history($template) {
    global $TPL, $projectID, $db;

    if ($projectID) {
      $query = sprintf("SELECT * from projectModificationNote WHERE projectID= %d", $projectID);
      $db->query($query);

      while ($db->next_record()) {
        $projectModification = new projectModificationNote;
        $projectModification->read_db_record($db);
        $projectModification->set_tpl_values(DST_HTML_ATTRIBUTE, "mod_");
        // Display the user who made modification.
        $person = new person;
        $person->set_id($projectModification->get_value("personID"));
        $person->select();
        $TPL["mod_personName"] = $person->get_value("username");
        include_template($template);
      }
    }
  }

  function show_person_list($template) {
    global $db, $TPL, $projectID;
    global $email_type_array, $rate_type_array, $project_person_role_array;

    if ($projectID) {
      $query = sprintf("SELECT * from projectPerson WHERE projectID=%d", $projectID);
      $db->query($query);

      while ($db->next_record()) {
        $projectPerson = new projectPerson;
        $projectPerson->read_db_record($db);
        $projectPerson->set_tpl_values(DST_HTML_ATTRIBUTE, "person_");
        $person = $projectPerson->get_foreign_object("person");
        $TPL["person_username"] = $person->get_value("username");
        $TPL["person_emailType_options"] = get_select_options($email_type_array, $TPL["person_emailType"]);
        $TPL["person_projectPersonRole_options"] = get_select_options($project_person_role_array, $TPL["person_projectPersonRoleID"]);
        $TPL["rateType_options"] = get_select_options($rate_type_array, $TPL["person_rateUnitID"]);
        $TPL["person_buttons"] = "
          <input type=\"submit\" name=\"person_save\" value=\"Save\">
          <input type=\"submit\" name=\"person_delete\" value=\"Delete\">";
        include_template($template);
      }
    }
  }

  function show_new_person($template) {
    global $TPL, $email_type_array, $rate_type_array, $projectID, $project_person_role_array;

    // Don't show entry form for new projects
    if (!$projectID) {
      return;
    }

    $TPL["person_buttons"] = "<input type=\"submit\" name=\"person_save\" value=\"Add\">";
    $project_person = new projectPerson;
    $project_person->set_tpl_values(DST_HTML_ATTRIBUTE, "person_");
    $TPL["person_emailType_options"] = get_select_options($email_type_array, $TPL["person_emailType"]);
    $TPL["person_projectPersonRole_options"] = get_select_options($project_person_role_array,false);
    $TPL["rateType_options"] = get_select_options($rate_type_array, $TPL["person_rateUnitID"]);
    include_template($template);
  }

  function show_time_sheets($template_name) {
    global $current_user;

    if ($current_user->is_employee()) {
      include_template($template_name);
    }
  }

  function show_transactions($template_name) {
    global $current_user;

    if ($current_user->is_employee()) {
      include_template($template_name);
    }
  }

  function show_person_options() {
    global $TPL;
    echo get_select_options(person::get_username_list($TPL["person_personID"]),$TPL["person_personID"]);
  }

  function show_tf_options($commission_tfID) {
    global $tf_array, $TPL;
    echo get_select_options($tf_array, $TPL[$commission_tfID]);
  }

  function show_comments() {
    global $projectID, $TPL;
    $options["showEditButtons"] = true;
    $TPL["commentsR"] = util_get_comments("project",$projectID,$options);

    if ($TPL["commentsR"] && !$_GET["comment_edit"]) {
      $TPL["class_new_project_comment"] = "hidden";
    }

    include_template("templates/projectCommentM.tpl");
  }

  function show_reminders($template) {
    global $TPL, $projectID, $reminderID, $current_user;

    // show all reminders for this project
    $reminder = new reminder;
    $db = new db_alloc;
    $permissions = explode(",", $current_user->get_value("perms"));

    if (in_array("admin", $permissions) || in_array("manage", $permissions)) {
      $query = sprintf("SELECT * FROM reminder WHERE reminderType='project' AND reminderLinkID=%d", $projectID);
    } else {
      $query = sprintf("SELECT * FROM reminder WHERE reminderType='project' AND reminderLinkID=%d AND personID='%s'", $projectID, $current_user->get_id());
    }

    $db->query($query);

    while ($db->next_record()) {
      $reminder->read_db_record($db);
      $reminder->set_tpl_values(DST_HTML_ATTRIBUTE, "reminder_");

      if ($reminder->get_value('reminderRecuringInterval') == "No") {
        $TPL["reminder_reminderRecurence"] = "&nbsp;";
      } else {
        $TPL["reminder_reminderRecurence"] = "Every ".$reminder->get_value('reminderRecuringValue')
          ." ".$reminder->get_value('reminderRecuringInterval')."(s)";
      }

      $person = new person;
      $person->set_id($reminder->get_value('personID'));
      $person->select();
      $TPL["reminder_reminderRecipient"] = $person->get_value('username');
      $TPL["returnToParent"] = "project";

      include_template($template);
    }
  }


// END FUNCTIONS




global $current_user;

$projectID = $_POST["projectID"] or $projectID = $_GET["projectID"];

$project = new project;

if ($projectID) {
  $project->set_id($projectID);
  $project->check_perm();
  $new_project = false;
} else {
  $new_project = true;
}


if ($_POST["save"]) {
  $project->read_globals();
  $project->set_value("is_agency", $_POST["project_is_agency"] ? 1 : 0);

  if (!$project->get_id()) {    // brand new project
    $definately_new_project = true;
  }


  $project->save();
  $projectID = $project->get_id();

  if ($definately_new_project) {
    $projectPerson = new projectPerson;
    $projectPerson->set_value("projectID", $projectID);
    $projectPerson->set_value_role("isManager");
    $projectPerson->set_value("personID", $current_user->get_id());
    $projectPerson->save();
  }
  // Automaticall created phases in projects
  if ($new_project && $project->get_value("projectType") == "project") {
    $creatorID = $current_user->get_id();
    $dateCreated = date("Y-m-d H:i:s");
    $taskNames = array(1=>"Phase 1: Discussion & Legal", 2=>"Phase 2: Planning & Documentation", 3=>"Phase 3: Development", 4=>"Phase 4: Testing", 5=>"Phase 5: Handover & Deployment");
    $phasePercentageTimes = array(1=>0.10, 2=>0.16, 3=>0.32, 4=>0.32, 5=>0.10);
    if ($project->get_value("dateTargetStart") != "") {
      $time_start = strtotime($project->get_value("dateTargetStart"));
    } else {
      $time_start = strtotime(date("Y-m-d"));
    }
    if ($project->get_value("dateTargetCompletion") != "") {
      $time_total = strtotime($project->get_value("dateTargetCompletion")) - $time_start;
    } else {
      $time_total = mktime(0, 0, 0, date("m") + 6, date("d"), date("Y")) - $time_start;
    }
    for ($i = 1; $i < 6; $i++) {
      $time_end = $time_start + $phasePercentageTimes[$i] * $time_total;
      $task = new task;
      $task->set_value("taskName", $taskNames[$i]);
      $task->set_value("creatorID", $creatorID);
      $task->set_value("priority", "4");
      $task->set_value("timeEstimate", "0");
      $task->set_value("dateCreated", $dateCreated);
      $task->set_value("projectID", $projectID);
      $task->set_value("dateTargetStart", date("Y-m-d", $time_start));
      $task->set_value("dateTargetCompletion", date("Y-m-d", $time_end));
      $task->set_value("parentTaskID", "0");
      $task->set_value("taskTypeID", TT_PHASE);
      $task->save();
      $time_start = $time_end;
    }

  }
} else if ($_POST["delete"]) {
  $project->read_globals();
  $project->delete();
  header("location: ".$TPL["url_alloc_projectList"]);
}


if ($projectID) {

  if ($_POST["person_save"] || $_POST["person_delete"]) {
    $project_person = new projectPerson;

    if ($_POST["person_projectPersonID"]) {
      // Read current values from database so we don't loose any fields that are not set by the form
      $project_person->set_id($_POST["person_projectPersonID"]);
      $project_person->select();
    }

    $project_person->read_globals();
    $project_person->read_globals("person_");

    if ($_POST["person_save"]) {
      $project_person->save();
    } else if ($_POST["person_delete"]) {
      $project_person->delete();
    }

  } else if ($_POST["commission_save"] || $_POST["commission_delete"]) {
    $commission_item = new projectCommissionPerson;
    $commission_item->read_globals();
    $commission_item->read_globals("commission_");

    if ($_POST["commission_save"]) {
      $commission_item->save();
    } else if ($_POST["commission_delete"]) {
      $commission_item->delete();
    }
  }
  // Displaying a record
  $project->set_id($projectID);
  $project->select() || die("Could not load project $projectID");
} else {
  // Creating a new record
  $project->read_globals();
  $projectID = $project->get_id();
  $project->select();
}

// Comments
if ($_GET["commentID"] && $_GET["comment_edit"]) {
  $comment = new comment();
  $comment->set_id($_GET["commentID"]);
  $comment->select();
  $TPL["comment"] = $comment->get_value('comment');
  $TPL["comment_buttons"] =
    sprintf("<input type=\"hidden\" name=\"comment_id\" value=\"%d\">", $_GET["commentID"])
           ."<input type=\"submit\" name=\"comment_update\" value=\"Save Comment\">";
} else {
  $TPL["comment_buttons"] = "<input type=\"submit\" name=\"comment_save\" value=\"Save Comment\">";
}


// if someone uploads an attachment
if ($_POST["save_attachment"]) {
  move_attachment("project",$projectID);
  header("Location: ".$TPL["url_alloc_project"]."projectID=".$projectID);
}


$project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

$TPL["project_is_agency"] = $project->get_value("is_agency") ? " checked" : "";


$db = new db_alloc;
$project->get_value("clientID") and $clientID_sql = sprintf(" OR clientID = %d",$project->get_value("clientID"));
$query = sprintf("SELECT * FROM client WHERE clientStatus = 'current' ".$clientID_sql." ORDER BY clientName");
$db->query($query);
$TPL["clientOptions"] = get_option("None", "0", $TPL["project_clientID"] == 0)."\n";
$TPL["clientOptions"].= get_options_from_db($db, "clientName", "clientID", $TPL["project_clientID"],55);
$client = $project->get_foreign_object("client");
$client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");

// If a client has been chosen
if ($clientID_sql) {
  $query = sprintf("SELECT * 
                      FROM client 
                 LEFT JOIN clientContact ON client.clientPrimaryContactID = clientContact.clientContactID 
                     WHERE client.clientID = %d "
                   ,$project->get_value("clientID"));

  $db->query($query);
  $row = $db->next_record();

  
  $row["clientStreetAddressOne"] and $one.= $row["clientStreetAddressOne"]."<br/>";
  $row["clientSuburbOne"]        and $one.= $row["clientSuburbOne"]."<br/>";
  $row["clientStateOne"]         and $one.= $row["clientStateOne"]."<br/>";
  $row["clientPostcodeOne"]      and $one.= $row["clientPostcodeOne"]."<br/>";
  $row["clientCountryOne"]       and $one.= $row["clientCountryOne"]."<br/>";

  $row["clientStreetAddressTwo"] and $two.= $row["clientStreetAddressTwo"]."<br/>";
  $row["clientSuburbTwo"]        and $two.= $row["clientSuburbTwo"]."<br/>";
  $row["clientStateTwo"]         and $two.= $row["clientStateTwo"]."<br/>";
  $row["clientPostcodeTwo"]      and $two.= $row["clientPostcodeTwo"]."<br/>";
  $row["clientCountryTwo"]       and $two.= $row["clientCountryTwo"]."<br/>";

  $row["clientContactName"]      and $thr.= $row["clientContactName"]."<br/>";
  $row["clientContactPhone"]     and $thr.= $row["clientContactPhone"]."<br/>";
  $row["clientContactMobile"]    and $thr.= $row["clientContactMobile"]."<br/>";
  $row["clientContactFax"]       and $thr.= $row["clientContactFax"]."<br/>";
  $row["clientContactEmail"]     and $thr.= $row["clientContactEmail"]."<br/>";

  $project->get_value("projectClientName")    and $fou.= $project->get_value("projectClientName")."<br/>";
  $project->get_value("projectClientAddress") and $fou.= $project->get_value("projectClientAddress")."<br/>";
  $project->get_value("projectClientPhone")   and $fou.= $project->get_value("projectClientPhone")."<br/>";
  $project->get_value("projectClientMobile")  and $fou.= $project->get_value("projectClientMobile")."<br/>";
  $project->get_value("projectClientEMail")   and $fou.= $project->get_value("projectClientEMail")."<br/>";

  $temp = str_replace("<br/>","",$fou);
  $temp and $thr = $fou;

  if ($project->get_value("clientContactID")) {
    $q = sprintf("SELECT * FROM clientContact WHERE clientContactID = %d",$project->get_value("clientContactID"));  
    $db->query($q);
    $db->next_record();
    $db->f("clientContactName")          and $fiv .= "<nobr>".$db->f("clientContactName")."</nobr><br/>";
    $db->f("clientContactStreetAddress") and $fiv .= $db->f("clientContactStreetAddress")."<br/>";
    $db->f("clientContactSuburb")        and $fiv .= $db->f("clientContactSuburb")."<br/>";
    $db->f("clientContactPostcode")      and $fiv .= $db->f("clientContactPostcode")."<br/>";
    $db->f("clientContactPhone")         and $fiv .= $db->f("clientContactPhone")."<br/>";
    $db->f("clientContactMobile")        and $fiv .= $db->f("clientContactMobile")."<br/>";
    $db->f("clientContactEmail")         and $fiv .= $db->f("clientContactEmail")."<br/>";
    $temp = str_replace("<br/>","",$fiv);
    $temp and $thr = $fiv;
  }

  $TPL["clientDetails"] = "<table width=\"70%\">";
  $TPL["clientDetails"].= "<tr><td class=\"nobr\"><b>Postal Address</b></td><td class=\"nobr\"><b>Street Address</b></td><td><b>Contact</b></td></tr>";
  $TPL["clientDetails"].= "<tr><td valign=\"top\">".$one."</td><td valign=\"top\">".$two."</td><td valign=\"top\">".$thr."</td></tr>";
  $TPL["clientDetails"].= "</table>";
}


$TPL["clientContactDropdown"] = client::get_client_contact_select($project->get_value("clientID"),$project->get_value("clientContactID"));


$options["showHeader"] = true;
$options["taskView"] = "byProject";
$options["projectIDs"] = array($project->get_id());   
$options["taskStatus"] = "not_completed";
$options["showAssigned"] = true;
$options["showTimes"] = true;

// Gets $ per hour, even if user uses metric like $200 Daily
function get_projectPerson_hourly_rate($personID,$projectID) {
  $db = new db_alloc;
  $q = sprintf("SELECT rate,rateUnitID FROM projectPerson WHERE personID = %d AND projectID = %d",$personID,$projectID);
  $db->query($q);
  $db->next_record();
  $rate = $db->f("rate");
  $unitID = $db->f("rateUnitID");
  $t = new timeUnit;
  $timeUnits = $t->get_assoc_array("timeUnitID","timeUnitSeconds",$unitID);
  ($rate && $timeUnits[$unitID]) and $hourly_rate = $rate / ($timeUnits[$unitID]/60/60);
  return $hourly_rate;
}


if ($project->get_id()) { 
  $TPL["task_summary"] = task::get_task_list($options);

  $options["return"] = "objects";
  $tasks = task::get_task_list($options);
  if (is_array($tasks)) {
    foreach ($tasks as $tid => $t) {
      $hourly_rate = get_projectPerson_hourly_rate($t["personID"],$t["projectID"]);

      $time_remaining = $t["timeEstimate"] - (task::get_time_billed($t["taskID"])/60/60);

      #echo "<br/>TR: ".$t["timeEstimate"] ." - ". (task::get_time_billed($t["taskID"])/60/60);
      #echo "<br>CR: ".$hourly_rate." * ".$time_remaining;

      $cost_remaining = $hourly_rate * $time_remaining;

      if ($cost_remaining > 0) {
        #echo "<br/>Tally: ".$TPL["cost_remaining"] += $cost_remaining; 
        $TPL["cost_remaining"] += $cost_remaining; 
        $TPL["time_remaining"] += $time_remaining;
      } 
      $t["timeEstimate"] and $count_quoted_tasks++;
    }
    $TPL["cost_remaining"] = sprintf("%0.2f",$TPL["cost_remaining"]);
    $TPL["time_remaining"] = sprintf("%0.1f",$TPL["time_remaining"]);

    $TPL["count_incomplete_tasks"] = count($tasks);
    $not_quoted = count($tasks) - $count_quoted_tasks;
    $TPL["count_not_quoted_tasks"] = sprintf("%d",$not_quoted);
  }
}




$TPL["navigation_links"] = $project->get_navigation_links();


$query = sprintf("SELECT * FROM tf ORDER BY tfName");
$db->query($query);
$tf_array = get_array_from_db($db, "tfID", "tfName");
$TPL["commission_tf_options"] = get_select_options($tf_array, $TPL["commission_tfID"]);



$query = sprintf("SELECT * FROM tf ORDER BY tfName");
$db->query($query);
$cost_centre_tfID_options = get_options_from_db($db, "tfName", "tfID", $TPL["project_cost_centre_tfID"]);

if ($cost_centre_tfID_options) {
$TPL["cost_centre_label"] = "Cost Centre TF";
$TPL["cost_centre_bit"] = "<select name=\"cost_centre_tfID\"><option value=\"\">&nbsp;</option>";
$TPL["cost_centre_bit"].= $cost_centre_tfID_options;
$TPL["cost_centre_bit"].= "</select>";
} else {
  $TPL["cost_centre_label"] = "";
  $TPL["cost_centre_bit"].= "";
}


$query = sprintf("SELECT projectPersonRoleName,projectPersonRoleID FROM projectPersonRole ORDER BY projectPersonRoleSortKey");
$db->query($query);
#$project_person_role_array[] = "";
while ($db->next_record()) {
  $project_person_role_array[$db->f("projectPersonRoleID")] = $db->f("projectPersonRoleName");
}



$email_type_array = array("None"=>"None", "Assigned Tasks"=>"Assigned Tasks", "All Tasks"=>"All Tasks");
$currency_array = array("AUD"=>"AUD", "USD"=>"USD", "NZD"=>"NZD", "CAD"=>"CAD");
$projectType_array = array("contract"=>"Contract", "job"=>"Job", "project"=>"Project");
$projectStatus_array = array("current"=>"Current", "potential"=>"Potential", "archived"=>"Archived");
$timeUnit = new timeUnit;
$rate_type_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelB");
$TPL["projectType_options"] = get_select_options($projectType_array, $TPL["project_projectType"]);
$TPL["projectStatus_options"] = get_select_options($projectStatus_array, $TPL["project_projectStatus"]);
$TPL["project_projectPriority"] or $TPL["project_projectPriority"] = 3;
$TPL["projectPriority_options"] = get_options_from_array(array(1, 2, 3, 4, 5), $TPL["project_projectPriority"], false);
$TPL["currencyType_options"] = get_select_options($currency_array, $TPL["project_currencyType"]);

if ($_GET["projectID"] || $_POST["projectID"] || $TPL["project_projectID"]) {
  define("PROJECT_EXISTS",1);
}


if ($project->have_perm(PERM_READ_WRITE)) {
  include_template("templates/projectFormM.tpl");
} else {
  include_template("templates/projectViewM.tpl");
}

page_close();



?>
