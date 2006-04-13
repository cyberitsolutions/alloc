<?php

/*
 *
 * Copyright 2006, Alex Lance, Clancy Malcolm, Cybersource Pty. Ltd.
 * 
 * This file is part of AllocPSA <info@cyber.com.au>.
 * 
 * AllocPSA is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 * 
 * AllocPSA is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * AllocPSA; if not, write to the Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 */

require_once("alloc.inc");




  function show_attachments($template_name) {
    global $projectID;
    $project = new project;
    $project->set_id($projectID);
    $project->set_tpl_values();
    include_template($template_name);
  }

  function list_attachments($template_name) {
    global $TPL, $projectID;
    if ($projectID) {
      if (is_dir($TPL["url_alloc_projectDocs_dir"].$projectID)) {
        $handle = opendir($TPL["url_alloc_projectDocs_dir"].$projectID);

        while (false !== ($file = readdir($handle))) {

          if ($file != "." && $file != "..") {
            $size = filesize($TPL["url_alloc_projectDocs_dir"].$projectID."/".$file);
            $TPL["filename"] = "<a href=\"".$TPL["url_alloc_projectDoc"]."projectID=".$projectID."&file=".urlencode($file)."\">".$file."</a>";
            $TPL["size"] = sprintf("%dk",$size/1024);
            include_template($template_name);
          }
        }
      }
    }
  }

