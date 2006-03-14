<?php

class taskCommentTemplate extends db_entity {
  
  var $data_table = "taskCommentTemplate";
  var $display_field_name = "taskCommentTemplateName";


  function taskCommentTemplate() {
    $this->db_entity();
    $this->key_field = new db_text_field("taskCommentTemplateID");
    $this->data_fields = array("taskCommentTemplateName"=>new db_text_field("taskCommentTemplateName")
                             , "taskCommentTemplateText"=>new db_text_field("taskCommentTemplateText")
                             , "taskCommentTemplateLastModified"=>new db_text_field("taskCommentTemplateLastModified"));
   }


  function get_populated_template($taskID) {

    $task = new task;
    $task->set_id($taskID);
    $task->select();
    $swap["ti"] = $task->get_id();
    $swap["to"] = person::get_fullname($task->get_value("creatorID"));
    $swap["ta"] = person::get_fullname($task->get_value("personID"));
    $swap["tn"] = stripslashes($task->get_value("taskName"));
    
    $project = new project;
    $project->set_id($task->get_value("projectID"));
    $project->select();
    $swap["pn"] = stripslashes($project->get_value("projectName"));

    $client = new client;
    $client->set_id($project->get_value("clientID"));
    $client->select();
    $swap["cc"] = stripslashes($client->get_value("clientName"));

    $swap["cd"] = "Phone: ".config::get_config_item("companyContactPhone");
    $swap["cd"].= "\nFax: ".config::get_config_item("companyContactFax");
    $swap["cd"].= "\n".config::get_config_item("companyContactAddress");
    $swap["cd"].= "\nEmail: ".config::get_config_item("companyContactEmail");
    $swap["cd"].= "  Web: ".config::get_config_item("companyContactHomePage");

    $swap["cn"] = config::get_config_item("companyName");

    $str = $this->get_value("taskCommentTemplateText");
    foreach ($swap as $k => $v) {
      $str = str_replace("%".$k,$v,$str);
    }
    return $str;
    

  }




}
?>
