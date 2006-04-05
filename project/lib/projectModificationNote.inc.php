<?php
class projectModificationNote extends db_entity {
  var $data_table = "projectModificationNote";
  var $display_field_name = "modDescription";

  function projectModificationNote() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("projectModNoteID");
    $this->data_fields = array("projectID"=>new db_text_field("projectID")
                               , "dateMod"=>new db_text_field("dateMod")
                               , "modDescription"=>new db_text_field("modDescription")
                               , "personID"=>new db_text_field("personID")
      );
  }
}



?>
