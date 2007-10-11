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

  // Add task comment template.
  if ($_POST["taskCommentTemplateID"] && !$_POST["comment"]) {
    $taskCommentTemplate = new taskCommentTemplate;
    $taskCommentTemplate->set_id($_POST["taskCommentTemplateID"]);
    $taskCommentTemplate->select();
    $_POST["comment"] = $taskCommentTemplate->get_value("taskCommentTemplateText");
  }
  

  $comment = new comment;

  if ($_POST["comment_update"]) {
    $comment->set_id($_POST["comment_id"]);
    $comment->select();
  }

  $comment->set_value('commentType', $entity);
  $comment->set_value('commentLinkID', $entityID);

  if ($_POST["comment"]) {
    $comment->set_value('comment', $_POST["comment"]);

    // Email new comment?
    if ($_POST["commentEmailCheckboxes"] || ($_POST["eo_email"] && preg_match("/@/",$_POST["eo_email"]))) {

      // On-the-fly add name and email to recipients
      if ($_POST["eo_email"]) {
        $str = $_POST["eo_name"];
        $str and $str.=" ";
        $str.= str_replace(array("<",">"),"",$_POST["eo_email"]);
        $_POST["commentEmailCheckboxes"][] = $str;
      }

      // Add the dude to the interested parties list
      if ($_POST["eo_email"] && $_POST["eo_add_interested_party"]) {
        $db = new db_alloc();
        $q = sprintf("INSERT INTO taskCCList (fullName,emailAddress,taskID) VALUES ('%s','%s',%d)",db_esc(trim($_POST["eo_name"])),db_esc(trim($_POST["eo_email"])),$entityID);
        $db->query($q);
      }

      // Add a new client contact
      if ($_POST["eo_email"] && $_POST["eo_add_client_contact"] && $_POST["eo_client_id"]) {
        $cc = new clientContact;
        $cc->set_value("clientContactName",trim($_POST["eo_name"]));
        $cc->set_value("clientContactEmail",trim($_POST["eo_email"]));
        $cc->set_value("clientID",sprintf("%d",$_POST["eo_client_id"]));
        $cc->save();
      }

      if (is_object($current_user) && is_object($e) && method_exists($e, "get_all_parties")) {
        $emails = $e->get_all_parties();
        if ($current_user->get_value("emailAddress") && !$emails[$current_user->get_value("emailAddress")]) {
        #die(print_r($emails,1));
          $db = new db_alloc();
          $q = sprintf("INSERT INTO taskCCList (fullName,emailAddress,taskID) VALUES ('%s','%s',%d)",db_esc($current_user->get_username(1)),db_esc($current_user->get_value("emailAddress")),$entityID);
          $db->query($q);
        }
      }

      if (is_object($e) && method_exists($e, "send_emails")) {

        $successful_recipients = $e->send_emails($_POST["commentEmailCheckboxes"], $entity."_comments", $comment->get_value("comment"));
 
        // Append success to end of the comment
        if ($successful_recipients && is_object($comment)) {
          $append_comment_text = "Email sent to: ".$successful_recipients;
          $message_good.= $append_comment_text;
          $comment->set_value("commentEmailRecipients",$successful_recipients);
        }
      }
    }
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
  $message_good and $extra.="&message_good=".urlencode($message_good);
  header("Location: ".$TPL["url_alloc_".$entity].$entity."ID=".$e->get_id().$extra);
}






?>
