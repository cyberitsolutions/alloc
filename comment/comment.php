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
$entity = $_POST["entity"] or $entity = $_GET["entity"];
$entityID = $_POST["entityID"] or $entityID = $_GET["entityID"];



if ($entity && $entityID) {
  $e = new $entity;
  $e->set_id($entityID);
  $e->select(); 
}

// comments
if ($_POST["comment_save"] || $_POST["comment_update"]) {

  // Add comment template.
  if ($_POST["commentTemplateID"] && !$_POST["comment"]) {
    $commentTemplate = new commentTemplate;
    $commentTemplate->set_id($_POST["commentTemplateID"]);
    $commentTemplate->select();
    $_POST["comment"] = $commentTemplate->get_value("commentTemplateText");
  }
  

  $comment = new comment;
  $comment->updateSearchIndexLater = true;

  if ($_POST["comment_update"]) {
    $comment->set_id($_POST["comment_id"]);
    $comment->select();
  }

  $comment->set_value('commentType', $entity);
  $comment->set_value('commentLinkID', $entityID);

  if ($_POST["comment"]) {
    $comment->set_value('comment', rtrim($_POST["comment"]));
    $comment->save();


    // Add relevant people to the comments interestedParties list
    interestedParty::make_interested_parties("comment",$comment->get_id(),$_POST["commentEmailRecipients"]);
    $emailRecipients[] = "interested";

    // On-the-fly add name and email to recipients
    if ($_POST["eo_email"] && preg_match("/@/",$_POST["eo_email"]) && $_POST["eo_email"]) {
      unset($lt,$gt); // used above
      $str = $_POST["eo_name"];
      $str and $str.=" ";
      $str and $lt = "<";
      $str and $gt = ">";
      $str.= $lt.str_replace(array("<",">"),"",$_POST["eo_email"]).$gt;
      $emailRecipients[] = $str;

      // Add a new client contact
      if ($_POST["eo_client_id"]) {
        $q = sprintf("SELECT * FROM clientContact WHERE clientID = %d AND clientContactEmail = '%s'"
                    ,$_POST["eo_client_id"],db_esc(trim($_POST["eo_email"])));
        $db = new db_alloc();
        if (!$db->qr($q)) {
          $cc = new clientContact;
          $cc->set_value("clientContactName",trim($_POST["eo_name"]));
          $cc->set_value("clientContactEmail",trim($_POST["eo_email"]));
          $cc->set_value("clientID",sprintf("%d",$_POST["eo_client_id"]));
          $cc->save();
        }
      }
      // Add the person to the interested parties list
      if (!interestedParty::exists("comment",$comment->get_id(),trim($_POST["eo_email"]))) {
        $interestedParty = new interestedParty;
        $interestedParty->set_value("fullName",trim($_POST["eo_name"]));
        $interestedParty->set_value("emailAddress",trim($_POST["eo_email"]));
        $interestedParty->set_value("entityID",$comment->get_id());
        $interestedParty->set_value("entity","comment");
        $interestedParty->set_value("external","1");
        if (is_object($cc) && $cc->get_id()) {
          $interestedParty->set_value("clientContactID",$cc->get_id());
        }
        $interestedParty->save();
      }
    }

    // We send this email to the default from address, so that a copy of the
    // original email is kept. The receiveEmail.php script will see that this
    // email is *from* the same address, and will then skip over it, when going
    // through the new emails.
    if (defined("ALLOC_DEFAULT_FROM_ADDRESS") && ALLOC_DEFAULT_FROM_ADDRESS) {
      list($from_address,$from_name) = parse_email_address(ALLOC_DEFAULT_FROM_ADDRESS);
      $emailRecipients[] = $from_address;
    }
  
    // if someone uploads an attachment
    if ($_FILES) {
      move_attachment("comment",$comment->get_id());
    }

    // if we're attaching an alloc generated file (i.e. a timesheet pdf)
    if ($_POST["attach_timeSheet"]) {

      // Begin buffering output to halt anything being sent to the web browser.
      ob_start();
      $t = new timeSheetPrint();
      $ops = query_string_to_array($_POST["attach_timeSheet"]);
      $t->get_printable_timeSheet_file($entityID,$ops["timeSheetPrintMode"],$ops["printDesc"],$ops["format"]);

      // Capture the output into $str
      $str = (string)ob_get_clean();

      $suffix = ".html";
      $ops["format"] != "html" and $suffix = ".pdf";

      $timeSheetPrintOptions = config::get_config_item("timeSheetPrintOptions");
      $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$comment->get_id();
      if (!is_dir($dir)) {
        mkdir($dir, 0777);
      }
      $file = $dir.DIRECTORY_SEPARATOR."timeSheet_".$entityID.$suffix;
      file_put_contents($file,$str);


    } else if ($_POST["attach_invoice"]) {
      // Begin buffering output to halt anything being sent to the web browser.
      ob_start();
      $invoice = new invoice();
      $invoice->set_id($entityID);
      $invoice->select();
      $invoice->generate_invoice_file($_REQUEST["generate_pdf_verbose"],true);

      // Capture the output into $str
      $str = (string)ob_get_clean();

      $suffix = ".pdf";

      $dir = ATTACHMENTS_DIR."comment".DIRECTORY_SEPARATOR.$comment->get_id();
      if (!is_dir($dir)) {
        mkdir($dir, 0777);
      }
      $file = $dir.DIRECTORY_SEPARATOR."invoice_".$entityID.$suffix;
      file_put_contents($file,$str);
    }


    if ($emailRecipients) {
      if (is_object($e)) {
        $from["commentID"] = $comment->get_id();
        $from["parentCommentID"] = $comment->get_id();
        $from["entity"] = "comment";
        $from["entityID"] = $comment->get_id();

        $token = new token;

        if ($comment->get_value("commentType") == "comment" && $comment->get_value("commentLinkID")) {
          $c = new comment;
          $c->set_id($comment->get_value("commentLinkID"));
          $c->select();
          if ($token->select_token_by_entity_and_action("comment",$c->get_id(),"add_comment_from_email")) {
            $from["hash"] = $token->get_value("tokenHash");
          }
        }
  
        if (!$from["hash"]) {
          if ($token->select_token_by_entity_and_action("comment",$comment->get_id(),"add_comment_from_email")) {
            $from["hash"] = $token->get_value("tokenHash");
          } else {
            $from["hash"] = $comment->make_token_add_comment_from_email();
          }
        }

        list($successful_recipients,$messageid) = comment::send_emails($e, $emailRecipients, $entity."_comments", $comment->get_value("comment"), $from);
 
        // Append success to end of the comment
        if ($successful_recipients && is_object($comment)) {
          $append_comment_text = "Email sent to: ".$successful_recipients;
          $message_good.= $append_comment_text;
          $comment->set_value("commentEmailMessageID",$messageid);
          $comment->set_value("commentEmailRecipients",$successful_recipients);
        }
      }
    }
    $comment->skip_modified_fields = true;
    $comment->save();
  }
} else if ($_POST["comment_delete"] && $_POST["comment_id"]) {
  $comment = new comment;
  $comment->set_id($_POST["comment_id"]);
  $comment->select();
  $comment->delete();
}

if (is_object($e) && $e->get_id()) {
  $_POST["comment_edit"] and $extra = "&comment_edit=true&commentID=".$_POST["comment_id"];
  $TPL["message_good"][] = $message_good;
  $extra.= "&sbs_link=comments";
  alloc_redirect($TPL["url_alloc_".$entity].$entity."ID=".$e->get_id().$extra);
}






?>
