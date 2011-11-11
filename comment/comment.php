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

global $TPL, $current_user;


function add_comment($entity,$entityID,$comment_text) {
  if ($entity && $entityID && $comment_text) {
    $comment = new comment;
    $comment->updateSearchIndexLater = true;
    $comment->set_value('commentType', $entity);
    $comment->set_value('commentLinkID', $entityID);
    $comment->set_value('comment', rtrim($comment_text));
    $comment->save();
    return $comment->get_id();
  }
}

function add_interested_parties($commentID,$ip=array(),$op=array()) {

  // We send this email to the default from address, so that a copy of the
  // original email is kept. The receiveEmail.php script will see that this
  // email is *from* the same address, and will then skip over it, when going
  // through the new emails.
  if (defined("ALLOC_DEFAULT_FROM_ADDRESS") && ALLOC_DEFAULT_FROM_ADDRESS) {
    list($from_address,$from_name) = parse_email_address(ALLOC_DEFAULT_FROM_ADDRESS);
    $emailRecipients[] = $from_address;
  }

  interestedParty::make_interested_parties("comment",$commentID,$ip);
  $emailRecipients[] = "interested";

  // Other parties that are added on-the-fly
  foreach ($op as $email => $info) {
    if ($email && in_str("@",$email)) {
      unset($lt,$gt); // used above
      $str = $info["name"];
      $str and $str.=" ";
      $str and $lt = "<";
      $str and $gt = ">";
      $str.= $lt.str_replace(array("<",">"),"",$email).$gt;
      $emailRecipients[] = $str;

      // Add a new client contact
      if ($info["addContact"] && $info["clientID"]) {
        $q = sprintf("SELECT * FROM clientContact WHERE clientID = %d AND clientContactEmail = '%s'"
                    ,$info["clientID"],db_esc(trim($email)));
        $db = new db_alloc();
        if (!$db->qr($q)) {
          $cc = new clientContact;
          $cc->set_value("clientContactName",trim($info["name"]));
          $cc->set_value("clientContactEmail",trim($email));
          $cc->set_value("clientID",sprintf("%d",$info["clientID"]));
          $cc->save();
        }
      }
      // Add the person to the interested parties list
      if ($info["addIP"] && !interestedParty::exists("comment",$commentID,trim($email))) {
        $interestedParty = new interestedParty;
        $interestedParty->set_value("fullName",trim($info["name"]));
        $interestedParty->set_value("emailAddress",trim($email));
        $interestedParty->set_value("entityID",$commentID);
        $interestedParty->set_value("entity","comment");
        $interestedParty->set_value("external","1");
        $interestedParty->set_value("interestedPartyActive","1");
        if (is_object($cc) && $cc->get_id()) {
          $interestedParty->set_value("clientContactID",$cc->get_id());
        }
        $interestedParty->save();
      }
    }
  }
  return $emailRecipients;
}

function send_comment($commentID, $emailRecipients) {

  $comment = new comment();
  $comment->set_id($commentID);
  $comment->select();
  $comment->from["commentID"] = $comment->get_id();
  $comment->from["entity"] = "comment";
  $comment->from["entityID"] = $comment->get_id();

  $token = new token;

  if ($comment->get_value("commentType") == "comment" && $comment->get_value("commentLinkID")) {
    $c = new comment;
    $c->set_id($comment->get_value("commentLinkID"));
    $c->select();
    if ($token->select_token_by_entity_and_action("comment",$c->get_id(),"add_comment_from_email")) {
      $comment->from["hash"] = $token->get_value("tokenHash");
    }
  }

  if (!$comment->from["hash"]) {
    if ($token->select_token_by_entity_and_action("comment",$comment->get_id(),"add_comment_from_email")) {
      $comment->from["hash"] = $token->get_value("tokenHash");
    } else {
      $comment->from["hash"] = $comment->make_token_add_comment_from_email();
    }
  }

  list($successful_recipients,$messageid) = $comment->send_emails($emailRecipients);

  // Append success to end of the comment
  if ($successful_recipients && is_object($comment)) {
    $append_comment_text = "Email sent to: ".$successful_recipients;
    $message_good.= $append_comment_text;
    $comment->set_value("commentEmailMessageID",$messageid);
    $comment->set_value("commentEmailRecipients",$successful_recipients);
  }

  $comment->skip_modified_fields = true;
  $comment->save();
}

function attach_timeSheet($commentID, $entityID, $options) {
  // Begin buffering output to halt anything being sent to the web browser.
  ob_start();
  $t = new timeSheetPrint();
  $ops = query_string_to_array($options);

  $t->get_printable_timeSheet_file($entityID,$ops["timeSheetPrintMode"],$ops["printDesc"],$ops["format"]);

  // Capture the output into $str
  $str = (string)ob_get_clean();

  $suffix = ".html";
  $ops["format"] != "html" and $suffix = ".pdf";

  $timeSheetPrintOptions = config::get_config_item("timeSheetPrintOptions");
  $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$commentID;
  if (!is_dir($dir)) {
    mkdir($dir, 0777);
  }
  $file = $dir.DIRECTORY_SEPARATOR."timeSheet_".$entityID.$suffix;
  file_put_contents($file,$str);
}

function attach_invoice($commentID,$entityID,$verbose) {
  // Begin buffering output to halt anything being sent to the web browser.
  ob_start();
  $invoice = new invoice();
  $invoice->set_id($entityID);
  $invoice->select();
  $invoice->generate_invoice_file($verbose,true);

  // Capture the output into $str
  $str = (string)ob_get_clean();

  $suffix = ".pdf";

  $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$commentID;
  if (!is_dir($dir)) {
    mkdir($dir, 0777);
  }
  $file = $dir.DIRECTORY_SEPARATOR."invoice_".$_REQUEST["entityID"].$suffix;
  file_put_contents($file,$str);
}



// add a comment
$commentID = add_comment($_REQUEST["entity"], $_REQUEST["entityID"], $_REQUEST["comment"]);

// add additional interested parties
if ($_REQUEST["eo_email"]) {
$other_parties[$_REQUEST["eo_email"]] = array("name"       => $_REQUEST["eo_name"]
                                             ,"addIP"      => $_REQUEST["eo_add_interested_party"]
                                             ,"addContact" => $_REQUEST["eo_add_client_contact"]
                                             ,"clientID"   => $_REQUEST["eo_client_id"]);
}

// add all interested parties
$emailRecipients = add_interested_parties($commentID, $_REQUEST["commentEmailRecipients"], $other_parties);

// If someone uploads an attachment
if ($_FILES) {
  move_attachment("comment",$commentID);
}

// Attach any alloc generated timesheet pdf
if ($_REQUEST["attach_timeSheet"]) {
  attach_timeSheet($commentID, $_REQUEST["entityID"], $_REQUEST["attach_timeSheet"]);
}

// Attach any alloc generated invoice pdf
if ($_REQUEST["attach_invoice"]) {
  attach_invoice($commentID,$_REQUEST["entityID"],$_REQUEST["generate_pdf_verbose"]);
}

// Re-email the comment out, including any attachments
send_comment($commentID,$emailRecipients);


// Re-direct browser back home
$TPL["message_good"][] = $message_good;
$extra.= "&sbs_link=comments";
alloc_redirect($TPL["url_alloc_".$_REQUEST["entity"]].$_REQUEST["entity"]."ID=".$_REQUEST["entityID"].$extra);


?>
