<?php
class config extends db_entity {
  var $data_table = "config";

  function config() {
    $this->db_entity();
    $this->key_field = new db_text_field("configID");
    $this->data_fields = array("name"=>new db_text_field("name", "")
                              ,"value"=>new db_text_field("value", "")
      );
  }

  function get_config_item($name='') {
    $db = new db_alloc;
    $db->query(sprintf("SELECT value FROM config WHERE name = '%s'",$name));
    $db->next_record();
    return $db->f('value');
  }

  function get_config_item_id($name='') {
    $db = new db_alloc;
    $db->query(sprintf("SELECT configID FROM config WHERE name = '%s'",$name));
    $db->next_record();
    return $db->f('configID');
  }

}



?>
