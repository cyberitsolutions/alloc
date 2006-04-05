<?php
class announcement extends db_entity {
  var $data_table = "announcement";
  var $display_field_name = "heading";


  function announcement() {
    $this->db_entity();
    $this->key_field = new db_text_field("announcementID");
    $this->data_fields = array("heading"=>new db_text_field("heading")
                               , "body"=>new db_text_field("body")
                               , "personID"=>new db_text_field("personID")
                               , "displayFromDate"=>new db_text_field("displayFromDate")
                               , "displayToDate"=>new db_text_field("displayToDate")
      );
  }

  function has_announcements() {
    $db = new db_alloc;
    $today = date("Y-m-d");
    $query = sprintf("select * from announcement where displayFromDate <= '%s' and displayToDate >= '%s'", $today, $today);
    $db->query($query);
    if ($db->next_record()) {
      return true;
    }
    return false;
  }

}



?>
