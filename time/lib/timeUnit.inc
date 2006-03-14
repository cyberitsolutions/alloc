<?php

class timeUnit extends db_entity
{
  var $classname = "timeUnit";
  var $data_table = "timeUnit";
  var $display_field_name = "timeUnitLabelA";

  function timeUnit() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("timeUnitID");
    $this->data_fields = array("timeUnitName"=>new db_text_field("timeUnitName")
                               ,"timeUnitLabelA"=>new db_text_field("timeUnitLabelA")
                               ,"timeUnitLabelB"=>new db_text_field("timeUnitLabelB")
                               ,"timeUnitSeconds"=>new db_text_field("timeUnitSeconds")
                               ,"timeUnitActive"=>new db_text_field("timeUnitActive")
                               ,"timeUnitSequence"=>new db_text_field("timeUnitSequence")
        );
  }


  function get_dropdown_options($key,$label,$sel=false) {
    $arr = $this->get_assoc_array($key,$label);
    return get_options_from_array($arr,$sel);
  }   

  function seconds_to_display_time_unit($seconds) {
    $q = "SELECT * FROM timeUnit";
    $db = new db_alloc;
    $db->query($q);
    while ($db->next_record()) {
      //blag someother time
    }  

  }



}  




?>
