<?php
class loan extends db_entity {
  var $data_table = "loan";
  var $display_field_name = "itemID";


  function loan() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("loanID");
    $this->data_fields = array("itemID"=>new db_text_field("itemID")
                               , "personID"=>new db_text_field("personID")
                               , "loanModifiedUser"=>new db_text_field("loanModifiedUser")
                               , "lastModified"=>new db_text_field("lastModified")
                               , "dateBorrowed"=>new db_text_field("dateBorrowed")
                               , "dateToBeReturned"=>new db_text_field("dateToBeReturned")
                               , "dateReturned"=>new db_text_field("dateReturned")
      );
  }



}



?>
