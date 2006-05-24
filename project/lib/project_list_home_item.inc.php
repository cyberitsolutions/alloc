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

class project_list_home_item extends home_item {
  function project_list_home_item() {
    home_item::home_item("project_list", "Project List", "project", "projectListH.tpl");
  }

  function show_projects($template_name) {
    global $current_user, $TPL;

    if (isset($current_user->prefs["projectListNum"]) && $current_user->prefs["projectListNum"] != "all") {
      $limit = sprintf("LIMIT %d",$current_user->prefs["projectListNum"]);
    }

    $query = sprintf("SELECT project.*, clientName
             FROM project LEFT JOIN client ON project.clientID = client.clientID, projectPerson
             WHERE projectPerson.projectID = project.projectID AND personID=%d AND projectStatus = 'current'
             ORDER BY projectName,projectPriority %s", $current_user->get_id(), $limit);
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
