<?php
class item extends db_entity {
  var $data_table = "item";
  var $display_field_name = "itemName";


  function item() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("itemID");
    $this->data_fields = array("itemModifiedUser"=>new db_text_field("itemModifiedUser")
                               , "itemName"=>new db_text_field("itemName")
                               , "itemAuthor"=>new db_text_field("itemAuthor")
                               , "itemNotes"=>new db_text_field("itemNotes")
                               , "lastModified"=>new db_text_field("lastModified")
                               , "itemType"=>new db_text_field("itemType")
			       , "personID"=>new db_text_field("personID")
      );
  }



}



?>
