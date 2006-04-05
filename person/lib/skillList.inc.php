<?php
class skillList extends db_entity {
  var $data_table = "skillList";
  var $display_field_name = "skillName";


  function skillList() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("skillID");
    $this->data_fields = array("skillName"=>new db_text_field("skillName"), "skillDescription"=>new db_text_field("skillDescription"), "skillClass"=>new db_text_field("skillClass")
      );
  }

  // return true if a skill with same name and class already exists
  // and update fields of current if it does exist
  function skill_exists() {
    $query = "SELECT * FROM skillList";
    $query.= sprintf(" WHERE skillName='%s'", $this->get_value('skillName'));
    $query.= sprintf(" AND skillClass='%s'", $this->get_value('skillClass'));
    $db = new db_alloc;
    $db->query($query);
    if ($db->next_record()) {
      $skillList = new skillList;
      $skillList->read_db_record($db);
      $this->set_id($skillList->get_id());
      $this->set_value('skillDescription', $skillList->get_value('skillDescription'));
      return TRUE;
    }
    return FALSE;
  }
}



?>
