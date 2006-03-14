<?php
include("alloc.inc");

function show_people($template_name) {
  global $person_query, $project, $TPL;

  $db = new db_alloc;
  $db->query($person_query);
  while ($db->next_record()) {
    $person = new person();
    $person->read_db_record($db);
    $person->set_tpl_values(DST_HTML_ATTRIBUTE, "person_");

    $task_filter = new task_filter();
    $task_filter->set_element("person", $person);
    if (isset($project)) {
      $task_filter->set_element("project", $project);
    }
    $task_list = new task_list($task_filter);
    $TPL["person_task_summary"] = $task_list->get_task_summary("", false);

    include_template($template_name);
  }
}

if ($projectID) {
  $project = new project;
  $project->set_id($projectID);
  $project->select();
  $TPL["navigation_links"] = $project->get_navigation_links();

  $project->check_perm(PERM_PROJECT_VIEW_TASK_ALLOCS);

  $person_query = sprintf("SELECT person.* ")
    .sprintf("FROM person, projectPerson ")
    .sprintf("WHERE person.personID = projectPerson.personID ")
    .sprintf(" AND projectPerson.projectID='%d'", addslashes($project->get_id()));

} else if ($personID) {
  $person_query = sprintf("SELECT * FROM person where personID = ".$personID." ORDER BY username");
} else {
  $person_query = sprintf("SELECT * FROM person ORDER BY username");
}

$TPL["projectID"] = $projectID;
include_template("templates/personGraphsM.tpl");

page_close();



?>
