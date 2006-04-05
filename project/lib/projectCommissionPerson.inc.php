<?php
class projectCommissionPerson extends db_entity {
  var $data_table = "projectCommissionPerson";
  var $display_field_name = "projectID";

  function projectCommissionPerson() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("projectCommissionPersonID");
    $this->data_fields = array("projectID"=>new db_text_field("projectID")
                               , "tfID"=>new db_text_field("tfID")
                               , "commissionPercent"=>new db_text_field("commissionPercent")
      );
  }


  function is_owner($person = "") {
    $project = new project;
    $project->set_id($this->get_value("projectID"));
    $project->select();
    return $project->is_owner($person);
  }


}



?>
