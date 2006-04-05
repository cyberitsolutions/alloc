<?php
class clientContact extends db_entity {
  var $data_table = "clientContact";
  var $display_field_name = "clientContactName";

  function clientContact() {
    $this->db_entity();
    $this->key_field = new db_text_field("clientContactID");
    $this->data_fields = array("clientID"=>new db_text_field("clientID"),
                               "clientContactName"=>new db_text_field("clientContactName"),
                               "clientContactStreetAddress"=>new db_text_field("clientContactStreetAddress"),
                               "clientContactSuburb"=>new db_text_field("clientContactSuburb"),
                               "clientContactState"=>new db_text_field("clientContactState"),
                               "clientContactPostcode"=>new db_text_field("clientContactPostcode"),
                               "clientContactPhone"=>new db_text_field("clientContactPhone"),
                               "clientContactMobile"=>new db_text_field("clientContactMobile"), "clientContactFax"=>new db_text_field("clientContactFax"), "clientContactEmail"=>new db_text_field("clientContactEmail"), "clientContactOther"=>new db_text_field("clientContactOther"));
  }
}



?>
