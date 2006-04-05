<?php
define("PERM_PROJECT_PERSON_READ_DETAILS", 256);

class projectPerson extends db_entity
{
  var $data_table = "projectPerson";
  var $display_field_name = "projectID";

  function projectPerson() {
    $this->db_entity();         // Call constructor of parent class
    $this->key_field = new db_text_field("projectPersonID");
    $this->data_fields = array("personID"=>new db_text_field("personID")
                              ,"projectID"=>new db_text_field("projectID")
                              ,"emailType"=>new db_text_field("emailType")
                              ,"emailEmptyTaskList"=>new db_text_field("emailEmptyTaskList")
                              ,"emailDateRegex"=>new db_text_field("emailDateRegex")
                              ,"rate"=>new db_text_field("rate")
                              ,"rateUnitID"=>new db_text_field("rateUnitID")
                              ,"projectPersonRoleID"=>new db_text_field("projectPersonRoleID")
                              );
    $this->set_value("emailEmptyTaskList", 0);
  }

  function date_regex_matches() {
    return eregi($this->get_value("emailDateRegex"), date("YmdD"));
  }


  function is_owner($person = "") {

    if (!$this->get_id()) {
      return true;
    } else {
      $project = new project;
      $project->set_id($this->get_value("projectID"));
      $project->select();
      return $project->is_owner($person);
    }
  }


  // This is a wrapper to simplify inserts into the projectPerson table using the new
  // projectPersonRole methodology.. role handle is canEditTasks, or isManager atm
  function set_value_role($roleHandle) {
    $db = new db_alloc;
    $db->query(sprintf("SELECT * FROM projectPersonRole WHERE projectPersonRoleHandle = '%s'",$roleHandle));
    $db->next_record();
    $this->set_value("projectPersonRoleID",$db->f("projectPersonRoleID"));
  }


}



?>
