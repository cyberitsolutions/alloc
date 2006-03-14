<?php
include ("alloc.inc");

function show_taskCommentTemplate($template_name) {
  global $current_user, $auth, $TPL;
  
// Run query and loop through the records
  $db = new db_alloc;
  $query = "SELECT * FROM taskCommentTemplate $where ORDER BY taskCommentTemplateName";
  $db->query($query);
  while ($db->next_record()) {
    $taskCommentTemplate = new taskCommentTemplate;
    $taskCommentTemplate->read_db_record($db);
    $taskCommentTemplate->set_tpl_values(DST_HTML_ATTRIBUTE, "TCT_");
    $TPL["odd_even"] = $TPL["odd_even"] == "even" ? "odd" : "even";
    include_template($template_name);
  }
}

include_template("templates/taskCommentTemplateListM.tpl");
page_close();
