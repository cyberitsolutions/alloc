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

require_once("../alloc.php");

  function check_optional_client_exists() {
    global $clientID;
    return $clientID;
  }

  function show_client_details_edit($template) {
    global $TPL, $clientID;

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_save\" value=\"Save Client Contact\">";

    if (!isset($clientID) || $_POST["client_edit"] || $TPL["message"]) {

      // If new client
      if (!$clientID) {
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
    if ($clientID && !$_POST["client_edit"] && !$TPL["message"]) {
      // setup formatted address output

      // postal address
      $TPL["client_clientPostalAddress"] = format_address($client->get_value('clientStreetAddressOne'), $client->get_value('clientSuburbOne'), $client->get_value('clientStateOne'), $client->get_value('clientPostcodeOne'), $client->get_value('clientCountryOne'));
      // street address
      $TPL["client_clientStreetAddress"] = format_address($client->get_value('clientStreetAddressTwo'), $client->get_value('clientSuburbTwo'), $client->get_value('clientStateTwo'), $client->get_value('clientPostcodeTwo'), $client->get_value('clientCountryTwo'));
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
        $a.= " ".$state;
      }
      if ($postcode != "") {
        $a.= " ".$postcode;
      }
      if ($country != "") {
        $a.= "<br>".$country;
      }
    } else {
      $a = "";
    }
    return $a;
  }

  function show_client_contacts() {
    global $TPL, $clientID;

    $TPL["clientContact_clientID"] = $clientID;

    if ($_POST["clientContact_delete"] && $_POST["clientContactID"]) {
      $clientContact = new clientContact;
      $clientContact->set_id($_POST["clientContactID"]);
      $clientContact->delete();
    }

    // get primary contact first
    $client = new client;
    $client->set_id($clientID);
    $client->select();
    $clientPrimaryContactID = $client->get_value("clientPrimaryContactID");

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_save\" value=\"Save Client Contact\">";

    // other contacts
    $query = sprintf("SELECT * 
                        FROM clientContact
                       WHERE clientID=%d    
                    ORDER BY clientContactName", $clientID);

    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $clientContact = new clientContact;
      $clientContact->read_db_record($db);

      if ($_POST["clientContact_edit"] && $_POST["clientContactID"] == $clientContact->get_id()) {
        continue;
      }


      $pc = "";
      if ($clientPrimaryContactID == $clientContact->get_id()) {
        $pc = " (Primary Contact)";
      }

      $col1 = array();
      $clientContact->get_value('clientContactName') and $col1[] = "<b>".$clientContact->get_value('clientContactName')."</b>".$pc;
      $clientContact->get_value('clientContactStreetAddress') and $col1[] = $clientContact->get_value('clientContactStreetAddress');

      $clientContact->get_value('clientContactSuburb') || $clientContact->get_value('clientContactState') || $clientContact->get_value('clientContactPostcode') and
      $col1[] = $clientContact->get_value('clientContactSuburb').' '.$clientContact->get_value('clientContactState')." ".$clientContact->get_value('clientContactPostcode');

      $clientContact->get_value('clientContactCountry') and $col1[] = $clientContact->get_value('clientContactCountry');


      // find some gpl icons!
      #$ico_e = "<img src=\"".$TPL["url_alloc_images"]."/icon_email.gif\">";
      #$ico_p = "<img src=\"".$TPL["url_alloc_images"]."/icon_phone.gif\">";
      #$ico_m = "<img src=\"".$TPL["url_alloc_images"]."/icon_mobile.gif\">";
      #$ico_f = "<img src=\"".$TPL["url_alloc_images"]."/icon_fax.gif\">";

      $ico_e = "E: ";
      $ico_p = "P: ";
      $ico_m = "M: ";
      $ico_f = "F: ";

      $col2 = array();
      $email = $clientContact->get_value("clientContactEmail");
      $email = str_replace("<","",$email);
      $email = str_replace(">","",$email);
      $email and $col2[] = $ico_e."<a href=\"mailto:".$email."\">".$email."</a>";

      $phone = $clientContact->get_value('clientContactPhone');
      $phone and $col2[] = $ico_p.$phone;

      $mobile = $clientContact->get_value('clientContactMobile');
      $mobile and $col2[] = $ico_m.$mobile;

      $fax = $clientContact->get_value('clientContactFax');
      $fax and $col2[] = $ico_f.$fax;

      $buttons = "<nobr><input type=\"submit\" name=\"clientContact_edit\" value=\"Edit\"> 
                        <input type=\"submit\" name=\"clientContact_delete\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete this Client Contact?')\"></nobr>";

      $rtn[] =  '<form action="'.$TPL["url_alloc_client"].'" method="post">';
      $rtn[] =  '<input type="hidden" name="clientContactID" value="'.$clientContact->get_id().'">';
      $rtn[] =  '<input type="hidden" name="clientID" value="'.$clientID.'">';
      $rtn[] =  '<table width="100%" cellspacing="0" border="0" class="comments">';
      $rtn[] =  '<tr>';
      $rtn[] =  '  <td width="25%" valign="top"><span class="nobr">'.implode('</span><br><span class="nobr">',$col1).'</span></td>';
      $rtn[] =  '  <td width="20%" valign="top"><span class="nobr">'.implode('</span><br><span class="nobr">',$col2).'</span></td>';
      $rtn[] =  '  <td rowspan="4" align="left" valign="top">'.nl2br($clientContact->get_value('clientContactOther')).'</td>';
      $rtn[] =  '  <th rowspan="2" align="right" width="2%">'.$buttons.'</th>';
      $rtn[] =  '</tr>';
      $rtn[] =  '</table>';
      $rtn[] =  '</form>';

    }

    if (is_array($rtn)) { 
      $TPL["clientContacts"] = implode("\n",$rtn);
    } 
    if ($_POST["clientContact_edit"] && $_POST["clientContactID"]) {
      $clientContact = new clientContact;
      $clientContact->set_id($_POST["clientContactID"]);
      $clientContact->select();
      $clientContact->set_tpl_values(DST_HTML_ATTRIBUTE, "clientContact_");
      if ($clientPrimaryContactID == $clientContact->get_id()) {
        $TPL["clientPrimaryContactID_checked"] = " checked";
      }
    } else if ($rtn) {
      $TPL["class_new_client_contact"] = "hidden";
    }

    include_template("templates/clientContactM.tpl");
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
  
    if ($TPL["commentsR"] && !$_GET["comment_edit"]) {
      $TPL["class_new_client_comment"] = "hidden";
    }
    include_template("templates/clientCommentM.tpl");
  }

  function show_invoices() {
    global $current_user, $clientID;

    $_FORM["showHeader"] = true;
    $_FORM["showInvoiceNumber"] = true;
    $_FORM["showInvoiceClient"] = true;
    $_FORM["showInvoiceName"] = true;
    $_FORM["showInvoiceAmount"] = true;
    $_FORM["showInvoiceAmountPaid"] = true;
    $_FORM["showInvoiceDate"] = true;
    $_FORM["showInvoiceStatus"] = true;
    $_FORM["clientID"] = $clientID;

    // Restrict non-admin users records  
    if (!$current_user->have_role("admin")) {
      $_FORM["personID"] = $current_user->get_id();  
    }

    echo invoice::get_invoice_list($_FORM);
  }



$client = new client;
$clientID = $_POST["clientID"] or $clientID = $_GET["clientID"];


if ($_POST["save"]) {
  if (!$_POST["clientName"]) {
    $TPL["message"][] = "Please enter a Company Name.";
  }
  $client->read_globals();
  $client->set_value("clientModifiedTime", date("Y-m-d"));
  $clientID = $client->get_id();
  $client->set_tpl_values(DST_HTML_ATTRIBUTE, "client_");

  if (!$client->get_id()) {
    // New client.

    $TPL["createGeneralSupportProject"] = "<tr> <td colspan=\"2\"><b>Don't Create General Support Project</b></td>
        <td><input type=\"checkbox\" name=\"dontCreateProject\" checked=\"yes\"/></td></tr>";
					     
    
    $client->set_value("clientCreatedTime", date("Y-m-d"));
    $new_client = true;
  }

  if (!$TPL["message"]) {
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
  }
  
} else if ($_POST["save_attachment"]) {
  move_attachment("client",$clientID);
  header("Location: ".$TPL["url_alloc_client"]."clientID=".$clientID."&sbs_link=attachments");

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
if ($_POST["clientContact_save"] || $_POST["clientContact_delete"]) {

  $clientContact = new clientContact;
  $clientContact->read_globals();

  if ($_POST["clientContact_save"]) {
    #$clientContact->set_value('clientID', $_POST["clientID"]);
    $clientContact->save();
  }


  if (is_object($client) && $_POST["clientPrimaryContactID"]) {
    #die("<pre>".print_r($clientContact,1)."</pre>");
    $client->set_value('clientPrimaryContactID', $clientContact->get_id());
    $client->save();
  } 

  if ($_POST["clientContact_delete"]) {
    $clientContact->delete();
  }
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


if (!$clientID) {
  $TPL["message_help"][] = "Create a new Client by inputting the Company Name and other details and clicking the Create New Client button.";
  $TPL["main_alloc_title"] = "New Client - ".APPLICATION_NAME;
  $TPL["clientSelfLink"] = "New Client";
} else {
  $TPL["main_alloc_title"] = "Client " . $client->get_id() . ": " . $client->get_client_name()." - ".APPLICATION_NAME;
  $TPL["clientSelfLink"] = sprintf("<a href=\"%s\">%d %s</a>", $client->get_url(), $client->get_id(), $client->get_client_name());
}


if ($current_user->have_role("admin")) {
  $TPL["invoice_links"].= "<a href=\"".$TPL["url_alloc_invoice"]."clientID=".$clientID."\">New Invoice</a>";
}



include_template("templates/clientM.tpl");

page_close();



?>
