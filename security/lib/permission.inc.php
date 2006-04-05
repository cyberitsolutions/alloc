<?php
class permission extends db_entity {
  var $data_table = "permission";
  var $display_field_name = "tableName";

  function permission() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("permissionID");
    $this->data_fields = array("tableName"=>new db_text_field("tableName")
                               , "entityID"=>new db_text_field("entityID", "Record ID", "", array("empty_to_null"=>false))
                               , "personID"=>new db_text_field("personID", "Record ID", "", array("empty_to_null"=>false))
                               , "roleName"=>new db_text_field("roleName", "Record ID", "", array("empty_to_null"=>false))
                               , "actions"=>new db_text_field("actions")
                               , "sortKey"=>new db_text_field("sortKey")
                               , "allow"=>new db_text_field("allow")
                               , "comment"=>new db_text_field("comment")
      );
  }

  function describe_actions() {
    $actions = $this->get_value("actions");
    $description = "";

    $entity_class = $this->get_value("tableName");
    $entity = new $entity_class;
    $permissions = $entity->permissions;

    reset($permissions);
    while (list($a, $d) = each($permissions)) {
      if ((($actions & $a) == $a) && $d != "") {
        if ($description) {
          $description.= ",";
        }
        $description.= $d;
      }
    }

    return $description;
  }
}



?>
