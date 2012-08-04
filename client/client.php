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

  function check_optional_client_exists() {
    global $clientID;
    return $clientID;
  }

  function show_client_contacts() {
    global $TPL;
    global $clientID;

    $TPL["clientContact_clientID"] = $clientID;

    if ($_POST["clientContact_delete"] && $_POST["clientContactID"]) {
      $clientContact = new clientContact();
      $clientContact->set_id($_POST["clientContactID"]);
      $clientContact->delete();
    }

    $client = new client();
    $client->set_id($clientID);
    $client->select();

    // other contacts
    $query = prepare("SELECT * 
                        FROM clientContact
                       WHERE clientID=%d    
                    ORDER BY clientContactActive DESC, primaryContact DESC, clientContactName", $clientID);

    $db = new db_alloc();
    $db->query($query);
    while ($db->next_record()) {
      $clientContact = new clientContact();
      $clientContact->read_db_record($db);

      if ($_POST["clientContact_edit"] && $_POST["clientContactID"] == $clientContact->get_id()) {
        continue;
      }


      $pc = "";
      if ($clientContact->get_value("primaryContact")) {
        $pc = " [Primary]";
      }

      $vcard_img = "icon_vcard.png";
      $clientContact->get_value("clientContactActive") or $vcard_img = "icon_vcard_faded.png";

      $vcard = '<a href="'.$TPL["url_alloc_client"].'clientContactID='.$clientContact->get_id().'&get_vcard=1"><img style="vertical-align:middle; padding:3px 6px 3px 3px;border: none" src="'.$TPL["url_alloc_images"].$vcard_img.'" alt="Download VCard" ></a>';

      $col1 = array();
      $clientContact->get_value('clientContactName') and $col1[] = "<h2 style='margin:0px; display:inline;'>".$vcard.$clientContact->get_value('clientContactName',DST_HTML_DISPLAY)."</h2>".$pc;
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

      $userName = $clientContact->get_value('clientContactName', DST_HTML_DISPLAY);
      if ($userName) {
          $mailto = '"' . $userName . '" <' . $email . ">";
      } else {
          $mailto = $email;
      }
      $email and $col2[] = $ico_e."<a href='mailto:".rawurlencode($mailto)."'>".$email."</a>";

      $phone = $clientContact->get_value('clientContactPhone',DST_HTML_DISPLAY);
      $phone and $col2[] = $ico_p.$phone;

      $mobile = $clientContact->get_value('clientContactMobile',DST_HTML_DISPLAY);
      $mobile and $col2[] = $ico_m.$mobile;

      $fax = $clientContact->get_value('clientContactFax',DST_HTML_DISPLAY);
      $fax and $col2[] = $ico_f.$fax;

      if ($clientContact->get_value("clientContactActive")) {
        $class_extra = " loud";
      } else {
        $class_extra = " quiet";
      }

      $buttons = '<nobr>
      <button type="submit" name="clientContact_delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
      <button type="submit" name="clientContact_edit" value="1"">Edit<i class="icon-edit"></i></button>
      </nobr>';

      $rtn[] =  '<form action="'.$TPL["url_alloc_client"].'" method="post">';
      $rtn[] =  '<input type="hidden" name="clientContactID" value="'.$clientContact->get_id().'">';
      $rtn[] =  '<input type="hidden" name="clientID" value="'.$clientID.'">';
      $rtn[] =  '<div class="panel'.$class_extra.' corner">';
      $rtn[] =  '<table width="100%" cellspacing="0" border="0">';
      $rtn[] =  '<tr>';
      $rtn[] =  '  <td width="25%" valign="top"><span class="nobr">'.implode('</span><br><span class="nobr">',$col1).'</span>&nbsp;</td>';
      $rtn[] =  '  <td width="20%" valign="top"><span class="nobr">'.implode('</span><br><span class="nobr">',$col2).'</span>&nbsp;</td>';
      $rtn[] =  '  <td width="50%" align="left" valign="top">'.nl2br($clientContact->get_value('clientContactOther',DST_HTML_DISPLAY)).'&nbsp;</td>';
      $rtn[] =  '  <td align="right" class="right nobr">'.$buttons.'</td>';
      $rtn[] =  '  <td align="right" class="right nobr" width="1%">'.page::star("clientContact",$clientContact->get_id()).'</td>';
      $rtn[] =  '</tr>';
      $rtn[] =  '</table>';
      $rtn[] =  '</div>';
      $rtn[] =  '<input type="hidden" name="sessID" value="'.$TPL["sessID"].'">';
      $rtn[] =  '</form>';

    }

    if (is_array($rtn)) { 
      $TPL["clientContacts"] = implode("\n",$rtn);
    } 
    if ($_POST["clientContact_edit"] && $_POST["clientContactID"]) {
      $clientContact = new clientContact();
      $clientContact->set_id($_POST["clientContactID"]);
      $clientContact->select();
      $clientContact->set_values("clientContact_");
      if ($clientContact->get_value("primaryContact")) {
        $TPL["primaryContact_checked"] = " checked";
      }
      if ($clientContact->get_value("clientContactActive")) {
        $TPL["clientContactActive_checked"] = " checked";
      }
    } else if ($rtn) {
      $TPL["class_new_client_contact"] = "hidden";
    }

    if (!$_POST["clientContactID"] || $_POST["clientContact_save"]) {
      $TPL["clientContactActive_checked"] = " checked";
    }

    include_template("templates/clientContactM.tpl");
  }

  function show_attachments() {
    global $clientID;
    util_show_attachments("client",$clientID);
  }
 
  function show_comments() {
    global $clientID;
    global $TPL;
    global $client;
    $TPL["commentsR"] = comment::util_get_comments("client",$clientID);
    $TPL["commentsR"] and $TPL["class_new_comment"] = "hidden";
    $interestedPartyOptions = $client->get_all_parties();
    $interestedPartyOptions = interestedParty::get_interested_parties("client",$client->get_id()
                                                                     ,$interestedPartyOptions);
    $TPL["allParties"] = $interestedPartyOptions or $TPL["allParties"] = array();
    $TPL["entity"] = "client";
    $TPL["entityID"] = $client->get_id();
    $TPL["clientID"] = $client->get_id();

    $commentTemplate = new commentTemplate();
    $ops = $commentTemplate->get_assoc_array("commentTemplateID","commentTemplateName","",array("commentTemplateType"=>"client"));
    $TPL["commentTemplateOptions"] = "<option value=\"\">Comment Templates</option>".page::select_options($ops);
    include_template("../comment/templates/commentM.tpl");
  }

  function show_invoices() {
    $current_user = &singleton("current_user");
    global $clientID;

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

    $rows = invoice::get_list($_FORM);
    echo invoice::get_list_html($rows,$_FORM);
  }



$client = new client();
$clientID = $_POST["clientID"] or $clientID = $_GET["clientID"];


if ($_POST["save"]) {
  if (!$_POST["clientName"]) {
    alloc_error("Please enter a Client Name.");
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

} else if ($_GET["get_vcard"]) {
  $clientContact = new clientContact();
  $clientContact->set_id($_GET["clientContactID"]);
  $clientContact->select();
  $clientContact->output_vcard();
  return;
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

$m = new meta("clientStatus");
$clientStatus_array = $m->get_assoc_array("clientStatusID","clientStatusID");
$TPL["clientStatusOptions"] = page::select_options($clientStatus_array, $client->get_value("clientStatus"));

$clientCategories = config::get_config_item("clientCategories") or $clientCategories = array();
foreach ($clientCategories as $k => $v) {
  $cc[$v["value"]] = $v["label"];
}
$TPL["clientCategoryOptions"] = page::select_options($cc,$client->get_value("clientCategory"));
$client->get_value("clientCategory") and $TPL["client_clientCategoryLabel"] = $cc[$client->get_value("clientCategory")];


// client contacts
if ($_POST["clientContact_save"] || $_POST["clientContact_delete"]) {

  $clientContact = new clientContact();
  $clientContact->read_globals();

  if ($_POST["clientContact_save"]) {
    #$clientContact->set_value('clientID', $_POST["clientID"]);
    $clientContact->save();
  }

  if ($_POST["clientContact_delete"]) {
    $clientContact->delete();
  }
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

$projectListOps = array("showProjectType"=>true
                       ,"clientID"=>$client->get_id()
                       );

$TPL["projectListRows"] = project::get_list($projectListOps);

$TPL["client_clientPostalAddress"] = $client->format_address("postal");
$TPL["client_clientStreetAddress"] = $client->format_address("street");

include_template("templates/clientM.tpl");




?>
