<?php
class tf_person extends data_entity {
  var $data_table = "tfPerson";
  var $display_field_name = "personID";


  function tf_person() {
    $this->key_field = new db_text_field("tfPersonID");
    $this->data_fields = array("tfID"=>new db_text_field("tfID")
                               , "personID"=>new db_text_field("personID"));

  }
}



?>
