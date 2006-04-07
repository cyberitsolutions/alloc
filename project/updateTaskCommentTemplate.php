<?php

require_once("alloc.inc");


if ($taskCommentTemplateID && $taskID) {
  $taskCommentTemplate = new taskCommentTemplate;
  $taskCommentTemplate->set_id($taskCommentTemplateID);
  $taskCommentTemplate->select();
  echo stripslashes($taskCommentTemplate->get_populated_template($taskID));
}



?>
