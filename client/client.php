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
          
      // Else just editing
      } else {
        $TPL["clientDetails_buttons"] =
          "<input type=\"submit\" name=\"save\" value=\"&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;\">
           <input type=\"submit\" name=\"delete\" value=\"Delete\" class=\"delete_button\">
           <input type=\"submit\" name=\"cancel\" value=\"Cancel Edit\">";
      }
      include_template($template);
    }
  }

  function show_client_details($template) {
    global $TPL, $client, $clientID;
    if ($clientID && !$_POST["client_edit"] && !$TPL["message"]) {
      // setup formatted address output
      $TPL["client_clientPostalAddress"] = $client->format_address("postal");
      $TPL["client_clientStreetAddress"] = $client->format_address("street");
      include_template($template);
    }
  }

  function show_client_contacts() {
    global $TPL, $clientID;

    $TPL["clientContact_clientID"] = $clientID;

    if ($_POST["clientContact_delete"] && $_POST["clientContactID"]) {
      $clientContact = new clientContact;
      $clientContact->set_id($_POST["clientContactID"]);
      $clientContact->delete();
    }

    $client = new client;
    $client->set_id($clientID);
    $client->select();

    $TPL["clientContactItem_buttons"] = "<input type=\"submit\" name=\"clientContact_save\" value=\"Save Client Contact\">";

    // other contacts
    $query = sprintf("SELECT * 
                        FROM clientContact
                       WHERE clientID=%d    
                    ORDER BY primaryContact desc, clientContactName", $clientID);

    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $clientContact = new clientContact;
      $clientContact->read_db_record($db);

      if ($_POST["clientContact_edit"] && $_POST["clientContactID"] == $clientContact->get_id()) {
        continue;
      }


      $pc = "";
      if ($clientContact->get_value("primaryContact")) {
        $pc = " (Primary Contact)";
      }

      $col1 = array();
      $clientContact->get_value('clientContactName') and $col1[] = "<b>".$clientContact->get_value('clientContactName',DST_HTML_DISPLAY)."</b>".$pc;
      $clientContact->get_value('clientContactStreetAddress') and $col1[] = $clientContact->get_value('clientContactStreetAddress',DST_HTML_DISPLAY);

      $clientContact->get_value('clientContactSuburb') || $clientContact->get_value('clientContactState') || $clientContact->get_value('clientContactPostcode') and
      $col1[] = $clientContact->get_value('clientContactSuburb',DST_HTML_DISPLAY).' '.$clientContact->get_value('clientContactState',DST_HTML_DISPLAY)." ".$clientContact->get_value('clientContactPostcode',DST_HTML_DISPLAY);

      $clientContact->get_value('clientContactCountry') and $col1[] = $clientContact->get_value('clientContactCountry',DST_HTML_DISPLAY);


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
      $email = $clientContact->get_value("clientContactEmail",DST_HTML_DISPLAY);
      $email = str_replace("<","",$email);
      $email = str_replace(">","",$email);
      $email = str_replace("&lt;","",$email);
      $email = str_replace("&gt;","",$email);
      $email and $col2[] = $ico_e."<a href=\"mailto:".$email."\">".$email."</a>";

      $phone = $clientContact->get_value('clientContactPhone',DST_HTML_DISPLAY);
      $phone and $col2[] = $ico_p.$phone;

      $mobile = $clientContact->get_value('clientContactMobile',DST_HTML_DISPLAY);
      $mobile and $col2[] = $ico_m.$mobile;

      $fax = $clientContact->get_value('clientContactFax',DST_HTML_DISPLAY);
      $fax and $col2[] = $ico_f.$fax;

      $buttons = "<nobr><input type=\"submit\" name=\"clientContact_edit\" value=\"Edit\"> 
                        <input type=\"submit\" name=\"clientContact_delete\" value=\"Delete\" class=\"delete_button\"></nobr>";

      $rtn[] =  '<form action="'.$TPL["url_alloc_client"].'" method="post">';
      $rtn[] =  '<input type="hidden" name="clientContactID" value="'.$clientContact->get_id().'">';
      $rtn[] =  '<input type="hidden" name="clientID" value="'.$clientID.'">';
      $rtn[] =  '<table width="100%" cellspacing="0" border="0" class="panel">';
      $rtn[] =  '<tr>';
      $rtn[] =  '  <td width="25%" valign="top"><span class="nobr">'.implode('</span><br><span class="nobr">',$col1).'</span></td>';
      $rtn[] =  '  <td width="20%" valign="top"><span class="nobr">'.implode('</span><br><span class="nobr">',$col2).'</span></td>';
      $rtn[] =  '  <td rowspan="4" align="left" valign="top">'.nl2br($clientContact->get_value('clientContactOther',DST_HTML_DISPLAY)).'</td>';
      $rtn[] =  '  <th rowspan="2" align="right" style="float:right">'.$buttons.'</th>';
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
      $clientContact->set_values("clientContact_");
      if ($clientContact->get_value("primaryContact")) {
        $TPL["primaryContact_checked"] = " checked";
      }
    } else if ($rtn) {
      $TPL["class_new_client_contact"] = "hidden";
    }

    include_template("templates/clientContactM.tpl");
  }

  function show_reminders($template) {
    global $TPL, $clientID, $reminderID, $current_user;

    // show all reminders for this project
    $db = new db_alloc;
    if ($current_user->have_role("manage") || $current_user->have_role("admin") || $current_user->have_role("god")) {
      $query = sprintf("SELECT * FROM reminder WHERE reminderType='client' AND reminderLinkID=%d", $clientID);
    } else {
      $query = sprintf("SELECT * FROM reminder WHERE reminderType='client' AND reminderLinkID=%d AND personID='%d'", $clientID, $current_user->get_id());
    }
    $db->query($query);
    while ($db->next_record()) {
      $reminder = new reminder;
      $reminder->read_db_record($db);
      $reminder->set_tpl_values("reminder_");
      if ($reminder->get_value('reminderRecuringInterval') == "No") {
        $TPL["reminder_reminderRecurence"] = "&nbsp;";
      } else {
        $TPL["reminder_reminderRecurence"] = "Every ".$reminder->get_value('reminderRecuringValue')
          ." ".$reminder->get_value('reminderRecuringInterval')."(s)";
      }
      $TPL["reminder_reminderRecipient"] = $reminder->get_recipient_description();
      $TPL["returnToParent"] = "client";

      include_template($template);
    }
  }

  function show_attachments() {
    global $clientID;
    util_show_attachments("client",$clientID);
  }
 
  function show_comments() {
    global $clientID, $TPL;
    $options["showEditButtons"] = true;
    $TPL["commentsR"] = comment::util_get_comments("client",$clientID,$options);
  
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

    echo invoice::get_list($_FORM);
  }



