<?php

class projectPersonRole extends db_entity
{
  var $data_table = "projectPersonRole";
  var $display_field_name = "projectID";

  function projectPersonRole() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("projectPersonRoleID");
    $this->data_fields = array("projectPersonRoleName"=>new db_text_field("projectPersonRoleName")
                               , "projectPersonRoleHandle"=>new db_text_field("projectPersonRoleHandle")
                               , "projectPersonRoleSortKey"=>new db_text_field("projectPersonRoleSortKey")
      );
  }



}



?>
