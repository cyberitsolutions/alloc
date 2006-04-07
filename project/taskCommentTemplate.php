<?php
require_once("alloc.inc");

// Create an object to hold a taskCommentTemplate
$taskCommentTemplate = new taskCommentTemplate();

// Load the taskCommentTemplate from the database
if (isset($taskCommentTemplate)){
 $taskCommentTemplate->set_id($taskCommentTemplateID);
 $taskCommentTemplate->select();
}

// Process submission of the form using the save button
if (isset($_POST["save"])) {
  $taskCommentTemplate->read_globals();
  $taskCommentTemplate->save();
  header("Location: ".$TPL["url_alloc_taskCommentTemplateList"]);

// Process submission of the form using the delete button
} else if (isset($_POST["delete"])) {
  header("Location: ".$TPL["url_alloc_taskCommentTemplateList"]);
  $taskCommentTemplate->delete();
  page_close();
  exit();
}
// Load data for display in the template
$taskCommentTemplate->set_tpl_values();

// Invoke the page's main template
include_template("templates/taskCommentTemplateM.tpl");

// Close the request
page_close();
				 
?>

