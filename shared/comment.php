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

global $TPL;
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
  $comment->set_value('commentType', $entity);
  $comment->set_value('commentLinkID', $entityID);
  $comment->set_modified_time();
  $comment->set_value('commentModifiedUser', $current_user->get_id());

  if ($_POST["comment_update"]) {
    $comment->set_id($_POST["comment_id"]);
  }

  if ($_POST["comment"]) {
    $comment->set_value('comment', $_POST["comment"]);
    $comment->save();

    // Email new comment?
    if ($_POST["commentEmailCheckboxes"]) {

      if (is_object($e) && method_exists($e, "send_emails")) {

        $successful_recipients = $e->send_emails($_POST["commentEmailCheckboxes"], $entity."_comments", $comment->get_value("comment"));
 
        // Append success to end of the comment
        if ($successful_recipients && is_object($comment)) {
          $append_comment_text = "Emailed: ".$successful_recipients." at ".date("Y-m-d H:i:s")."\n".$comment->get_value("comment");
          $message_good.= $append_comment_text;
          $comment->set_value("comment",$append_comment_text);
          $comment->save();
        }
      }
    }
  }
} else if ($_POST["comment_delete"] && $_POST["comment_id"]) {
  $comment = new comment;
  $comment->set_id($_POST["comment_id"]);
  $comment->delete();
}

if (is_object($e) && $e->get_id()) {
  $_POST["comment_edit"] and $extra = "&comment_edit=true&commentID=".$_POST["comment_id"];
  $message_good and $extra.="&message_good=".urlencode($message_good);
  header("Location: ".$TPL["url_alloc_".$entity].$entity."ID=".$e->get_id().$extra);
}






?>
