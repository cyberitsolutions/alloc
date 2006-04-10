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

if ($taskID) {
  $task = new task;
  $task->set_id($taskID);
  $task->select(); 
}

// comments
if (isset($taskComment_save) || isset($taskComment_update)) {

  // Add task comment template.
  if ($taskCommentTemplateID && !$taskComment) {
    $taskCommentTemplate = new taskCommentTemplate;
    $taskCommentTemplate->set_id($taskCommentTemplateID);
    $taskCommentTemplate->select();
    $taskComment = $taskCommentTemplate->get_value("taskCommentTemplateText");
  }
  

  $comment = new comment;
  $comment->set_value('commentType', 'task');
  $comment->set_value('commentLinkID', $taskID);
  $comment->set_modified_time();
  $comment->set_value('commentModifiedUser', $current_user->get_id());

  if (isset($taskComment_update)) {
    $comment->set_id($taskComment_id);
  }

  if (isset($taskComment)) {
    $comment->set_value('comment', $taskComment);
    $comment->save();

    // Email new comment?
    if ($commentEmailCheckboxes) {
      $successful_recipients = $task->send_emails($commentEmailCheckboxes, $comment, "Task Comments");
 
      // Append success to end of the comment
      if ($successful_recipients && is_object($comment)) {
        $append_comment_text = "Emailed: ".$successful_recipients." at ".date("Y-m-d H:i:s")."\n".$comment->get_value("comment");
        $comment->set_value("comment",$append_comment_text);
        $comment->save();
      }
    }
  }
} else if (isset($taskComment_delete) && isset($taskComment_id)) {
  $comment = new comment;
  $comment->set_id($taskComment_id);
  $comment->delete();
}

if ($task->get_id()) {
  $taskComment_edit and $extra = "&taskComment_edit=true&commentID=".$taskComment_id;
  header("Location: ".$TPL["url_alloc_task"]."taskID=".$task->get_id().$extra);
}






?>
