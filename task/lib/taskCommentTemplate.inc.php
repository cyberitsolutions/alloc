<?php

/*
 * Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
 * Pty. Ltd.
 * 
 * This file is part of the allocPSA application <info@cyber.com.au>.
 * 
 * allocPSA is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at
 * your option) any later version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public
 * License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with allocPSA. If not, see <http://www.gnu.org/licenses/>.
*/


class taskCommentTemplate extends db_entity {
  
  var $data_table = "taskCommentTemplate";
  var $display_field_name = "taskCommentTemplateName";


  function taskCommentTemplate() {
    $this->db_entity();
    $this->key_field = new db_field("taskCommentTemplateID");
    $this->data_fields = array("taskCommentTemplateName"=>new db_field("taskCommentTemplateName")
                             , "taskCommentTemplateText"=>new db_field("taskCommentTemplateText")
                             , "taskCommentTemplateModifiedTime"=>new db_field("taskCommentTemplateModifiedTime"));
   }


  function get_populated_template($taskID) {
    global $current_user;
    $swap["cu"] = person::get_fullname($current_user->get_id());

    $task = new task;
    $task->set_id($taskID);
    $task->select();
    $swap["ti"] = $task->get_id();
    $swap["to"] = person::get_fullname($task->get_value("creatorID"));
    $swap["ta"] = person::get_fullname($task->get_value("personID"));
    $swap["tm"] = person::get_fullname($task->get_value("managerID"));
    $swap["tc"] = person::get_fullname($task->get_value("closerID"));
    $swap["tn"] = $task->get_value("taskName");
    $swap["td"] = $task->get_value("taskDescription");
    
    $project = new project;
    $project->set_id($task->get_value("projectID"));
    $project->select();
    $swap["pn"] = $project->get_value("projectName");

    $client = new client;
    $client->set_id($project->get_value("clientID"));
    $client->select();
    $swap["cc"] = $client->get_value("clientName");

    $swap["cd"] = config::get_config_item("companyContactAddress");
    $swap["cd"].= " ".config::get_config_item("companyContactAddress2");
    $swap["cd"].= " ".config::get_config_item("companyContactAddress3");
    $swap["cd"].= "\nP: ".config::get_config_item("companyContactPhone");
    $swap["cd"].= "\nF: ".config::get_config_item("companyContactFax");
    $swap["cd"].= "\nE: ".config::get_config_item("companyContactEmail");
    $swap["cd"].= "\nW: ".config::get_config_item("companyContactHomePage");

    $swap["cn"] = config::get_config_item("companyName");

    $swap["tu"] = config::get_config_item("allocURL")."task/task.php?taskID=".$this->get_id();

    $swap["c1"] = config::get_config_item("companyContactAddress");
    $swap["c2"] = config::get_config_item("companyContactAddress2");
    $swap["c3"] = config::get_config_item("companyContactAddress3");
    $swap["ce"] = config::get_config_item("companyContactEmail");
    $swap["cp"] = config::get_config_item("companyContactPhone");
    $swap["cf"] = config::get_config_item("companyContactFax");
    $swap["cw"] = config::get_config_item("companyContactHomePage");

    $str = $this->get_value("taskCommentTemplateText");
    foreach ($swap as $k => $v) {
      $str = str_replace("%".$k,$v,$str);
    }
    return $str;
    

  }




}
?>
