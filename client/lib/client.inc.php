<?php
class client extends db_entity {
  var $data_table = "client";
  var $display_field_name = "clientName";


  function client() {
    $this->db_entity();
    $this->display_field_name = "clientName";
    $this->key_field = new db_text_field("clientID");
    $this->data_fields = array("clientName"=>new db_text_field("clientName")

                               , "clientPrimaryContactID"=>new db_text_field("clientPrimaryContactID")

                               , "clientStreetAddressOne"=>new db_text_field("clientStreetAddressOne")
                               , "clientStreetAddressTwo"=>new db_text_field("clientStreetAddressTwo")
                               // , "clientContactNameOne"=> new db_text_field("clientContactNameOne")
                               // , "clientContactNameTwo"=> new db_text_field("clientContactNameTwo")
                               , "clientSuburbOne"=>new db_text_field("clientSuburbOne")
                               , "clientSuburbTwo"=>new db_text_field("clientSuburbTwo")

                               , "clientStateOne"=>new db_text_field("clientStateOne")
                               , "clientStateTwo"=>new db_text_field("clientStateTwo")

                               , "clientPostcodeOne"=>new db_text_field("clientPostcodeOne")
                               , "clientPostcodeTwo"=>new db_text_field("clientPostcodeTwo")

                               , "clientPhoneOne"=>new db_text_field("clientPhoneOne")
                               // , "clientPhoneTwo"=> new db_text_field("clientPhoneTwo")
                               , "clientFaxOne"=>new db_text_field("clientFaxOne")
                               // , "clientFaxTwo"=> new db_text_field("clientFaxTwo")
                               // , "clientEmailOne"=> new db_text_field("clientEmailOne")
                               // , "clientEmailTwo"=> new db_text_field("clientEmailTwo")
                               , "clientCountryOne"=>new db_text_field("clientCountryOne")
                               , "clientCountryTwo"=>new db_text_field("clientCountryTwo")

                               , "clientComment"=>new db_text_field("clientComment")

			       , "clientCreatedTime"=>new db_text_field("clientCreatedTime")
			       , "clientModifiedTime"=>new db_text_field("clientModifiedTime")
                               , "clientModifiedUser"=>new db_text_field("clientModifiedUser")

                               , "clientStatus"=>new db_text_field("clientStatus"));

  }
}



?>