$client = new client;
$clientID = $_POST["clientID"] or $clientID = $_GET["clientID"];


if ($_POST["save"]) {
  if (!$_POST["clientName"]) {
    $TPL["message"][] = "Please enter a Client Name.";
  }
  $client->read_globals();
  $client->set_value("clientModifiedTime", date("Y-m-d"));
  $clientID = $client->get_id();
  $client->set_values("client_");

  if (!$client->get_id()) {
    // New client.
    $client->set_value("clientCreatedTime", date("Y-m-d"));
    $new_client = true;
  }

  if (!$TPL["message"]) {
    $client->save();
    $clientID = $client->get_id();
    $client->set_values("client_");
  }
  
} else if ($_POST["save_attachment"]) {
  move_attachment("client",$clientID);
  alloc_redirect($TPL["url_alloc_client"]."clientID=".$clientID."&sbs_link=attachments");

} else {

  if ($_POST["delete"]) {
    $client->read_globals();
    $client->delete();
    alloc_redirect($TPL["url_alloc_clientList"]);
  } else {
    $client->set_id($clientID);
    $client->select();
  }

  $client->set_values("client_");
}

$clientStatus_array = array("current"=>"Current", "potential"=>"Potential", "archived"=>"Archived");
$TPL["clientStatusOptions"] = page::select_options($clientStatus_array, $client->get_value("clientStatus"));

$clientCategories = config::get_config_item("clientCategories") or $clientCategories = array();
foreach ($clientCategories as $k => $v) {
  $cc[$v["value"]] = $v["label"];
}
$TPL["clientCategoryOptions"] = page::select_options($cc,$client->get_value("clientCategory"));
$client->get_value("clientCategory") and $TPL["client_clientCategoryLabel"] = $cc[$client->get_value("clientCategory")];


// client contacts
if ($_POST["clientContact_save"] || $_POST["clientContact_delete"]) {

  $clientContact = new clientContact;
  $clientContact->read_globals();

  if ($_POST["clientContact_save"]) {
    #$clientContact->set_value('clientID', $_POST["clientID"]);
    $clientContact->save();
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
  $TPL["message_help"][] = "Create a new Client by inputting the Client Name and other details and clicking the Create New Client button.";
  $TPL["main_alloc_title"] = "New Client - ".APPLICATION_NAME;
  $TPL["clientSelfLink"] = "New Client";
} else {
  $TPL["main_alloc_title"] = "Client " . $client->get_id() . ": " . $client->get_name()." - ".APPLICATION_NAME;
  $TPL["clientSelfLink"] = sprintf("<a href=\"%s\">%d %s</a>", $client->get_url(), $client->get_id(), $client->get_name(array("return"=>"html")));
}


if ($current_user->have_role("admin")) {
  $TPL["invoice_links"].= "<a href=\"".$TPL["url_alloc_invoice"]."clientID=".$clientID."\">New Invoice</a>";
}



include_template("templates/clientM.tpl");




?>
