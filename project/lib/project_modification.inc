<?php
class project_modification extends data_entity {
  var $data_table = "projectModificationNote";
  var $display_field_name = "modDescription";

  function project_modification() {
    $this->key_field = new db_text_field("projectModNoteID");
    $this->data_fields = array("projectID"=>new db_text_field("projectID")
                               , "dateMod"=>new db_text_field("dateMod")
                               , "modDescription"=>new db_text_field("modDescription")
                               , "personID"=>new db_text_field("personID")
      );
  }
}



?>
