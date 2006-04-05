<?php
class taskType extends db_entity {
  var $data_table = "taskType";
  var $display_field_name = "taskTypeName";

  function taskType() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("taskTypeID");
    $this->data_fields = array("taskTypeName"=>new db_text_field("taskTypeName")
                              ,"taskTypeActive"=>new db_text_field("taskTypeActive")
                              ,"taskTypeSequence"=>new db_text_field("taskTypeSequence")
                              );
  }
}



?>
