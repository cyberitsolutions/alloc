<?php


function show_taskComments($template) {
  include_template($template);
}


function sort_comments_callback_func($a, $b) {
  return $a["date"] > $b["date"];
}

// show table of comments
function show_taskCommentsR($template) {
  global $TPL, $taskID, $commentID, $view, $taskCommentTemplateID, $current_user, $auth;
  
  // setup add/edit comment section values
  $TPL["task_taskID"] = $taskID;
  $TPL["task_taskComment"] = "";
  
  // Init
  $rows = array();
  
  // Get list of comments from timeSheetItem table
  $query = sprintf("SELECT timeSheetID, dateTimeSheetItem AS date, comment, personID
                      FROM timeSheetItem
                     WHERE timeSheetItem.taskID = %d AND (commentPrivate != 1 OR commentPrivate IS NULL)
                  ORDER BY dateTimeSheetItem,timeSheetItemID
                   ",$taskID);
  
  $db = new db_alloc;
  $db->query($query);
  while ($db->next_record()) {
    $timeSheetItem = new timeSheetItem;
    $timeSheetItem->read_db_record($db);
    $rows[] = $db->Record;
  }

  // Get list of comments
  $query = sprintf("SELECT commentID, commentLinkID, commentModifiedTime AS date, comment, commentModifiedUser AS personID
                      FROM comment 
                     WHERE comment.commentType = 'task' AND comment.commentLinkID = %d
                  ORDER BY comment.commentModifiedTime", $taskID);
  $db = new db_alloc;
  $db->query($query);
  while ($db->next_record()) {
    $rows[] = $db->Record;
  }

  usort($rows, "sort_comments_callback_func");

  foreach ($rows as $v) {

    if (!$v["comment"]) continue ;

    $person = new person;
    $person->set_id($v["personID"]);
    $person->select();
    $person->set_tpl_values(DST_HTML_ATTRIBUTE, "task_");

    $TPL["comment_buttons"] = "";
     $TPL["ts_label"] = "";
    if ($v["timeSheetID"]) {
      $TPL["ts_label"] = "(Time Sheet Comment)";

    } else if ($v["personID"] == $auth->auth["uid"]) {
      $TPL["comment_buttons"] = "<nobr><input type=\"submit\" name=\"taskComment_edit\" value=\"Edit\">
                                       <input type=\"submit\" name=\"taskComment_delete\" value=\"Delete\"></nobr>";
    }

    $TPL["task_commentID"] = $v["commentID"];
    $TPL["task_commentLinkID"] = $v["commentLinkID"];
    $TPL["task_commentModifiedDate"] = $v["date"];
    $TPL["task_username"] = $person->get_username(1);

    // trim comment to 128 characters
    if (strlen($v["comment"]) > 3000 && $view != "printer") {
      $TPL["task_comment_trimmed"] = nl2br(sprintf("%s...", substr($v["comment"], 0, 3000)));
    } else {
      $TPL["task_comment_trimmed"] = str_replace("\n", "<br>", htmlentities($v["comment"]));
    }

    if (!$commentID || $commentID != $v["commentID"]) {
      include_template($template);
    }
  }
}




?>
