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

  function check_optional_client_exists() {
    global $clientID;
    return isset($clientID);
  }

  function show_client_details_edit($template) {
    global $TPL, $clientID;

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_save\" value=\"Save\">"."<input type=\"submit\" name=\"clientContact_delete\" value=\"Delete\">";

    if (!isset($clientID) || $_POST["client_edit"]) {

      // If new client
      if (!isset($clientID)) {
        $TPL["clientDetails_buttons"] = "<input type=\"submit\" name=\"save\" value=\"Create New Client\">";
        $TPL["createGeneralSupportProject"] = "<b>Create General Support Project</b> <input type=\"checkbox\" name=\"createProject\"/>";
          
      // Else just editing
      } else {
        $TPL["clientDetails_buttons"] =
          "<input type=\"submit\" name=\"save\" value=\"&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;\">".
          "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".
          "<input type=\"submit\" name=\"cancel\" value=\"&nbsp;&nbsp;&nbsp;Cancel&nbsp;&nbsp;&nbsp;\">"."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"."<input type=\"submit\" name=\"delete\" value=\"Delete Record\" onClick=\"return confirm('Are you sure you want to delete this record?')\">";
        // new support project check box should NOT be visible.
        $TPL["createGeneralSupportProject"] = "";
      
      }
      include_template($template);
    }
  }

  function show_client_details($template) {
    global $TPL, $client, $clientID;
    if (isset($clientID) && !$_POST["client_edit"]) {
      // setup formatted address output

      // postal address
      $TPL["client_clientPostalAddress"] = $client->get_value('clientName')."<br>".format_address($client->get_value('clientStreetAddressOne'), $client->get_value('clientSuburbOne'), $client->get_value('clientStateOne'), $client->get_value('clientPostcodeOne'), $client->get_value('clientCountryOne'));
      // street address
      $TPL["client_clientStreetAddress"] = $client->get_value('clientName')."<br>".format_address($client->get_value('clientStreetAddressTwo'), $client->get_value('clientSuburbTwo'), $client->get_value('clientStateTwo'), $client->get_value('clientPostcodeTwo'), $client->get_value('clientCountryTwo'));
      include_template($template);
    }
  }

  function format_address($address, $suburb, $state, $postcode, $country) {
    if ($address != "") {
      $a = $address;
      if ($suburb != "") {
        $a.= "<br>".$suburb;
      }
      if ($state != "") {
        $a.= "<br>".$state;
        if ($postcode != "") {
          $a.= ", ".$postcode;
        }
      }
      if ($country != "") {
        $a.= "<br>".$country;
      }
    } else {
      $a = "(no address)";
    }
    return $a;
  }

  function show_client_contacts($template) {
    global $TPL, $clientID;

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_save\" value=\"Save\">"."<input type=\"submit\" name=\"clientContact_delete\" value=\"Delete\">";

    // get primary contact first
    $client = new client;
    $client->set_id($clientID);
    $client->select();
    if ($client->get_id('clientPrimaryContactID') != "NULL") {
      $clientContact = new clientContact;
      $clientContact->set_id($client->get_value('clientPrimaryContactID'));
      $clientContact->select();
      if ($clientContact->get_value('clientContactName') != "") {
        $clientContact->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
        $TPL["client_clientTitle"] = "Primary: ".$clientContact->get_value('clientContactName');
        if ($TPL["client_clientContactEmail"]) {
          $TPL["client_clientTitle"] .= "&nbsp;&nbsp;<a href=\"mailto:".$TPL["client_clientContactEmail"]."\">".$TPL["client_clientContactEmail"]."</a>";
        }
        $TPL["client_clientContactID"] = $clientContact->get_id();

        include_template($template);
      }
    }

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_save\" value=\"Save\">"."<input type=\"submit\" name=\"clientContact_makepc\" value=\"Make Primary Contact\">"."<input type=\"submit\" name=\"clientContact_delete\" value=\"Delete\">";

    // other contacts
    $query = "SELECT * FROM clientContact";
    $query.= sprintf(" WHERE clientID=%d AND clientContactID!=%d", $clientID, $client->get_value('clientPrimaryContactID'));
    $query.= " ORDER BY clientContactName";
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $clientContact = new clientContact;
      $clientContact->read_db_record($db);
      $clientContact->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
      $TPL["client_clientTitle"] = $clientContact->get_value('clientContactName');
      if ($TPL["client_clientContactEmail"]) {
        $TPL["client_clientTitle"] .= "&nbsp;&nbsp;<a href=\"mailto:".$TPL["client_clientContactEmail"]."\">".$TPL["client_clientContactEmail"]."</a>";
      }
      $TPL["client_clientContactID"] = $clientContact->get_id();

      include_template($template);
    }

    // place blank client contact form at the end with add button for adding new contacts

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_addpc\" value=\"Add as Primary Contact\">"."<input type=\"submit\" name=\"clientContact_add\" value=\"Add\">";

    $clientContact = new clientContact;
    $clientContact->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
    $TPL["client_clientID"] = $clientID;
    $TPL["client_clientTitle"] = "Add New Contact";
    include_template($template);
  }

  function show_reminders($template) {
    global $TPL, $clientID, $reminderID, $current_user;

    // show all reminders for this project
    $reminder = new reminder;
    $db = new db_alloc;
    if ($current_user->have_role("manage") || $current_user->have_role("admin") || $current_user->have_role("god")) {
      $query = sprintf("SELECT * FROM reminder WHERE reminderType='client' AND reminderLinkID=%d", $clientID);
    } else {
      $query = sprintf("SELECT * FROM reminder WHERE reminderType='client' AND reminderLinkID=%d AND personID='%s'", $clientID, $current_user->get_id());
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
      $TPL["returnToParent"] = "client";

      include_template($template);
    }
  }

  function show_projects() {

    global $clientID;

    if (!isset($clientID)) {
      return;
    }

    $db_projects = new db_alloc;
    $query = sprintf("SELECT * from project where clientID=%d", $clientID);
    $db_projects->query($query);

    while ($db_projects->next_record()) {

      $project = new project;
      $project->read_db_record($db_projects);
      $project->set_tpl_values(DST_HTML_ATTRIBUTE, "project_");

      include_template("templates/projectListR.tpl");

    }
  }

  function show_attachments() {
    global $clientID;
    util_show_attachments("client",$clientID);
  }
 
  function show_comments() {
    global $clientID, $TPL;
    $options["showEditButtons"] = true;
    $TPL["commentsR"] = util_get_comments("client",$clientID,$options);
    include_template("templates/clientCommentM.tpl");
  }



