<?php
class project_list_home_item extends home_item {
  function project_list_home_item() {
    home_item::home_item("project_list", "Project List", "project", "projectListH.tpl");
  }

  function show_projects($template_name) {
    global $current_user, $TPL;

    $query = sprintf("SELECT project.*, clientName
             FROM project LEFT JOIN client ON project.clientID = client.clientID, projectPerson
             WHERE projectPerson.projectID = project.projectID AND personID=%d AND projectStatus = 'current'
             ORDER BY projectName,projectPriority LIMIT 15", $current_user->get_id());
    $db = new db_alloc;
    $db->query($query);
    while ($db->next_record()) {
      $project = new project;
      $project->read_db_record($db);
      $TPL["projectID"] = $project->get_id();
      $TPL["projectName"] = $project->get_value("projectName");
      $TPL["projectNav"] = $project->get_navigation_links();
      include_template($this->get_template_dir().$template_name, $this);
    }
  }
}



?>
