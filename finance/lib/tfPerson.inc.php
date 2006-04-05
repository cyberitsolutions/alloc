<?php
class tfPerson extends db_entity {
  var $data_table = "tfPerson";
  var $display_field_name = "personID";


  function tfPerson() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("tfPersonID");
    $this->data_fields = array("tfID"=>new db_text_field("tfID")
                               , "personID"=>new db_text_field("personID"));

  }
}



?>
