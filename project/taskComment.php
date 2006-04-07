<?php

include("alloc.inc");

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
      $successful_recipients = $task->send_emails($commentEmailCheckboxes, $comment, "New Task Comments");
 
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
  header("Location: ".$TPL["url_alloc_task"]."&taskID=".$task->get_id().$extra);
}






?>
