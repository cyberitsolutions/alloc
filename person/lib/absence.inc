<?php
class absence extends db_entity {
  var $data_table = "absence";
  var $display_field_name = "personID";


  function absence() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("absenceID");
    $this->data_fields = array("dateFrom"=>new db_text_field("dateFrom")
                               , "dateTo"=>new db_text_field("dateTo")
                               , "personID"=>new db_text_field("personID")
                               , "absenceType"=>new db_text_field("absenceType")
                               , "contactDetails"=>new db_text_field("contactDetails")
      );
  }
}



?>