$grand_total = 0;

  function show_timeSheet($template) {
    global $db, $TPL, $project, $current_user, $projectBudget, $grand_total;

    $projectID = $project->get_id();
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
    global $db, $TPL, $project, $current_user, $projectBudget, $grand_total;

    $projectID = $project->get_id();
    $transaction = new transaction;

    if (isset($projectID) && $projectID) {
      if (have_entity_perm("transaction", PERM_READ, $current_user, false)) {
        $query = sprintf("SELECT transaction.* ")
          .sprintf("FROM transaction ")
          .sprintf("WHERE transaction.projectID = '%d' ", $projectID)
          .sprintf("ORDER BY lastModified desc");
      } else {
        $query = sprintf("SELECT transaction.* ")
          .sprintf("FROM transaction ")
          .sprintf("WHERE transaction.projectID = '%d' ", $projectID)
          .sprintf(" AND transaction.tfID = %d ", $current_user->get_id())
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
        $p = sprintf("%0.1f", $gt / $pb * 100);
      } else {
        $p = "0";
      }
      $TPL["percentage"] = $p;
    }
  }

  function show_commission_list($template_name) {
    global $TPL, $db, $project;

    $TPL["commission_list_buttons"] = "
      <input type=\"submit\" name=\"commission_save\" value=\"Save\">
      <input type=\"submit\" name=\"commission_delete\" value=\"Delete\">";

    $projectID = $project->get_id();

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
    global $TPL, $project, $projectID;

    // Don't show entry form for new projects
    if (!$projectID) {
      return;
    }

    $TPL["commission_list_buttons"] = "<input type=\"submit\" name=\"commission_save\" value=\"Add\">";
    $commission_item = new projectCommissionPerson;
    $commission_item->set_tpl_values(DST_HTML_ATTRIBUTE, "commission_");
    $TPL["commission_projectID"] = $project->get_id();
    include_template($template_name);
  }

  function show_modification_history($template) {
    global $TPL, $project, $db;
    $projectID = $project->get_id();

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
    global $db, $TPL, $project;
    global $email_type_array, $rate_type_array, $project_person_role_array;




    $projectID = $project->get_id();

    if ($projectID) {
      $query = sprintf("SELECT * from projectPerson WHERE projectID=%d", $projectID);
      $db->query($query);

      while ($db->next_record()) {
        $projectPerson = new projectPerson;
        $projectPerson->read_db_record($db);
        $projectPerson->set_tpl_values(DST_HTML_ATTRIBUTE, "person_");
        $person = $projectPerson->get_foreign_object("person");
        $TPL["person_username"] = $person->get_value("username");
        $TPL["person_emailType_options"] = get_options_from_array($email_type_array, $TPL["person_emailType"]);
        $TPL["person_projectPersonRole_options"] = get_options_from_array($project_person_role_array, $TPL["person_projectPersonRoleID"]);
        $TPL["rateType_options"] = get_options_from_array($rate_type_array, $TPL["person_rateUnitID"]);
        $TPL["person_buttons"] = "
          <input type=\"submit\" name=\"person_save\" value=\"Save\">
          <input type=\"submit\" name=\"person_delete\" value=\"Delete\">";
        // <a href=\"".$TPL["url_alloc_projectPerson"]."projectPersonID=".$projectPerson->get_id()."\">Details</a>"
        include_template($template);
      }
    }
  }

  function show_new_person($template) {
    global $TPL, $email_type_array, $rate_type_array, $project, $project_person_role_array;

    // Don't show entry form for new projects
    if (!$project->get_id()) {
      return;
    }

    $TPL["person_buttons"] = "<input type=\"submit\" name=\"person_save\" value=\"Add\">";
    $project_person = new projectPerson;
    $project_person->set_tpl_values(DST_HTML_ATTRIBUTE, "person_");
    $TPL["person_emailType_options"] = get_options_from_array($email_type_array, $TPL["person_emailType"]);
    $TPL["person_projectPersonRole_options"] = get_options_from_array($project_person_role_array,false);
    $TPL["rateType_options"] = get_options_from_array($rate_type_array, $TPL["person_rateUnitID"]);
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
    echo get_options_from_array($tf_array, $TPL[$commission_tfID]);
  }

  // show table of comments
  function show_comments($template) {
    global $TPL, $projectID, $commentID;

    // setup add/edit comment section values
    $TPL["project_projectID"] = $projectID;
    $TPL["project_projectComment_title"] = "New comment:";
    $TPL["project_projectComment"] = "";
    $TPL["project_projectComment_buttons"] = "<input type=\"submit\" name=\"projectComment_save\" value=\"Save Comment\">";

    $query = "SELECT * FROM comment LEFT JOIN person ON comment.commentModifiedUser=person.personID";
    $query.= sprintf(" WHERE comment.commentType='project' AND comment.commentLinkID=%d", $projectID);
    $query.= " ORDER BY comment.commentModifiedTime DESC";
    $db = new db_alloc;
    $db->query($query);

    while ($db->next_record()) {
      $comment = new comment;
      $comment->read_db_record($db);
      $comment->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");
      $person = new person;
      $person->read_db_record($db);
      $person->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

      // if a commentID was posted and it is equal to this one then setup values for editing
      if (isset($commentID) && $commentID == $comment->get_id()) {
        $TPL["project_projectComment_title"] = "Edit comment:";
        $TPL["project_projectComment"] = $comment->get_value('comment');
        $TPL["project_projectComment_buttons"] =
          sprintf("<input type=\"hidden\" name=\"projectComment_id\" value=\"%d\">", $commentID).
          "<input type=\"submit\" name=\"projectComment_update\" value=\"Update\">"."<input type=\"submit\" name=\"projectComment_delete\" value=\"Delete\">"."<input type=\"submit\" name=\"projectComment_cancel\" value=\"Cancel\">";
      }

      $TPL["project_commentModifiedDate"] = $comment->get_modified_date();

      // trim comment to 128 characters
      if (strlen($comment->get_value('comment')) > 128) {
        $TPL["project_comment_trimmed"] = sprintf("%s...", substr($comment->get_value('comment'), 0, 128));
      } else {
        $TPL["project_comment_trimmed"] = $comment->get_value('comment');
      }

      include_template($template);
    }
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
      $TPL["returnToParent"] = "t";

      include_template($template);
    }
  }


// END FUNCTIONS


$project = new project;


global $projectID, $current_user;

if ($projectID) {
  $project->set_id($projectID);
  $project->check_perm();
  $new_project = false;
} else {
  $new_project = true;
}


if (isset($save)) {
  $project->read_globals();
  $project->set_value("is_agency", $project_is_agency ? 1 : 0);

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
    $projectPerson->set_value("emailEmptyTaskList", "None");
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
      $task->set_value("percentComplete", "0");
      $task->set_value("parentTaskID", "0");
      $task->set_value("taskTypeID", TT_PHASE);
      $task->save();
      $time_start = $time_end;
    }

  }
} else if (isset($delete)) {
  $project->read_globals();
  $project->delete();
  header("location: ".$TPL["url_alloc_projectList"]);
}


