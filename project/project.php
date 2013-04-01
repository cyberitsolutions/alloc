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




  function show_attachments() {
    global $projectID;
    util_show_attachments("project",$projectID);
  }

  function list_attachments($template_name) {
    global $TPL;
    global $projectID;

    if ($projectID) {
      $rows = get_attachments("project",$projectID);
      foreach ($rows as $row) {
        $TPL = array_merge($TPL,$row);
        include_template($template_name);
      }
    }
  }

  function show_transaction($template) {
    global $db;
    global $TPL;
    global $projectID;
    $current_user = &singleton("current_user");

    $transaction = new transaction();

    if (isset($projectID) && $projectID) {
      $query = prepare("SELECT transaction.*
                          FROM transaction
                          WHERE transaction.projectID = %d
                      ORDER BY transactionModifiedTime desc
                        ", $projectID);
      $db->query($query);
      while ($db->next_record()) {
        $transaction = new transaction();
        $transaction->read_db_record($db);
        $transaction->set_values("transaction_");

        $tf = $transaction->get_foreign_object("tf");
        $tf->set_values();
        $tf->set_values("tf_");

        $TPL["transaction_username"] = $db->f("username");
        $TPL["transaction_amount"] = page::money($TPL["transaction_currenyTypeID"],$TPL["transaction_amount"],"%s%mo");
        $TPL["transaction_type_link"] = $transaction->get_transaction_type_link() or $TPL["transaction_link"] = $transaction->get_value("transactionType");

        include_template($template);

      }


    }
  }

  function show_invoices() {
    $current_user = &singleton("current_user");
    global $project;
    $clientID = $project->get_value("clientID");
    $projectID = $project->get_id();

    $_FORM["showHeader"] = true;
    $_FORM["showInvoiceNumber"] = true;
    $_FORM["showInvoiceClient"] = true;
    $_FORM["showInvoiceName"] = true;
    $_FORM["showInvoiceAmount"] = true;
    $_FORM["showInvoiceAmountPaid"] = true;
    $_FORM["showInvoiceDate"] = true;
    $_FORM["showInvoiceStatus"] = true;
    $_FORM["clientID"] = $clientID;
    $_FORM["projectID"] = $projectID;

    // Restrict non-admin users records  
    if (!$current_user->have_role("admin")) {
      $_FORM["personID"] = $current_user->get_id();  
    }

    $rows = invoice::get_list($_FORM);
    echo invoice::get_list_html($rows,$_FORM);
  }
  
  function show_projectHistory() {
    global $project;
    global $TPL;
    $TPL["changeHistory"] = $project->get_changes_list();
    include_template("templates/projectHistoryM.tpl");
  }

  function show_commission_list($template_name) {
    global $TPL;
    global $db;
    global $projectID;

    if ($projectID) {
      $query = prepare("SELECT * from projectCommissionPerson WHERE projectID= %d", $projectID);
      $db->query($query);

      while ($db->next_record()) {
        $commission_item = new projectCommissionPerson();
        $commission_item->read_db_record($db);
        $commission_item->set_values("commission_");
        $tf = $commission_item->get_foreign_object("tf");
        $TPL["save_label"] = "Save";
        include_template($template_name);
      }
    }
  }

  function show_new_commission($template_name) {
    global $TPL;
    global $projectID;

    // Don't show entry form for new projects
    if (!$projectID) {
      return;
    }
    $TPL["commission_new"] = true;
    $commission_item = new projectCommissionPerson();
    $commission_item->set_values("commission_");
    $TPL["commission_projectID"] = $projectID;
    $TPL["save_label"] = "Add Commission";
    include_template($template_name);
  }

  function show_person_list($template) {
    global $db;
    global $TPL;
    global $projectID;
    global $email_type_array;
    global $rate_type_array;
    global $project_person_role_array;

    if ($projectID) {
      $query = prepare("SELECT projectPerson.*, roleSequence
                          FROM projectPerson 
                     LEFT JOIN role ON role.roleID = projectPerson.roleID
                         WHERE projectID=%d ORDER BY roleSequence DESC,projectPersonID ASC", $projectID);
      $db->query($query);

      while ($db->next_record()) {
        $projectPerson = new projectPerson();
        $projectPerson->read_db_record($db);
        $projectPerson->set_values("person_");
        $person = $projectPerson->get_foreign_object("person");
        $TPL["person_username"] = $person->get_value("username");
        $TPL["person_emailType_options"] = page::select_options($email_type_array, $TPL["person_emailType"]);
        $TPL["person_role_options"] = page::select_options($project_person_role_array, $TPL["person_roleID"]);
        $TPL["rateType_options"] = page::select_options($rate_type_array, $TPL["person_rateUnitID"]);
        include_template($template);
      }
    }
  }

  function show_projectPerson_list() {
    global $db;
    global $TPL;
    global $projectID;
    $template = "templates/projectPersonSummaryViewR.tpl";

    if ($projectID) {
      $query = prepare("SELECT personID, roleName
                          FROM projectPerson
                     LEFT JOIN role ON role.roleID = projectPerson.roleID
                         WHERE projectID = %d 
                      GROUP BY projectPerson.personID
                      ORDER BY roleSequence DESC, personID ASC", $projectID);
      $db->query($query);
      while ($db->next_record()) {
        $projectPerson = new projectPerson();
        $projectPerson->read_db_record($db);
        $TPL['person_roleName'] = $db->f("roleName");
        $TPL['person_name'] = person::get_fullname($projectPerson->get_value('personID'));
        include_template($template);
      }
    }
  }

  function show_new_person($template) {
    global $TPL;
    global $email_type_array;
    global $rate_type_array;
    global $projectID;
    global $project_person_role_array;

    // Don't show entry form for new projects
    if (!$projectID) {
      return;
    }
    $project_person = new projectPerson();
    $project_person->set_values("person_");
    $TPL["person_emailType_options"] = page::select_options($email_type_array, $TPL["person_emailType"]);
    $TPL["person_role_options"] = page::select_options($project_person_role_array,false);
    $TPL["rateType_options"] = page::select_options($rate_type_array);
    include_template($template);
  }

  function show_time_sheets($template_name) {
    $current_user = &singleton("current_user");

    if ($current_user->is_employee()) {
      include_template($template_name);
    }
  }

  function show_project_managers($template_name) {
    include_template($template_name);
  }

  function show_transactions($template_name) {
    $current_user = &singleton("current_user");

    if ($current_user->is_employee()) {
      include_template($template_name);
    }
  }

  function show_person_options() {
    global $TPL;
    echo page::select_options(person::get_username_list($TPL["person_personID"]),$TPL["person_personID"]);
  }

  function show_tf_options($commission_tfID) {
    global $tf_array;
    global $TPL;
    echo page::select_options($tf_array, $TPL[$commission_tfID]);
  }

  function show_comments() {
    global $projectID;
    global $TPL;
    global $project;
    $TPL["commentsR"] = comment::util_get_comments("project",$projectID);
    $TPL["commentsR"] and $TPL["class_new_comment"] = "hidden";
    $interestedPartyOptions = $project->get_all_parties();
    $interestedPartyOptions = interestedParty::get_interested_parties("project",$project->get_id()
                                                                     ,$interestedPartyOptions);
    $TPL["allParties"] = $interestedPartyOptions or $TPL["allParties"] = array();
    $TPL["entity"] = "project";
    $TPL["entityID"] = $project->get_id();
    $TPL["clientID"] = $project->get_value("clientID");

    $commentTemplate = new commentTemplate();
    $ops = $commentTemplate->get_assoc_array("commentTemplateID","commentTemplateName","",array("commentTemplateType"=>"project"));
    $TPL["commentTemplateOptions"] = "<option value=\"\">Comment Templates</option>".page::select_options($ops);

    $ops = array(""=>"Format as...","pdf"=>"PDF","pdf_plus"=>"PDF+","html"=>"HTML","html_plus"=>"HTML+");

    $TPL["attach_extra_files"] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $TPL["attach_extra_files"].= "Attach Task Report ";
    $TPL["attach_extra_files"].= '<select name="attach_tasks">'.page::select_options($ops).'</select><br>';

    include_template("../comment/templates/commentM.tpl");
  }

  function show_tasks() {
    global $TPL;
    global $project;
    $options["showHeader"] = true;
    $options["taskView"] = "byProject";
    $options["projectIDs"] = array($project->get_id());   
    $options["taskStatus"] = array("open","pending");
    $options["showTaskID"] = true;
    $options["showAssigned"] = true;
    $options["showStatus"] = true;
    $options["showManager"] = true;
    $options["showDates"] = true;
    #$options["showTimes"] = true; // performance hit
    $options["return"] = "html";
    // $TPL["taskListRows"] is used for the budget estimatation outside of this function
    $TPL["taskListRows"] = task::get_list($options); 
    $TPL["_FORM"] = $options;
    include_template("templates/projectTaskS.tpl"); 
  }

  function show_import_export($template) {
    include_template($template);
  }


// END FUNCTIONS




$current_user = &singleton("current_user");

$projectID = $_POST["projectID"] or $projectID = $_GET["projectID"];

$project = new project();

if ($projectID) {
  $project->set_id($projectID);
  $project->select();
  $new_project = false;
  if (!$project->have_perm(PERM_UPDATE)) {
    $TPL["message_help"][] = "Project is read-only for you.";
  }
} else {
  $new_project = true;
}



if ($_POST["save"]) {
  $project->read_globals();

  if (!$project->get_id()) {    // brand new project
    $definitely_new_project = true;
  }

  if (!$project->get_value("projectName")) {  
    alloc_error("Please enter a name for the Project.");
  }  

  // enforced at the database, but show a friendlier error here if possible
  $query = prepare("SELECT COUNT(*) as count FROM project WHERE projectShortName = '%s'", $db->esc($project->get_value("projectShortName")));
  if (!$definitely_new_project) {
    $query .= prepare(" AND projectID != %d", $project->get_id());
  }
  $db->query($query);
  $db->next_record();
  if ($db->f('count') > 0) {
    alloc_error("A project with that nickname already exists.");
  }

  if (!$TPL["message"]) {

    $project->set_value("projectComments",rtrim($project->get_value("projectComments")));
    $project->save();
    $projectID = $project->get_id();
    interestedParty::make_interested_parties("project",$project->get_id(),$_POST["interestedParty"]);

    $client = new client();
    $client->set_id($project->get_value("clientID"));
    $client->select();
    if ($client->get_value("clientStatus") == 'Potential') {
      $client->set_value("clientStatus", "Current");
      $client->save();
    }
   
    if ($definitely_new_project) {
      $projectPerson = new projectPerson();
      $projectPerson->currency = $project->get_value("currencyTypeID");
      $projectPerson->set_value("projectID", $projectID);
      $projectPerson->set_value_role("isManager");
      $projectPerson->set_value("personID", $current_user->get_id());
      $projectPerson->save();
    }
    alloc_redirect($TPL["url_alloc_project"]."projectID=".$project->get_id());
  }
} else if ($_POST["delete"]) {
  $project->read_globals();
  $project->delete();
  alloc_redirect($TPL["url_alloc_projectList"]);

// If they are creating a new project that is based on an existing one
} else if ($_POST["copy_project_save"] && $_POST["copy_projectID"] && $_POST["copy_project_name"]) {
  
  $p = new project();
  $p->set_id($_POST["copy_projectID"]);
  if ($p->select()) {
    $p2 = new project();
    $p2->read_row_record($p->row());
    $p2->set_id("");
    $p2->set_value("projectName",$_POST["copy_project_name"]);
    $p2->set_value("projectShortName","");
    $p2->save();
    $TPL["message_good"][] = "Project details copied successfully.";

    // Copy project people
    $q = prepare("SELECT * FROM projectPerson WHERE projectID = %d",$p->get_id());
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $projectPerson = new projectPerson();
      $projectPerson->currency = $p->get_value("currencyTypeID");
      $projectPerson->read_row_record($row);
      $projectPerson->set_id("");
      $projectPerson->set_value("projectID",$p2->get_id());
      $projectPerson->save();
      $TPL["message_good"]["projectPeople"] = "Project people copied successfully.";
    }

    // Copy commissions
    $q = prepare("SELECT * FROM projectCommissionPerson WHERE projectID = %d",$p->get_id());
    $db = new db_alloc();
    $db->query($q);
    while ($row = $db->row()) {
      $projectCommissionPerson = new projectCommissionPerson();
      $projectCommissionPerson->read_row_record($row);
      $projectCommissionPerson->set_id("");
      $projectCommissionPerson->set_value("projectID",$p2->get_id());
      $projectCommissionPerson->save();
      $TPL["message_good"]["projectCommissions"] = "Project commissions copied successfully.";
    }

    alloc_redirect($TPL["url_alloc_project"]."projectID=".$p2->get_id());
  }


}




if ($projectID) {

  if ($_POST["person_save"]) {
    $q = prepare("SELECT * FROM projectPerson WHERE projectID = %d",$project->get_id());
    $db = new db_alloc();
    $db->query($q);
    while ($db->next_record()) {
      $pp = new projectPerson();
      $pp->read_db_record($db);
      $delete[] = $pp->get_id();
      #$pp->delete(); // need to delete them after, cause we'll accidently wipe out the current user
    }

    if (is_array($_POST["person_personID"])) {
      foreach ($_POST["person_personID"] as $k => $personID) {
        if ($personID) {
          $pp = new projectPerson();
          $pp->currency = $project->get_value("currencyTypeID");
          $pp->set_value("projectID",$project->get_id());
          $pp->set_value("personID",$personID);
          $pp->set_value("roleID",$_POST["person_roleID"][$k]);
          $pp->set_value("rate",$_POST["person_rate"][$k]);
          $pp->set_value("rateUnitID",$_POST["person_rateUnitID"][$k]);
          $pp->set_value("projectPersonModifiedUser",$current_user->get_id());
          $pp->save();
        }
      }
    }

    if (is_array($delete)) {
      foreach ($delete as $projectPersonID) {
        $pp = new projectPerson();
        $pp->set_id($projectPersonID);
        $pp->delete();
      }
    }

  

  } else if ($_POST["commission_save"] || $_POST["commission_delete"]) {
    $commission_item = new projectCommissionPerson();
    $commission_item->read_globals();
    $commission_item->read_globals("commission_");

    if ($_POST["commission_save"]) {
      if (!$_POST["commission_tfID"]) {
        alloc_error("No TF selected.");
      } else {
        $commission_item->save();
      }
    } else if ($_POST["commission_delete"]) {
      $commission_item->delete();
    }
  } else if ($_POST['do_import']) {
    // Import from an uploaded file
    switch($_POST['import_type']) {
      case 'planner':
        import_gnome_planner('import');
      break;
      case 'csv':
	$fn = store_csv($_FILES['import']['tmp_name']);
	if ($fn) {
	  alloc_redirect($TPL["url_alloc_importCSV"]."projectID=".$projectID."&filename=$fn");
	}
	$TPL['message'] = "There was an error processing the uploaded file.";
      break;
    }
  }
  // Displaying a record
  $project->set_id($projectID);
  $project->select() || alloc_error("Could not load project $projectID");
} else {
  // Creating a new record
  $project->read_globals();
  $projectID = $project->get_id();
  $project->select();
}

// Comments
$TPL["comment_buttons"] = "<input type=\"submit\" name=\"comment_save\" value=\"Save Comment\">";


// if someone uploads an attachment
if ($_POST["save_attachment"]) {
  move_attachment("project",$projectID);
  alloc_redirect($TPL["url_alloc_project"]."projectID=".$projectID."&sbs_link=attachments");
}


$project->set_values("project_");

$db = new db_alloc();

$clientID = $project->get_value("clientID") or $clientID = $_GET["clientID"];
$client = new client();
$client->set_id($clientID);
$client->select();
$client->set_tpl_values("client_");

// If a client has been chosen
if ($clientID) {

  $query = prepare("SELECT * 
                      FROM clientContact
                     WHERE clientContact.clientID = %d AND clientContact.primaryContact = true"
                   ,$clientID);
  $db->query($query);
  $cc = new clientContact();
  $cc->read_db_record($db);
  
  $one = $client->format_address("postal");
  $two = $client->format_address("street");
  $thr = $cc->format_contact();
  $fou = $project->format_client_old();  

  $temp = str_replace("<br>","",$fou);
  $temp and $thr = $fou;

  if ($project->get_value("clientContactID")) {
    $cc = new clientContact();
    $cc->set_id($project->get_value("clientContactID"));
    $cc->select();
    $fiv = $cc->format_contact();
    $temp = str_replace("<br>","",$fiv);
    $temp and $thr = $fiv;
  }

  $TPL["clientDetails"] = "<table width=\"100%\">";
  $TPL["clientDetails"].= "<tr>";
  $TPL["clientDetails"].= "<td colspan=\"3\"><h2 style=\"margin-bottom:0px; display:inline;\">".$TPL["client_clientName"]."</h2></td>";
  $TPL["clientDetails"].= "</tr>";
  $TPL["clientDetails"].= "<tr>";
  $one and $TPL["clientDetails"].= "<td class=\"nobr\"><u>Postal Address</u></td>";
  $two and $TPL["clientDetails"].= "<td class=\"nobr\"><u>Street Address</u></td>";
  $thr and $TPL["clientDetails"].= "<td><u>Contact</u></td>";
  $TPL["clientDetails"].= "</tr>";
  $TPL["clientDetails"].= "<tr>";
  $one and $TPL["clientDetails"].= "<td valign=\"top\">".$one."</td>";
  $two and $TPL["clientDetails"].= "<td valign=\"top\">".$two."</td>";
  $thr and $TPL["clientDetails"].= "<td valign=\"top\">".$thr."</td>";
  $TPL["clientDetails"].= "</tr>";
  $TPL["clientDetails"].= "</table>";
}


$db->query(prepare("SELECT fullName, emailAddress, clientContactPhone, clientContactMobile, interestedPartyActive
                      FROM interestedParty
                 LEFT JOIN clientContact ON interestedParty.clientContactID = clientContact.clientContactID
                     WHERE entity='project' 
                       AND entityID = %d
                       AND interestedPartyActive = 1
                  ORDER BY fullName",$project->get_id()));
while ($db->next_record()) {
  $value = interestedParty::get_encoded_interested_party_identifier($db->f("fullName"), $db->f("emailAddress"));
  $phone = array("p"=>$db->f('clientContactPhone'),"m"=>$db->f('clientContactMobile'));
  $TPL["interestedParties"][] = array('key'=>$value, 'name'=>$db->f("fullName"), 'email'=>$db->f("emailAddress"), 'phone'=>$phone);
}

$TPL["interestedPartyOptions"] = $project->get_cc_list_select();



$TPL["clientContactDropdown"] = "<input type=\"hidden\" name=\"clientContactID\" value=\"".$project->get_value("clientContactID")."\">";
$TPL["clientHidden"] = "<input type=\"hidden\" id=\"clientID\" name=\"clientID\" value=\"".$clientID."\">";
$TPL["clientHidden"].= "<input type=\"hidden\" id=\"clientContactID\" name=\"clientContactID\" value=\"".$project->get_value("clientContactID")."\">";

// Gets $ per hour, even if user uses metric like $200 Daily
function get_projectPerson_hourly_rate($personID,$projectID) {
  $db = new db_alloc();
  $q = prepare("SELECT rate,rateUnitID FROM projectPerson WHERE personID = %d AND projectID = %d",$personID,$projectID);
  $db->query($q);
  $db->next_record();
  $rate = $db->f("rate");
  $unitID = $db->f("rateUnitID");
  $t = new timeUnit();
  $timeUnits = $t->get_assoc_array("timeUnitID","timeUnitSeconds",$unitID);
  ($rate && $timeUnits[$unitID]) and $hourly_rate = $rate / ($timeUnits[$unitID]/60/60);
  return $hourly_rate;
}

if (is_object($project) && $project->get_id()) {
  if (is_array($TPL["taskListRows"])) { // $tasks is a global defined in show_tasks() for performance reasons
    foreach ($TPL["taskListRows"] as $tid => $t) {
      $hourly_rate = get_projectPerson_hourly_rate($t["personID"],$t["projectID"]);
      $time_remaining = $t["timeLimit"] - (task::get_time_billed($t["taskID"])/60/60);

      $cost_remaining = $hourly_rate * $time_remaining;

      if ($cost_remaining > 0) {
        #echo "<br>Tally: ".$TPL["cost_remaining"] += $cost_remaining; 
        $TPL["cost_remaining"] += $cost_remaining; 
        $TPL["time_remaining"] += $time_remaining;
      } 
      $t["timeLimit"] and $count_quoted_tasks++;
    }


    $TPL["time_remaining"] and $TPL["time_remaining"] = sprintf("%0.1f",$TPL["time_remaining"])." Hours.";

    $TPL["count_incomplete_tasks"] = count($TPL["taskListRows"]);
    $not_quoted = count($TPL["taskListRows"]) - $count_quoted_tasks;
    $not_quoted and $TPL["count_not_quoted_tasks"] = "(".sprintf("%d",$not_quoted)." tasks not included in estimate)";
  }


  $TPL["invoice_links"].= "<a href=\"".$TPL["url_alloc_invoice"]."clientID=".$clientID."&projectID=".$project->get_id()."\">New Invoice</a>";
}

$TPL["navigation_links"] = $project->get_navigation_links();

$query = prepare("SELECT tfID AS value, tfName AS label 
                    FROM tf 
                   WHERE tfActive = 1
                ORDER BY tfName");
$TPL["commission_tf_options"] = page::select_options($query, $TPL["commission_tfID"]);
$TPL["cost_centre_tfID_options"] = page::select_options($query, $TPL["project_cost_centre_tfID"]);

$db->query($query);
while ($db->row()) {
  $tf_array[$db->f("value")] = $db->f("label");
}

if ($TPL["project_cost_centre_tfID"]) {
  $tf = new tf();
  $tf->set_id($TPL["project_cost_centre_tfID"]);
  $tf->select();
  $TPL["cost_centre_tfID_label"] = $tf->get_link();
}



$query = prepare("SELECT roleName,roleID FROM role WHERE roleLevel = 'project' ORDER BY roleSequence");
$db->query($query);
#$project_person_role_array[] = "";
while ($db->next_record()) {
  $project_person_role_array[$db->f("roleID")] = $db->f("roleName");
}



$email_type_array = array("None"=>"None", "Assigned Tasks"=>"Assigned Tasks", "All Tasks"=>"All Tasks");

$t = new meta("currencyType");
$currency_array = $t->get_assoc_array("currencyTypeID","currencyTypeID");
$projectType_array = project::get_project_type_array();

$m = new meta("projectStatus");
$projectStatus_array = $m->get_assoc_array("projectStatusID","projectStatusID");
$timeUnit = new timeUnit();
$rate_type_array = $timeUnit->get_assoc_array("timeUnitID","timeUnitLabelB");
$TPL["project_projectType"] = $projectType_array[$TPL["project_projectType"]];
$TPL["projectType_options"] = page::select_options($projectType_array, $TPL["project_projectType"]);
$TPL["projectStatus_options"] = page::select_options($projectStatus_array, $TPL["project_projectStatus"]);
$TPL["project_projectPriority"] or $TPL["project_projectPriority"] = 3;
$projectPriorities = config::get_config_item("projectPriorities") or $projectPriorities = array();
$tp = array();
foreach($projectPriorities as $key => $arr) {
  $tp[$key] = $arr["label"];
}
$TPL["projectPriority_options"] = page::select_options($tp,$TPL["project_projectPriority"]);
$TPL["project_projectPriority"] and $TPL["priorityLabel"] = " <div style=\"display:inline; color:".$projectPriorities[$TPL["project_projectPriority"]]["colour"]."\">[".$tp[$TPL["project_projectPriority"]]."]</div>";


$TPL["defaultTimeSheetRate"] = $project->get_value("defaultTimeSheetRate");
$TPL["defaultTimeSheetUnit_options"] = page::select_options($rate_type_array, $project->get_value("defaultTimeSheetRateUnitID"));
$TPL["defaultTimeSheetRateUnits"] = $rate_type_array[$project->get_value("defaultTimeSheetRateUnitID")];

$TPL["currencyType_options"] = page::select_options($currency_array, $TPL["project_currencyTypeID"]);

if ($_GET["projectID"] || $_POST["projectID"] || $TPL["project_projectID"]) {
  define("PROJECT_EXISTS",1);
}

if ($new_project && !(is_object($project) && $project->get_id())) {
  $TPL["main_alloc_title"] = "New Project - ".APPLICATION_NAME;
  $TPL["projectSelfLink"] = "New Project";
  $p = new project();
  $TPL["message_help_no_esc"][] = "Create a new Project by inputting the Project Name and any other details, and clicking the Save button.";
  $TPL["message_help_no_esc"][] = "";
  $TPL["message_help_no_esc"][] = "<a href=\"#x\" class=\"magic\" id=\"copy_project_link\">Or copy an existing project</a>";
  $str =<<<DONE
    <div id="copy_project" style="display:none; margin-top:10px;">
      <form action="{$TPL["url_alloc_project"]}" method="post">
        <table>
          <tr>
            <td colspan="2">
              <label for="project_status_current">Current Projects</label>
              <input id="project_status_current" type="radio" name="project_status"  value="Current" checked>
              &nbsp;&nbsp;&nbsp;
              <label for="project_status_potential">Potential Projects</label>
              <input id="project_status_potential" type="radio" name="project_status"  value="Potential">
              &nbsp;&nbsp;&nbsp;
              <label for="project_status_archived">Archived Projects</label>
              <input id="project_status_archived" type="radio" name="project_status"  value="Archived">
            </td>
          </tr>
          <tr>
            <td>Existing Project</td><td><div id="projectDropdown"><select name="copy_projectID"></select></div></td>
          </tr>
          <tr>
            <td>New Project Name</td><td><input type="text" size="50" name="copy_project_name"></td>
          </tr>
          <tr>
            <td colspan="2" align="center"><input type="submit" name="copy_project_save" value="Copy Project"></td>
          </tr>
        </table>
      <input type="hidden" name="sessID" value="{$TPL["sessID"]}">
      </form>
    </div>
DONE;
  $TPL["message_help_no_esc"][] = $str;

} else {
  $TPL["main_alloc_title"] = "Project " . $project->get_id() . ": " . $project->get_name() . " - ".APPLICATION_NAME;
  $TPL["projectSelfLink"] = "<a href=\"". $project->get_url() . "\">";
  $TPL["projectSelfLink"] .=  sprintf("%d %s", $project->get_id(), $project->get_name(array("return"=>"html")));
  $TPL["projectSelfLink"] .= "</a>";
}

$TPL["taxName"] = config::get_config_item("taxName");

// Need to html-ise projectName and description
$TPL["project_projectName_html"] = page::to_html($project->get_value("projectName"));
$TPL["project_projectComments_html"] = page::to_html($project->get_value("projectComments"));

$db = new db_alloc();

$q = prepare("SELECT SUM((amount * pow(10,-currencyType.numberToBasic))) 
                  AS amount, transaction.currencyTypeID as currency
                FROM transaction
           LEFT JOIN timeSheet on timeSheet.timeSheetID = transaction.timeSheetID
           LEFT JOIN currencyType on currencyType.currencyTypeID = timeSheet.currencyTypeID
               WHERE timeSheet.projectID = %d
                 AND transaction.status = 'pending'
            GROUP BY transaction.currencyTypeID
              ",$project->get_id());
$db->query($q);
unset($rows);
while ($row = $db->row()) {
  $rows[] = $row;
}
$TPL["total_timeSheet_transactions_pending"] = page::money_print($rows);

$q = prepare("SELECT SUM(customerBilledDollars * timeSheetItemDuration * multiplier * pow(10,-currencyType.numberToBasic))
                  AS amount, timeSheet.currencyTypeID as currency
                FROM timeSheetItem 
           LEFT JOIN timeSheet ON timeSheetItem.timeSheetID = timeSheet.timeSheetID
           LEFT JOIN currencyType on currencyType.currencyTypeID = timeSheet.currencyTypeID
               WHERE timeSheet.projectID = %d
            GROUP BY timeSheetItemID
                ",$project->get_id());
$db->query($q);
unset($rows);
while ($row = $db->row()) {
  $rows[] = $row;
}
$TPL["total_timeSheet_customerBilledDollars"] = page::money_print($rows);

$q = prepare("SELECT SUM((amount * pow(10,-currencyType.numberToBasic))) 
                  AS amount, transaction.currencyTypeID as currency
                FROM transaction
           LEFT JOIN timeSheet on timeSheet.timeSheetID = transaction.timeSheetID
           LEFT JOIN currencyType on currencyType.currencyTypeID = timeSheet.currencyTypeID
               WHERE timeSheet.projectID = %d
                 AND transaction.status = 'approved'
            GROUP BY transaction.currencyTypeID
              ",$project->get_id());
$db->query($q);
unset($rows);
while ($row = $db->row()) {
  $rows[] = $row;
}
$TPL["total_timeSheet_transactions_approved"] = page::money_print($rows);

$q = prepare("SELECT SUM((amount * pow(10,-currencyType.numberToBasic))) 
                  AS amount, transaction.currencyTypeID as currency
                FROM transaction
           LEFT JOIN invoiceItem on invoiceItem.invoiceItemID = transaction.invoiceItemID
           LEFT JOIN invoice on invoice.invoiceID = invoiceItem.invoiceID
           LEFT JOIN currencyType on currencyType.currencyTypeID = invoice.currencyTypeID
               WHERE invoice.projectID = %d
                 AND transaction.status = 'pending'
            GROUP BY transaction.currencyTypeID
              ",$project->get_id());
$db->query($q);
unset($rows);
while ($row = $db->row()) {
  $rows[] = $row;
}
$TPL["total_invoice_transactions_pending"] = page::money_print($rows);

$q = prepare("SELECT SUM((amount * pow(10,-currencyType.numberToBasic))) 
                  AS amount, transaction.currencyTypeID as currency
                FROM transaction
           LEFT JOIN invoiceItem on invoiceItem.invoiceItemID = transaction.invoiceItemID
           LEFT JOIN invoice on invoice.invoiceID = invoiceItem.invoiceID
           LEFT JOIN currencyType on currencyType.currencyTypeID = invoice.currencyTypeID
               WHERE invoice.projectID = %d
                 AND transaction.status = 'approved'
            GROUP BY transaction.currencyTypeID
              ",$project->get_id());
$db->query($q);
unset($rows);
while ($row = $db->row()) {
  $rows[] = $row;
}
$TPL["total_invoice_transactions_approved"] = page::money_print($rows);


$q = prepare("SELECT SUM((amount * pow(10,-currencyType.numberToBasic))) 
                  AS amount, transaction.currencyTypeID as currency
                FROM transaction
           LEFT JOIN currencyType on currencyType.currencyTypeID = transaction.currencyTypeID
               WHERE transaction.projectID = %d
                 AND transaction.status = 'approved'
            GROUP BY transaction.currencyTypeID
              ",$project->get_id());
$db->query($q);
unset($rows);
while ($row = $db->row()) {
  $rows[] = $row;
}
$TPL["total_expenses_transactions_approved"] = page::money_print($rows);


if ($project->get_id()) {
  $defaults["projectID"] = $project->get_id();
  $defaults["showFinances"] = true;
  if (!$project->have_perm(PERM_READ_WRITE)) {
    $defaults["personID"] = $current_user->get_id();
  }
  $rtn = timeSheet::get_list($defaults);
  $TPL["timeSheetListRows"] = $rtn["rows"];
  $TPL["timeSheetListExtra"] = $rtn["extra"];
}



if ($project->have_perm(PERM_READ_WRITE)) {
  include_template("templates/projectFormM.tpl");
} else {
  include_template("templates/projectViewM.tpl");
}


?>
