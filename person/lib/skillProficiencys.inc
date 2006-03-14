<?php
class skillProficiencys extends db_entity {
  var $data_table = "skillProficiencys";
  var $display_field_name = "personID";


  function skillProficiencys() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("proficiencyID");
    $this->data_fields = array("personID"=>new db_text_field("personID"), "skillID"=>new db_text_field("skillID"), "skillProficiency"=>new db_text_field("skillProficiency"),);
  }
}



?>