$client = new client;
$clientID = $_POST["clientID"] or $clientID = $_GET["clientID"];


if ($_POST["save"]) {
  if ($_POST["clientName"] == "") {
    $TPL["error"] = "Please enter a company name.";
  }
  $client->read_globals();
  $client->set_value("clientModifiedTime", date("Y-m-d"));
  if (!$client->get_id()) {
    // New client.

    $TPL["createGeneralSupportProject"] = "<tr> <td colspan=\"2\"><b>Don't Create General Support Project</b></td>
        <td><input type=\"checkbox\" name=\"dontCreateProject\" checked=\"yes\"/></td></tr>";
					     
    
    $client->set_value("clientCreatedTime", date("Y-m-d"));
    $new_client = true;
  }

  $client->save();
  $clientID = $client->get_id();
  $client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
  
  if ($new_client == true && $_POST["createProject"]) {
     // Create Project: <Client Name> - General Support 
    $project = new project;
    $project->set_value("projectName", $client->get_value("clientName")." - General Support");
    $project->set_value("clientID", $clientID);
    $project->set_value("projectType", "contract");
    $project->set_value("projectStatus", "current");
    $project->save();

    // Now add current_user as a projectPerson
    $projectperson = new projectperson;
    $projectperson->set_value("personID", $current_user->get_id());
    $projectperson->set_value("projectID", $project->get_id());
    $projectperson->set_value_role("isManager");
    $projectperson->save();
  }    
  
} else if ($_POST["save_attachment"]) {
  move_attachment("client",$clientID);
  header("Location: ".$TPL["url_alloc_client"]."clientID=".$clientID);

} else {

  if ($_POST["delete"]) {
    $client->read_globals();
    // delete all contacts and comments linked with this client as well
    $db = new db_alloc;
    $query = sprintf("SELECT * FROM clientContact WHERE clientID=%d", $client->get_id());
    $db->query($query);
    while ($db->next_record()) {
      $clientContact = new clientContact;
      $clientContact->read_db_record($db);
      $clientContact->delete();
    }
    $query = sprintf("SELECT * FROM comment WHERE commentLinkID=%d", $client->get_id());
    $db->query($query);
    while ($db->next_record()) {
      $comment = new comment;
      $comment->read_db_record($db);
      $comment->delete();
    }
    $client->delete();
    header("location: ".$TPL["url_alloc_clientList"]);
  } else {
    $client->set_id($clientID);
    $client->select();
  }

  $client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");
}

$clientStatus_array = array("current"=>"Current", "potential"=>"Potential", "archived"=>"Archived");
$TPL["clientStatusOptions"] = get_select_options($clientStatus_array, $client->get_value("clientStatus"));

  // client contacts
if ($_POST["clientContact_save"] || $_POST["clientContact_add"] || $_POST["clientContact_addpc"]
    || $_POST["clientContact_makepc"] || $_POST["clientContact_delete"]) {
  $clientContact = new clientContact;
  $clientContact->read_globals();
  if ($_POST["clientContact_add"] || $_POST["clientContact_addpc"]) {
    $clientContact->set_value('clientID', $clientID);


    if ($clientContact->get_value('clientContactEmail') && preg_match("/<(.*)>/",$clientContact->get_value('clientContactEmail'),$matches)) {
      $clientContact->set_value('clientContactEmail',$matches[1]);
    }

    $clientContact->save();
    if ($_POST["clientContact_addpc"]) {
      $client->set_value('clientPrimaryContactID', $clientContact->get_id());
      $client->save();
    }
  }
  if ($_POST["clientContact_save"]) {
    $clientContact->save();
  }
  if ($_POST["clientContact_delete"]) {
    $clientContact->delete();
  }
  if ($_POST["clientContact_makepc"]) {
    $client->set_value('clientPrimaryContactID', $clientContact->get_id());
    $client->save();
  }
}





// Comments
if ($_POST["commentID"] && $_POST["comment_edit"]) {
  $comment = new comment();
  $comment->set_id($_POST["commentID"]);
  $comment->select();
  $TPL["comment"] = $comment->get_value('comment');
  $TPL["comment_buttons"] =
    sprintf("<input type=\"hidden\" name=\"comment_id\" value=\"%d\">", $_POST["commentID"])
           ."<input type=\"submit\" name=\"comment_update\" value=\"Save Comment\">";
} else {
  $TPL["comment_buttons"] = "<input type=\"submit\" name=\"comment_save\" value=\"Save Comment\">";
}


if (!$clientID) {
  $TPL["message_help"][] = "Create a new Client by inputting the Company Name and other details and clicking the Create New Client button.";
}


include_template("templates/clientM.tpl");

page_close();



?>