if ($projectID) {

  if ($person_save || $person_delete) {
    $project_person = new projectPerson;

    if ($person_projectPersonID) {
      // Read current values from database so we don't loose any fields that are not set by the form
      $project_person->set_id($person_projectPersonID);
      $project_person->select();
    }

    $project_person->read_globals();
    $project_person->read_globals("person_");

    if ($person_save) {
      $project_person->save();
    } else if ($person_delete) {
      $project_person->delete();
    }

  } else if ($commission_save || $commission_delete) {
    $commission_item = new projectCommissionPerson;
    $commission_item->read_globals();
    $commission_item->read_globals("commission_");

    if ($commission_save) {
      $commission_item->save();
    } else if ($commission_delete) {
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
if (isset($projectComment_save) || isset($projectComment_update)) {
  $comment = new comment;
  $comment->set_value('commentType', 'project');
  $comment->set_value('commentLinkID', $projectID);
  $comment->set_modified_time();
  $comment->set_value('commentModifiedUser', $current_user->get_id());

  if (isset($projectComment_update)) {
    $comment->set_id($projectComment_id);
  }

  if (isset($projectComment)) {
    $comment->set_value('comment', $projectComment);
    $comment->save();
  }
}


if (isset($projectComment_delete) && isset($projectComment_id)) {
  $comment = new comment;
  $comment->set_id($projectComment_id);
  $comment->delete();
}

if (isset($projectComment_cancel)) {
  unset($commentID);
}
// if someone uploads an attachment

if ($save_attachment) {

  if ($attachment != "none") {
    is_uploaded_file($attachment) || die("Uploaded document error.  Please try again.");

    if (!is_dir($TPL["url_alloc_projectDocs_dir"].$projectID)) {
      mkdir($TPL["url_alloc_projectDocs_dir"].$projectID, 0777);
    }

    if (!move_uploaded_file($attachment, $TPL["url_alloc_projectDocs_dir"].$projectID."/".$attachment_name)) {
      die("could not move attachment to: ".$TPL["url_alloc_projectDocs_dir"].$projectID."/".$attachment_name);
    } else {
      chmod($TPL["url_alloc_projectDocs_dir"].$projectID."/".$attachment_name, 0777);
      header("Location: ".$TPL["url_alloc_project"]."projectID=".$projectID);
    }
  }
}


$project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

$TPL["project_is_agency"] = $project->get_value("is_agency") ? " checked" : "";


$db = new db_alloc;
$query = sprintf("SELECT * FROM client ORDER BY clientName");
$db->query($query);
$TPL["clientOptions"] = get_option("None", "0", $TPL["project_clientID"] == 0)."\n";
$TPL["clientOptions"].= get_options_from_db($db, "clientName", "clientID", $TPL["project_clientID"]);
$client = $project->get_foreign_object("client");
$client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");

$options["taskView"] = "byProject";
$options["projectIDs"] = array($project->get_id());   
$options["taskStatus"] = "not_completed";
$options["showAssigned"] = true;
    
$project->get_id() and $TPL["task_summary"] = task::get_task_list($options);

$TPL["navigation_links"] = $project->get_navigation_links();


$query = sprintf("SELECT * FROM tf ORDER BY tfName");
$db->query($query);
$tf_array = get_array_from_db($db, "tfID", "tfName");
$TPL["commission_tf_options"] = get_options_from_array($tf_array, $TPL["commission_tfID"]);



$query = sprintf("SELECT * FROM tf ORDER BY tfName");
$db->query($query);
$cost_centre_tfID_options = get_options_from_db($db, "tfName", "tfID", $TPL["project_cost_centre_tfID"]);

if ($cost_centre_tfID_options) {
$TPL["cost_centre_label"] = "Cost Centre TF (Time<br>Sheets will use this TF)";
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
$TPL["projectType_options"] = get_options_from_array($projectType_array, $TPL["project_projectType"]);
$TPL["projectStatus_options"] = get_options_from_array($projectStatus_array, $TPL["project_projectStatus"]);
$TPL["project_projectPriority"] or $TPL["project_projectPriority"] = 3;
$TPL["projectPriority_options"] = get_options_from_array(array(1, 2, 3, 4, 5), $TPL["project_projectPriority"], false);
$TPL["currencyType_options"] = get_options_from_array($currency_array, $TPL["project_currencyType"]);


if ($project->have_perm(PERM_READ_WRITE)) {
  include_template("templates/projectFormM.tpl");
} else {
  include_template("templates/projectViewM.tpl");
}

page_close();



?>
